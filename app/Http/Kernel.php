<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
use App\Http\Middleware\DetectTenant;
use App\Http\Middleware\CleanupParamaters;
use Idaas\Passport\Middleware\AllowClientCORS;
use Laravel\Passport\Http\Middleware\CheckScopes;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\TrustProxies::class,
        \App\Http\Middleware\AllowCORS::class
    ];

    protected $middlewarePriority = [
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        DetectTenant::class,
        \Illuminate\Auth\Middleware\Authenticate::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        CleanupParamaters::class,
        \Illuminate\Auth\Middleware\Authorize::class,
        \App\Http\Middleware\AllowCORS::class,
        AllowClientCORS::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            DetectTenant::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            CleanupParamaters::class
        ],

        'web.nosession' => [
            DetectTenant::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            CleanupParamaters::class
        ],

        'api' => [
            'api.nopolicy',
            'can:manage,App\Tenant',
            'scopes:applications:manage'
        ],

        'api.nopolicy' => [
            'auth:api',
            'cors.oauth',
            'throttle:20000,1',
            DetectTenant::class,
            'bindings',
            CleanupParamaters::class
        ],

        'api.noauth' => [
            'cors.always',
            'throttle:600,1',
            DetectTenant::class,
            'bindings',
            CleanupParamaters::class
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'cors.oauth' => AllowClientCORS::class,
        'cors.always' => \App\Http\Middleware\AllowCORS::class,
        'scopes' => CheckScopes::class,

    ];
}
