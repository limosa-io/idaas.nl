<?php

namespace App\Http\Middleware;

use App\Http\Controllers\HomeController;
use Closure;
use Illuminate\Contracts\Encryption\DecryptException;

class AllowCORS
{
    public $headers = 'Content-Type, X-AuthRequest, Authorization, x-challenge';

    public $exposeHeaders = 'x-scim-proof-of-creation, x-challenge';

    /**
     * Create a new middleware instance.
     *
     * @param  \League\OAuth2\Server\ResourceServer  $server
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  ...$scopes
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if ($request->header('origin')) {
            //TODO: Check the request is coming from the UI Server.

            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Allow-Headers', $this->headers);
            $response->headers->set('Access-Control-Expose-Headers', $this->exposeHeaders);
            $response->headers->set('Access-Control-Allow-Origin', $request->header('origin', '*'));
            $response->headers->set('Vary', 'Origin');
            $response->headers->set('Access-Control-Max-Age', '3600');
        }

        if (
            ! $response->headers->has('Content-Security-Policy')
            && ($cookie = $request->cookie(HomeController::COOKIE_FRAME_INFO)) != null
        ) {
            try {
                $decrypted = decrypt($cookie);

                $response->headers->set(
                    'Content-Security-Policy',
                    'frame-ancestors '.$decrypted.';'
                );
            } catch (DecryptException $ignore) {
                // ignore
            }
        }

        return $response;
    }
}
