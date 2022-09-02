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
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;

class AuthorizationController extends IdaasAuthorizationController
{
    public function continueAuthorizeWtithState(
        State $state,
        $authRequest,
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

        $config = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::plainText($privateKey->getKeyContents()),
            InMemory::plainText($privateKey->getKeyContents())
        );

        $token = $config->builder()
            ->withHeader('kid', method_exists($privateKey, 'getKid') ? $privateKey->getKid() : null)
            ->issuedBy(url('/'))
            ->withHeader('sub', $request->input('subject'))
            ->permittedFor(url('/'))
            ->expiresAt(\DateTimeImmutable::createFromMutable((new \DateTime('+300 seconds'))))
            ->issuedAt(new \DateTimeImmutable())
            ->withClaim('state', $request->input('state'))
            ->withClaim('nonce', $request->input('nonce'));
    }
}
