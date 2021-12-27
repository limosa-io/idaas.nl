<?php

namespace App\Http\Controllers\OAuth;

use Idaas\Passport\Bridge\ClientRepository;
use League\OAuth2\Server\Grant\AbstractGrant;
use Psr\Http\Message\ServerRequestInterface;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Lcobucci\JWT\Parser;
use League\OAuth2\Server\Exception\OAuthServerException;
use Illuminate\Contracts\Encryption\DecryptException;
use Laravel\Passport\TokenRepository;
use League\OAuth2\Server\AuthorizationValidators\BearerTokenValidator;

class STSGrant extends AbstractGrant
{

    public function getIdentifier()
    {
        return 'urn:ietf:params:oauth:grant-type:token-exchange';
    }

    public function respondToAccessTokenRequest(
        ServerRequestInterface $request,
        ResponseTypeInterface $responseType,
        \DateInterval $accessTokenTTL
    ) {

        $body = $request->getParsedBody();

        if (!isset($body['subject_token_type']) || $body['subject_token_type'] != 'io.orange:one-time-token') {
            throw new OAuthServerException('Invalid subject_token_type', 400, 'invalid_subject_type');
        }

        try {
            $decrypted = decrypt($body['subject_token']);
        } catch (DecryptException $e) {
            throw new OAuthServerException('Invalid subject_token', 400, 'subject_token');
        }

        /* @var $parser Lcobucci\JWT\Parser */
        $parser = resolve(Parser::class);

        $validator = resolve(BearerTokenValidator::class);

        $header = $request->getHeader('authorization');
        $jwt = trim(preg_replace('/^(?:\s+)?Bearer\s/', '', $header[0]));

        // die($jwt);
        $token = $parser->parse($jwt);
        // var_dump($token->getClaim('aud'));exit;

        $validator->ensureValidity($token);

        $tokenEloquent = resolve(TokenRepository::class)->find($token->getClaim('jti'));
        $client = resolve(ClientRepository::class)->getClientEntity($tokenEloquent->client_id, 'authorization_code', null, false);

        if ($decrypted['user_id'] != $tokenEloquent->user_id) {
            throw new OAuthServerException('Invalid subject_token', 400, 'subject_token');
        }

        /* @var $accessToken \App\Http\Controllers\OAuth\SpecialAccessToken */
        $accessToken = $this->issueAccessToken($accessTokenTTL, $client, $tokenEloquent->user_id, $this->validateScopes(implode(self::SCOPE_DELIMITER_STRING, $tokenEloquent->scopes)));

        unset($decrypted['user_id']);
        $accessToken->addVerified($decrypted);

        $responseType->setAccessToken($accessToken);

        return $responseType;
    }
}
