<?php
/**
 * Remove the tenant parameter. Needed for other component routes to work.
 */
namespace App\Http\Middleware;

use Closure;

class CleanupParamaters
{

    public function handle($request, Closure $next)
    {
        
        $request->route()->forgetParameter('tenant');
        
        return $next($request);

    }

}