<?php

namespace App\AuthTypes;

use App\AuthChain\ModuleInterface;
use App\AuthChain\State;
use App\AuthChain\Subject;
use Exception;
use Illuminate\Http\Request;
use lbuchs\WebAuthn\WebAuthn;

class Fido extends AbstractType
{
    public function getDefaultName()
    {
        return 'FIDO 2';
    }

    protected function getWebAuth(): WebAuthn
    {
        return new WebAuthn(config('app.domain'), config('app.domain'));
    }

    public function isEnabled(?Subject $subject)
    {
        return $subject != null && $subject->getUser() != null && $subject->getUser()->fidoKeys()->first() != null;
    }

    public function process(Request $request, State $state, ModuleInterface $module)
    {
        if ($request->input('init')) {
            $credentialIds = $state->getSubject()->getUser()->fidoKeys()->get('credential_id')->pluck('credential_id')->map(
                function ($a) {
                    return base64_decode($a);
                }
            )->toArray();

            $webAuth = $this->getWebAuth();

            return $module->baseResult()->setResponse(
                response(
                    [
                        'arguments' => $webAuth->getGetArgs($credentialIds, 20),
                        'challenge' => $webAuth->getChallenge()->getHex(),
                    ]
                )
            )->setCompleted(false);
        } elseif ($request->input('verify')) {
            $webAuth = $this->getWebAuth();

            $response = $request->input('response');

            $clientDataJSON = base64_decode($response['clientDataJSON']);
            $authenticatorData = base64_decode($response['authenticatorData']);
            $signature = base64_decode($response['signature']);
            $userHandle = base64_decode($response['userHandle']);
            $id = base64_decode($response['id']);

            $challenge = \lbuchs\WebAuthn\Binary\ByteBuffer::fromHex($request->input('challenge'));
            $credentialPublicKey = $state->getSubject()->getUser()->fidoKeys()->where('credential_id', $response['id'])->first()->credential_public_key;

            if ($credentialPublicKey === null) {
                throw new Exception('Public Key for credential ID not found!');
            }

            // process the get request. throws WebAuthnException if it fails
            $webAuth->processGet($clientDataJSON, $authenticatorData, $signature, $credentialPublicKey, $challenge, null);

            return $module->baseResult()->setCompleted(true);
        }
    }
}
