<?php

namespace App\Http\Controllers\OAuth;

use Illuminate\Http\Request;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\Key;
use ArieTimmerman\Laravel\AuthChain\State;
use Idaas\Passport\ClientRepository;
use Idaas\Passport\Http\Controllers\AuthorizationController as IdaasAuthorizationController;
use Idaas\Passport\KeyRepository;
use Laravel\Passport\TokenRepository;

class AuthorizationController extends IdaasAuthorizationController
{
    public function continueAuthorizeWtithState(
        State $state,
        $authRequest = null,
        Request $request,
        ClientRepository $clients,
        TokenRepository $tokens
    ) {
        if ($authRequest == null) {
            $authRequest = $state->data;
        }
        
        if (!$state->isCompleted()) {
            return $this->returnError($authRequest);
        }
        
        return parent::continueAuthorize($authRequest, $request, $clients, $tokens);
    }


    public function initTest(Request $request)
    {
        $request->input('state');
        $request->input('nonce');

        $privateKey = resolve(KeyRepository::class)->getPrivateKey();

        return (new Builder())->setHeader('kid', $privateKey->getKid())
            ->setIssuer(url('/'))
            ->setSubject($request->input('subject'))
            ->setAudience(url('/'))
            ->setExpiration((new \DateTime('+300 seconds'))->getTimestamp())
            ->setIssuedAt((new \DateTime())->getTimestamp())
            ->set('state', $request->input('state'))
            ->set('nonce', $request->input('nonce'))
            ->sign(new Sha256(), new Key($privateKey->getKeyPath(), $privateKey->getPassPhrase()))
            ->getToken();
    }
}
