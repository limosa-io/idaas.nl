<?php

namespace App\Providers;

use Idaas\Passport\Passport;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapManageApiRoutes();
        $this->mapManageWebRoutes();

        Route::middleware(['api'])->domain('{tenant}.manage.' . config('app.domain'))->group(
            function () {
                AuthRouteProvider::manageRoutes();
            }
        );

        Passport::routes(
            function ($router) {
                $router->forManagement();
            },
            ['domain' => '{tenant}.manage.' . config('app.domain')]
        );

        Route::middleware('api.noauth')->get('/api/logout', 'App\Http\Controllers\LogoutController@logout');

        $this->mapLoginApiRoutes();
        $this->mapLoginWebRoutes();

        Passport::routes(
            function ($router) {
                $router->forUserinfo();
                $router->forIntrospect();
            },
            ['middleware' => ['api.nopolicy'], 'domain' => '{tenant}.' . config('app.domain')]
        );

        Passport::routes(
            function ($router) {
                $router->forAuthorization();
                $router->forOIDCClients();

                Route::middleware('api')->put(
                    '/connect/register/{client_id}',
                    '\App\Http\Controllers\Manage\ClientController@update'
                )->name('oidc.manage.client.replace');
            },
            ['middleware' => ['web'], 'domain' => '{tenant}.' . config('app.domain')]
        );

        Passport::routes(
            function ($router) {
                $router->forWellKnown();
                $router->forAccessTokens();
            },
            ['prefix' => '', 'middleware' => ['api.noauth'], 'domain' => '{tenant}.' . config('app.domain')]
        );

        Route::middleware(['api.nopolicy'])->prefix('api')->domain('{tenant}.manage.' . config('app.domain'))->group(
            function () {
                \ArieTimmerman\Laravel\SCIMServer\RouteProvider::meRoutes();

                Route::get(
                    '/hotp_secret_generator',
                    '\App\Http\Controllers\ManageTOTPController@generateSecuret'
                );

                Route::get(
                    '/fido_get_create_arguments',
                    '\App\Http\Controllers\ManageFidoController@getCreateArguments'
                );

                Route::get(
                    '/fido_list_keys',
                    '\App\Http\Controllers\ManageFidoController@listFidoKeys'
                );

                Route::delete(
                    '/fido/{fidoKey}',
                    '\App\Http\Controllers\ManageFidoController@delete'
                );

                Route::post(
                    '/fido_register',
                    '\App\Http\Controllers\ManageFidoController@register'
                );

                Route::post('/updateEmail', 'App\Http\Controllers\SCIMMeController@updateEmail');

                Route::resource('tenants', 'App\Http\Controllers\TenantController')->only(
                    [
                        'index', 'store', 'update'
                    ]
                );
            }
        );

        Route::middleware(['api.noauth'])->prefix('api')->domain('{tenant}.manage.' . config('app.domain'))->group(
            function () {
                Route::post('/scim/v2/Me', 'App\Http\Controllers\SCIMMeController@createMe')->name('scim.me.post');

                // TODO: this is not working for some reason why not?
                // \ArieTimmerman\Laravel\SCIMServer\RouteProvider::publicRoutes();
            }
        );
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapLoginWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/login/web.php'));
    }

    protected function mapManageWebRoutes()
    {
        Route::middleware('web.nosession')
            ->namespace($this->namespace)
            ->group(base_path('routes/manage/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapLoginApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api.noauth')
            ->namespace($this->namespace)
            ->group(base_path('routes/login/api.php'));
    }

    protected function mapManageApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/manage/api.php'));
    }
}
