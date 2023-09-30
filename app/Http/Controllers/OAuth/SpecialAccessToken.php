<?php

namespace App\Http\Controllers\OAuth;

use DateTimeImmutable;
use Idaas\OpenID\CryptKey;
use Laravel\Passport\Bridge\AccessToken;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;

class SpecialAccessToken extends AccessToken
{
    protected $verified = null;

    public function convertToJWT(CryptKey $privateKey)
    {
        $config = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::plainText($privateKey->getKeyContents()),
            InMemory::plainText($privateKey->getKeyContents())
        );

        $token = $config->builder()
            ->withHeader('kid', method_exists($privateKey, 'getKid') ? $privateKey->getKid() : null)
            ->identifiedBy($this->getIdentifier())
            ->permittedFor($this->getClient()->getIdentifier())
            ->relatedTo($this->getUserIdentifier())
            ->canOnlyBeUsedAfter(new DateTimeImmutable())
            ->expiresAt($this->getExpiryDateTime())
            ->issuedAt(new DateTimeImmutable())
            ->withClaim('scopes', $this->getScopes());

        if ($this->verified != null) {
            $token->withClaim('verified', $this->verified);
        }

        return $token->getToken($config->signer(), $config->signingKey());
    }

    public function addVerified(array $verified)
    {
        $this->verified = $verified;
    }
}
