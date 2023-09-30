<?php

/**
 * Design a cache system that holds information of 10.000 tokens max?
 * Perhaps by storing the tokenId in the cache itself? Allows very short key sizes. Acceptable if look-up is faster.
 * Or really short expiration times that auto increase upon usage?
 */

namespace App;

use Idaas\Passport\TokenCache as IdaasTokenCache;
use Illuminate\Support\Facades\Cache;

class TokenCache extends IdaasTokenCache
{
    /**
     * TODO: Store in hashmap. Only first xx characters? rest in map.
     */
    public function rememberUserInfo(string $tokenId, $closure)
    {
        return Cache::remember('info:'.$tokenId, 10, $closure);
    }

    /**
     * Unset this cache if the token gets revoked. Or client deleted/revoked/updated
     */
    public function rememberUser(string $tokenId, $closure)
    {
        return Cache::remember('user:'.$tokenId, 10, $closure);
    }

    /**
     * Unset this cache if client gets deleted/revoked/updated
     */
    public function rememberOriginAllowed(?string $origin, $closure)
    {
        return Cache::remember(sha1($origin), 10, $closure);
    }

    /**
     * TODO: Instruct specific server to clear cache
     */
    public function clearCacheForToken(string $tokenId)
    {
        Cache::forget('info:'.$tokenId);
        Cache::forget('user:'.$tokenId);
    }
}
