<?php

namespace App\Http\Controllers\OAuth;

use App\Repository\AccessTokenRepository;
use App\Repository\KeyRepository;
use DateInterval;
use Exception;
use Idaas\Passport\Bridge\ClientRepository;
use League\OAuth2\Server\Grant\AbstractGrant;
use Psr\Http\Message\ServerRequestInterface;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;

class ClientWithoutCredentialsGrant extends AbstractGrant
{
    protected $client_id;

    public function __construct($client_id)
    {
        $this->client_id = $client_id;

        $this->setAccessTokenRepository(
            resolve(AccessTokenRepository::class)
        );
        $this->setPrivateKey(
            resolve(KeyRepository::class)->getPrivateKey()
        );
    }

    public function getToken()
    {
        /** @var ClientRepository */
        $clientRepository = resolve(ClientRepository::class);

        $client = $clientRepository->getClientEntity(
            $this->client_id
        );

        return $this->issueAccessToken(
            new DateInterval('PT2M'),
            $client,
            $client->getIdentifier()
        );
    }

    public function getIdentifier()
    {

        return 'no-grant';
    }

    public function respondToAccessTokenRequest(ServerRequestInterface $request, ResponseTypeInterface $responseType, DateInterval $accessTokenTTL)
    {
        throw new Exception("Not implemented");
    }
}
