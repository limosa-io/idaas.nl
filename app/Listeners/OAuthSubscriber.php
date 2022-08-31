<?php

namespace App\Listeners;

use App\Stats\Statter;
use App\Client;
use Laravel\Passport\Events\AccessTokenCreated;
use Laravel\Passport\Events\RefreshTokenCreated;
use Laravel\Passport\Token;

class OAuthSubscriber
{
    protected $settings = null;

    public function onAccessTokenCreated(AccessTokenCreated $event)
    {
        Statter::emit(Client::find($event->clientId), 'access_token', $event->userId);
    }

    public function onRefreshTokenCreated(RefreshTokenCreated $event)
    {
        $token = Token::find($event->accessTokenId);

        Statter::emit(Client::find($token->client_id), 'refresh_token', $token->user_id);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Laravel\Passport\Events\AccessTokenCreated',
            'App\Listeners\OAuthSubscriber@onAccessTokenCreated'
        );

        $events->listen(
            'Laravel\Passport\Events\RefreshTokenCreated',
            'App\Listeners\OAuthSubscriber@onRefreshTokenCreated'
        );
    }
}
