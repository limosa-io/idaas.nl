<?php

namespace App\Repository;

use App\Tenant;
use Laravel\Passport\Events\RefreshTokenCreated;
use Laravel\Passport\RefreshTokenRepository as LaravelRefreshTokenRepository;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;

class RefreshTokenRepository extends LaravelRefreshTokenRepository
{
    /**
     * {@inheritdoc}
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {
        $this->database->table('oauth_refresh_tokens')->insert(
            [
            'id' => $id = $refreshTokenEntity->getIdentifier(),
            'access_token_id' => $accessTokenId = $refreshTokenEntity->getAccessToken()->getIdentifier(),
            'revoked' => false,
            'expires_at' => $refreshTokenEntity->getExpiryDateTime(),
            'tenant_id' => resolve(Tenant::class)->id
            ]
        );

        $this->events->dispatch(new RefreshTokenCreated($id, $accessTokenId));
    }

    /**
     * {@inheritdoc}
     */
    public function revokeRefreshToken($tokenId)
    {
        $this->database->table('oauth_refresh_tokens')
            ->where('tenant_id', resolve(Tenant::class)->id)->where('id', $tokenId)->update(['revoked' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public function isRefreshTokenRevoked($tokenId)
    {
        $refreshToken = $this->database->table('oauth_refresh_tokens')
            ->where('tenant_id', resolve(Tenant::class)->id)->where('id', $tokenId)->first();

        if ($refreshToken === null || $refreshToken->revoked) {
            return true;
        }

        return $this->tokens->isAccessTokenRevoked(
            $refreshToken->access_token_id
        );
    }
}
