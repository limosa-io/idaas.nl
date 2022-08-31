<?php

/**
 * Used as a token observer.
 * Set with `Laravel\Passport\Token::observe` in order to clear the caches if a token is revoked.
 * Only works in a replicated or single redis environment.
 */

namespace App\Observers;

use Laravel\Passport\Token;
use App\TokenCache;

class TokenObserver
{
    /**
     * Handle the User "updated" event.
     *
     * @param  \App\User $user
     * @return void
     */
    public function updated(Token $token)
    {
        resolve(TokenCache::class)->clearCacheForToken($token->id);
    }

    /**
     * Handle the User "deleted" event.
     *
     * @param  \App\User $user
     * @return void
     */
    public function deleted(Token $token)
    {
        resolve(TokenCache::class)->clearCacheForToken($token->id);
    }
}
