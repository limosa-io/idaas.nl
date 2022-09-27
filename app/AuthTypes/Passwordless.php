<?php

namespace App\AuthTypes;

use App\AuthChain\Helper;
use App\AuthChain\Module\ModuleInterface;
use App\AuthChain\Module\ModuleResult;
use App\AuthChain\State;
use App\AuthChain\Subject;
use App\EmailTemplate;
use App\Exceptions\AuthFailedException;
use App\Exceptions\TokenExpiredException;
use App\Mail\StandardMail;
use App\Repository\KeyRepository;
use App\Repository\SubjectRepository;
use App\Repository\UserRepository;
use App\User;
use DateTimeImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Validation\Constraint\SignedWith;

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

        // 2. check signature

        $config = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::plainText($publicKey->getKeyContents()),
            InMemory::plainText($publicKey->getKeyContents())
        );

        $parser = $config->parser();
        $token = $parser->parse($request->query->get('token'));

        $config->validator()->validate($token, ...[new SignedWith($config->signer(), $config->verificationKey())]);

//        $token->verify(new Sha256(), new Key($publicKey->getKeyPath()));

        // 3. check issuer, audience, expiration
        if ($token->isExpired(new DateTimeImmutable())) {
            throw new TokenExpiredException('passwordless token expired');
        }

        // 4. get user with id equals subject
        $user = User::findOrFail($token->claims()->get('sub'));

        // 5. check if last_login_date is the same from the jwt
        $state = Helper::loadStateFromSession(app(), $token->claims()->get('state'));

        if ($state == null) {
            throw new AuthFailedException('Unknown state');
        }

        if ($state->getIncomplete() == null) {
            throw new AuthFailedException("The provided token has already been used");
        }

        $module = $state->getIncomplete()->getModule();

        // 6. log the user in
        $result = $module
            ->baseResult()
            ->setCompleted(true)
            ->setSubject(
                resolve(SubjectRepository::class)
                ->with($user->email, $this, $module)
                ->setTypeIdentifier(
                    $this->getIdentifier()
                )
                ->setUserId($user->id)
            );

        $state->addResult($result);

        return Helper::getAuthResponseAsRedirect($request, $state);
    }

    public function getRedirect(ModuleInterface $module, State $state)
    {
        $state = (string)$state;
    }

    public function process(Request $request, State $state, ModuleInterface $module)
    {
        if ($state->getSubject() != null) {
            //MUST approve subject!
            $subject = $state->getSubject();

            if ($state->getSubject()->getEmail() == null) {
                return (new ModuleResult())
                    ->setCompleted(false)
                    ->setResponse(response(['error' => 'No email address is known for this user']));
            }

            if ($state->getSubject()->getUserId() == null) {
                return (new ModuleResult())
                    ->setCompleted(false)
                    ->setResponse(response(['error' => 'No user id is known for this user']));
            }

            Mail::to($state->getSubject()->getEmail())->send(
                new StandardMail(
                    @$module->config['template_id'],
                    [
                    'url' => htmlentities(
                        route('ice.login.passwordless') . '?token=' . urlencode(
                            self::getToken(
                                $state->getSubject()->getUserId(),
                                $state
                            )->toString()
                        )
                    ),
                    'subject' => $state->getSubject(),
                    'user' =>  $state->getSubject() ? $state->getSubject()->getUser() : null,
                    ],
                    EmailTemplate::TYPE_PASSWORDLESS,
                    $subject->getPreferredLanguage()
                )
            );

            return $module->baseResult()->setCompleted(false)->setResponse(response([]));
        } else {
            $user = resolve(UserRepository::class)->findByIdentifier($request->input('username'));

            if ($user == null) {
                return (new ModuleResult())
                    ->setCompleted(false)
                    ->setResponse(
                        response(['error' => 'We could not find a user with this attribute.'], 422)
                    );
            }

            $url = route('ice.login.passwordless') . '?token=' .
                urlencode(self::getToken($user->id, $state)->toString());

            Mail::to($user->email)->send(
                new StandardMail(
                    @$module->config['template_id'],
                    [
                    'url' => htmlentities($url),
                    'subject' => $state->getSubject(),
                    'user' =>  $state->getSubject() ? $state->getSubject()->getUser() : null
                    ],
                    EmailTemplate::TYPE_PASSWORDLESS,
                    $user->preferredLanguage
                )
            );

            return $module->baseResult()->setCompleted(false)->setResponse(response([]));
        }
    }

    public static function getToken($identifier, State $state)
    {
        $privateKey = resolve(KeyRepository::class)->getPrivateKey();

        $config = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::plainText($privateKey->getKeyContents()),
            InMemory::plainText($privateKey->getKeyContents())
        );

        $token = $config->builder()
            ->withHeader('kid', method_exists($privateKey, 'getKid') ? $privateKey->getKid() : null)
            ->issuedBy(url('/'))
            ->permittedFor(url('/'))
            ->relatedTo($identifier)
            ->expiresAt(DateTimeImmutable::createFromMutable(new \DateTime('+300 seconds')))
            ->withClaim('state', (string) $state)
            ->issuedAt(new DateTimeImmutable());

        return $token->getToken($config->signer(), $config->signingKey());
    }
}
