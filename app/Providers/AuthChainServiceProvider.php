<?php

/**
 * Laravel service provider for registering the routes and publishing the configuration.
 */

namespace App\Providers;

use App\AuthChain\AuthChain;
use App\AuthChain\Helper;
use App\Exceptions\AuthFailedException;
use App\Exceptions\NoStateException;
use App\Repository\ModuleRepository;
use App\Session\OIDCSession;

class AuthChainServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot(\Illuminate\Routing\Router $router)
    {

        $this->app->bind(
            \App\AuthChain\State::class,
            function ($app) {
                $state = Helper::getStateFromSession(request()->header('X-StateId'));

                /**
                 * Can occur in case (1) the login screen stays open
                 * very - very ! - long and (2) the state has expired in between.
                 */
                if ($state == null) {
                    throw new NoStateException('This state is no longer valid.');
                }

                return $state;
            }
        );

        $this->app->bind(
            \ArieTimmerman\Passport\OIDC\Session::class,
            OIDCSession::class
        );


        $this->app->singleton('App\AuthChain\AuthChain', AuthChain::class);

        /**
         * The module the users chooses to use for authentication
         */
        $router->bind(
            'module',
            function ($moduleId, $route) {
                $module = \resolve(ModuleRepository::class)->get($moduleId);

                if ($module == null) {
                    throw new AuthFailedException('Unknown module: ' . $moduleId);
                }

                return $module;
            }
        );

        /**
         * The state as originally created by us.
         * Uses to verify the client is allowed to initiate authentication attempts.
         */
        $router->bind(
            'state',
            function ($state, $route) {
                $state = Helper::getStateFromSession($state);

                if ($state == null) {
                    return response(null, 408);
                }

                // Save it as an instance so it's used for constructors etc
                $this->app->instance('App\AuthChain\State', $state);

                return $state;
            }
        );
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
