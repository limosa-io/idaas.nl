<?php

namespace App\AuthTypes;

use Illuminate\Http\Request;
use ArieTimmerman\Laravel\AuthChain\State;
use ArieTimmerman\Laravel\AuthChain\Module\ModuleResult;
use ArieTimmerman\Laravel\AuthChain\Module\ModuleInterface;
use ArieTimmerman\Laravel\AuthChain\Repository\SubjectRepositoryInterface;

use Illuminate\Support\Facades\Mail;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\Key;
use App\Repository\KeyRepository;
use ArieTimmerman\Laravel\AuthChain\Types\AbstractType;
use ArieTimmerman\Laravel\AuthChain\Helper;
use App\User;
use App\Mail\StandardMail;
use ArieTimmerman\Laravel\AuthChain\Object\Subject;
use ArieTimmerman\Laravel\AuthChain\Repository\UserRepositoryInterface;
use App\Exceptions\TokenExpiredException;
use App\EmailTemplate;
use ArieTimmerman\Laravel\AuthChain\Exceptions\AuthFailedException;

class Passwordless extends AbstractType
{

    public function isPassive()
    {
        return false;
    }

    public function init(Request $request, State $state, ModuleInterface $module)
    {
        
    }

    public function getDefaultName()
    {
        return "Magic Link";
    }

    /**
     * This module can work as a first-factor, or as a second-factor in case the subject has a mail address
     */
    public function isEnabled(?Subject $subject)
    {
        return $subject == null || $subject->getEmail('email') != null;
    }

    public function processCallback(Request $request)
    {
        

        // 1. get token
        $publicKey = resolve(KeyRepository::class)->getPublicKey();

        $parser = new Parser();
        $token = $parser->parse($request->query->get('token'));

        // 2. check signature
        $token->verify(new Sha256(), new Key($publicKey->getKeyPath()));

        // 3. check issuer, audience, expiration
        if($token->isExpired()) {
            throw new TokenExpiredException('passwordless token expired');
        }

         // 4. get user with id equals subject
        $user = User::findOrFail($token->getClaim('sub'));

        // 5. check if last_login_date is the same from the jwt
        $state = Helper::loadStateFromSession(app(), $token->getClaim('state'));

        if($state == null) {
            throw new AuthFailedException('Unknown state');
        }

        if($state->getIncomplete() == null) {
            throw new AuthFailedException("The provided token has already been used");
        }

        $module = $state->getIncomplete()->getModule();

        // 6. log the user in
        $result = $module->baseResult()->setCompleted(true)->setSubject(resolve(SubjectRepositoryInterface::class)->with($user->email, $this, $module)->setTypeIdentifier($this->getIdentifier())->setUserId($user->id));

        $state->addResult($result);
        
        return Helper::getAuthResponseAsRedirect($request, $state);
    }

    public function getRedirect(ModuleInterface $module, State $state)
    {
        $state = (string)$state;
    }

    public function process(Request $request, State $state, ModuleInterface $module)
    {

        if($state->getSubject() != null) {

            //MUST approve subject!
            $subject = $state->getSubject();

            if($state->getSubject()->getEmail() == null) {
                return (new ModuleResult())->setCompleted(false)->setResponse(response(['error'=>'No email address is known for this user']));
            }

            if($state->getSubject()->getUserId() == null) {
                return (new ModuleResult())->setCompleted(false)->setResponse(response(['error'=>'No user id is known for this user']));
            }

            Mail::to($state->getSubject()->getEmail())->send(
                new StandardMail(
                    @$module->config['template_id'], [
                    'url'=> htmlentities(route('ice.login.passwordless') . '?token=' . urlencode(self::getToken($state->getSubject()->getUserId(), $state))),
                    'subject' => $state->getSubject(),
                    'user' =>  $state->getSubject() ? $state->getSubject()->getUser() : null,
                    ], EmailTemplate::TYPE_PASSWORDLESS, $subject->getPreferredLanguage()
                )
            );
            
            return $module->baseResult()->setCompleted(false)->setResponse(response([]));

        }else{

            $user = resolve(UserRepositoryInterface::class)->findByIdentifier($request->input('username'));

            if($user == null) {
                return (new ModuleResult())->setCompleted(false)->setResponse(response(['error'=>'We could not find a user with this attribute.'], 422));
            }

            $url = route('ice.login.passwordless') . '?token=' . urlencode(self::getToken($user->id, $state));

            Mail::to($user->email)->send(
                new StandardMail(
                    @$module->config['template_id'], [
                    'url'=> htmlentities($url),
                    'subject' => $state->getSubject(),
                    'user' =>  $state->getSubject() ? $state->getSubject()->getUser() : null
                    ], EmailTemplate::TYPE_PASSWORDLESS, $user->preferredLanguage 
                )
            );
                    
            return $module->baseResult()->setCompleted(false)->setResponse(response([]));

        }

    }

    public static function getToken($identifier, State $state)
    {

        $privateKey = resolve(KeyRepository::class)->getPrivateKey();

        return (new Builder())->setHeader('kid', $privateKey->getKid())
            ->setIssuer(url('/'))
            ->setSubject($identifier)
            ->setAudience(url('/'))
            ->setExpiration((new \DateTime('+300 seconds'))->getTimestamp())
            ->setIssuedAt((new \DateTime())->getTimestamp())
            ->set('state', (string) $state)
            //->set('last_login_date', $user->last_login_date)
            ->sign(new Sha256(), new Key($privateKey->getKeyPath(), $privateKey->getPassPhrase()))
            ->getToken();

    }

}