<?php

/**
 * This listener ensures access tokens with scope online_access get revoked upon session revocation
 */

namespace App\Listeners;

use Illuminate\Support\Facades\Session;
use Laravel\Passport\Events\AccessTokenCreated;

class OnlineAccessTokenSubscriber
{
    /**
     * Handle user logout events.
     */
    public function onAccessTokenCreated(AccessTokenCreated $event)
    {
        //TODO: This only works for the implicit flow...
        Session::push('access_tokens', $event->tokenId);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Laravel\Passport\Events\AccessTokenCreated',
            'App\Listeners\OnlineAccessTokenSubscriber@onAccessTokenCreated'
        );
    }
}
