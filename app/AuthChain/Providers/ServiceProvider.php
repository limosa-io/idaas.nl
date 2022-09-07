<?php

/**
 * Laravel service provider for registering the routes and publishing the configuration.
 */

namespace App\AuthChain\Providers;

use App\Session\OIDCSession;
use App\AuthChain\Exceptions\AuthFailedException;
use App\AuthChain\Helper;
use App\AuthChain\AuthChain;
use App\AuthChain\Repository\ModuleRepository;
use App\AuthChain\Repository\ChainRepository;
use App\AuthChain\Repository\UserRepository;
use App\AuthChain\Repository\LinkRepository;
use App\AuthChain\Repository\SubjectRepository;
use App\AuthChain\Http\CompleteProcessor;
use App\AuthChain\Exceptions\NoStateException;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot(\Illuminate\Routing\Router $router)
    {
        $this->publishes(
            [
                __DIR__ .
                DIRECTORY_SEPARATOR .
                '..' .
                DIRECTORY_SEPARATOR .
                '..' .
                DIRECTORY_SEPARATOR .
                'config' .
                DIRECTORY_SEPARATOR .
                'authchain.php' => config_path('authchain.php'),
            ]
        );

        $this->publishes(
            [
                __DIR__ . '/../../database/migrations/' => database_path('migrations')
            ],
            'migrations'
        );


        // Do not load migrations for now ...
        $this->loadRoutesFrom(__DIR__ . '/../../routes/routes.php');

        $this->loadViewsFrom(__DIR__ . '/../../views/', 'authchain');

        if ($this->app->runningInConsole()) {
            $this->commands(
                []
            );
        }

        $this->app->bind(
            'App\AuthChain\State',
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

        $this->app->bindIf(
            'App\AuthChain\Repository\ModuleRepositoryInterface',
            ModuleRepository::class
        );

        $this->app->bind(
            \ArieTimmerman\Passport\OIDC\Session::class,
            OIDCSession::class
        );

        $this->app->bindIf('App\AuthChain\Repository\ChainRepositoryInterface', ChainRepository::class);

        $this->app->bindIf('App\AuthChain\Repository\UserRepositoryInterface', UserRepository::class);
        $this->app->bindIf('App\AuthChain\Repository\LinkRepositoryInterface', LinkRepository::class);
        $this->app->bindIf('App\AuthChain\Repository\SubjectRepositoryInterface', SubjectRepository::class);

        $this->app->bindIf('App\AuthChain\Http\CompleteProcessorInterface', CompleteProcessor::class);

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
                $module = \resolve('App\AuthChain\Repository\ModuleRepositoryInterface')->get($moduleId);

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
        $this->mergeConfigFrom(
            __DIR__ .
            DIRECTORY_SEPARATOR .
            '..' .
            DIRECTORY_SEPARATOR .
            '..' .
            DIRECTORY_SEPARATOR .
            'config' .
            DIRECTORY_SEPARATOR .
            'authchain.php',
            'authchain'
        );
    }
}
