<?php

namespace App\Http\Middleware;

use App\Exceptions\NoTenantException;
use App\Stats\Statter;
use App\Tenant;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class DetectTenant
{
    public static function activateTenant(Tenant $tenant = null)
    {
        if ($tenant == null) {
            throw new NoTenantException('No tenant!');
        }

        //Ensures the domain-paramaters get populated. Used for url generation.
        app()->instance('App\Tenant', $tenant);

        URL::defaults(['tenant' => $tenant->subdomain]);
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
        self::activateTenant(resolve('App\Tenant'));

        $result = $next($request);

        $subject = Auth::user();

        if ($subject != null) {
            Statter::emit($subject, 'visit', $request->path());
        }

        Statter::save();

        return $result;
    }
}
