<?php
/**
 * Make use of a special access token.
 * This special access token MAY contain a 'verified' claim. Used to issue special access tokens needed for updating verified attributes, such as emails are phone numbers.
 * 
 * The special access token is usually obtained by (1) letting the user retrieve a one-time token via mail/sms, (2) let the user exchange this token via an STS for an access token with 'verified' claim
 */
namespace App\Repository;

use App\Http\Controllers\OAuth\SpecialAccessToken;
use Idaas\Passport\Bridge\AccessTokenRepository as IdaasAccessTokenRepository;
use League\OAuth2\Server\Entities\ClientEntityInterface;

class AccessTokenRepository extends IdaasAccessTokenRepository
{

    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
    {
        return new SpecialAccessToken($userIdentifier, $scopes, $clientEntity);
    }

}