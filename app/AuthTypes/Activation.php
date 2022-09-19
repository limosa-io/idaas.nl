<?php

/*
Allows self-service password reset.

Requires
- A mail address or username.
- Or a subject that is related to an user

(1) Ask for the users mail address (or get the one from the existing subject)
(2) Send a mail with a link with token.
(3) Let the user click on the link. Check token.
(4) Provide a prompt.
(5) Set the password of the user

*/

namespace App\AuthTypes;

use Illuminate\Http\Request;
use App\AuthChain\State;
use App\AuthChain\Module\ModuleResult;
use App\AuthChain\Module\ModuleInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use App\Repository\KeyRepository;
use App\AuthChain\Helper;
use App\User;
use App\AuthChain\Object\Subject;
use App\AuthChain\Repository\UserRepositoryInterface;
use App\Exceptions\TokenExpiredException;
use App\EmailTemplate;
use App\Mail\StandardMail;
use App\Repository\SubjectRepository;
use DateTimeImmutable;

class Activation extends AbstractType
{
    /**
     * This module can work as a first-factor, or as a second-factor in case the subject has a mail address
     */
    public function isEnabled(?Subject $subject)
    {
        return $subject == null || $subject->getEmail('email') != null;
    }

    public function getDefaultGroup()
    {
        return null;
    }

    public function getDefaultName()
    {
        return "Activation";
    }

    public function processCallback(Request $request)
    {
        // 1. get token
        $privateKey = resolve(KeyRepository::class)->getPrivateKey();
        $publicKey = resolve(KeyRepository::class)->getPublicKey();

        $config = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::plainText($privateKey->getKeyContents()),
            InMemory::plainText($publicKey->getKeyContents() . "\n")
        );
        $config->setValidationConstraints(
            new SignedWith(new Sha256(), InMemory::plainText($publicKey->getKeyContents() . "\n"))
        );

        $parser = $config->parser();
        $token = $parser->parse($request->query->get('token'));

        $constraints = $config->validationConstraints();
        // 2. check signature
        if (! $config->validator()->validate($token, ...$constraints)) {
            throw new TokenExpiredException('Signature not valid');
        }


        // 3. check issuer, audience, expiration
        if ($token->isExpired(new DateTimeImmutable())) {
            throw new TokenExpiredException('Activation token expired');
        }

        // 4. get user with id equals subject
        $user = User::findOrFail($token->headers()->get("sub"));

        // 5. check if last_login_date is the same from the jwt
        $state = Helper::loadStateFromSession(app(), $token->claims()->get('state'));

        if ($state == null) {
            throw new \Exception("Unknown state");
        }

        if ($state->getIncomplete() == null) {
            throw new \Exception("Token already in use");
        }

        $module = $state->getIncomplete()->getModule();

        // 6. log the user in
        $result = $module
            ->baseResult()
            ->setCompleted(true)
            ->setSubject(
                resolve(SubjectRepository::class)
                ->with($user->email, $this, $module)
                ->setTypeIdentifier($this->getIdentifier())
                ->setUserId($user->id)
            );

        $state->addResult($result);

        return Helper::getAuthResponseAsRedirect($request, $state);
    }

    public function getRedirect(ModuleInterface $module, State $state)
    {
        $state = (string)$state;
    }

    public function sendEmail(Subject $subject, ModuleInterface $module, State $state)
    {
        Mail::to($subject->getEmail())->send(
            new StandardMail(
                @$module->config['template_id'],
                [
                'url' => htmlentities(route('ice.login.activation') . '?token=' . urlencode(
                    self::getToken($subject->getUserId(), $state)->toString()
                )),
                'subject' => $subject,
                'user' =>  $subject->getUser()
                ],
                EmailTemplate::TYPE_ACTIVATION,
                $subject->getPreferredLanguage()
            )
        );
    }

    public function process(Request $request, State $state, ModuleInterface $module)
    {
        if (
            $state->getIncomplete() != null &&
            $state->getIncomplete()->moduleState != null &&
            $state->getIncomplete()->moduleState['state'] == 'confirmed'
        ) {
            //TODO: is this really needed for activation?
            if ($request->input('password')) {
                $user = $state->getIncomplete()->getSubject()->getUser();

                $user->password = Hash::make($request->input('password'));
                $user->save();

                return $state->getIncomplete()->setCompleted(true)->setResponse(response([  ]));
            } else {
                return $state
                    ->getIncomplete()
                    ->setResponse(response([ 'error' => 'You must provide a password'  ], 400));
            }
        } elseif ($state->getSubject() != null) {
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

            if ($state->getSubject()->isActive()) {
                return (new ModuleResult())
                    ->setCompleted(false)
                    ->setResponse(response(['error' => 'The user is already active']));
            }

            $this->sendEmail($subject, $module, $state);

            return $module->baseResult()->setCompleted(false)->setResponse(response([]));
        } else {
            $user = resolve(UserRepositoryInterface::class)->findByIdentifier($request->input('username'));

            if ($user == null) {
                return (new ModuleResult())
                    ->setCompleted(false)
                    ->setResponse(response(['error' => 'User is not found'], 422));
            }

            $subject = resolve(SubjectRepository::class)->with($request->input('username'), $this, $module);

            $this->sendEmail($subject, $module, $state);

            return $module->baseResult()->setSubject($subject)->setCompleted(false)->setResponse(response([]));
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
            ->withHeader('sub', $identifier)
            ->permittedFor(url('/'))
            // TODO: make expiration time configurable
            ->expiresAt(\DateTimeImmutable::createFromMutable((new \DateTime('+7200 seconds'))))
            ->issuedAt(new \DateTimeImmutable())
            ->withClaim('state', (string) $state);

        return $token->getToken($config->signer(), $config->signingKey());
    }
}
