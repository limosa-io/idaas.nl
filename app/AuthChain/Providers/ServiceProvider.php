<?php

/**
 * Laravel service provider for registering the routes and publishing the configuration.
 */

namespace App\AuthChain\Providers;

use App\Session\OIDCSession;
use App\AuthChain\Exceptions\AuthFailedException;
use App\AuthChain\Helper;
use App\AuthChain\AuthChain;
use App\AuthChain\Repository\ChainRepository;
use App\AuthChain\Repository\UserRepository;
use App\AuthChain\Repository\LinkRepository;
use App\AuthChain\Repository\SubjectRepository;
use App\AuthChain\Http\CompleteProcessor;
use App\AuthChain\Exceptions\NoStateException;
use App\Repository\ModuleRepository;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
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


        $this->app->bindIf('App\AuthChain\Repository\LinkRepositoryInterface', LinkRepository::class);
        $this->app->bindIf('App\AuthChain\Repository\SubjectRepositoryInterface', SubjectRepository::class);


        $this->app->singleton('App\AuthChain\AuthChain', AuthChain::class);

        AuthChain::addType('\App\AuthChain\Types\Password');
        AuthChain::addType('\App\AuthChain\Types\Consent');
        AuthChain::addType('\App\AuthChain\Types\Start');

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
