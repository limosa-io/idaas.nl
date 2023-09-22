<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repository\ModuleRepository;
use App\Repository\ChainRepository;
use App\Repository\SubjectRepository;
use App\Repository\LinkRepository;
use App\Repository\UserRepository;
use App\Repository\RemoteServiceProviderConfigRepository;
use App\Repository\HostedIdentityProviderConfigRepository;
use App\Repository\AuthLevelRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\AuthChain\AuthChain;
use App\AuthTypes\Anonymous;
use App\AuthTypes\Fido;
use App\AuthTypes\TOTP;
use App\Scim\PolicyDecisionPoint;
use App\Session\DatabaseSessionHandler;
use Illuminate\Database\ConnectionInterface;
use App\AuthTypes\OpenIDConnect;
use App\AuthTypes\OtpMail;
use App\AuthTypes\Passwordless;
use App\CloudFunction\DigitalOceanHandler;
use App\CloudFunction\HandlerInterface;
use App\CloudFunction\OpenWhiskHandler;
use Illuminate\Support\Facades\DB;
use App\SCIMConfig;
use App\SAMLConfig;
use App\Tenant;
use App\Exceptions\NoTenantException;
use Laravel\Passport\Token;
use App\Observers\TokenObserver;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\AuthChain\RememberStorage;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;
use App\PassportConfig;
use App\Repository\ClaimRepository;
use App\Repository\ClientRepository as RepositoryClientRepository;
use App\Repository\KeyRepository;
use App\Repository\OIDCUserRepository;
use App\Repository\ProviderRepository;
use App\Repository\TokenRepository;
use App\Session\OIDCSession;
use App\TokenCache;
use Exception;
use Idaas\OpenID\Repositories\UserRepositoryInterface;
use Idaas\Passport\ProviderRepository as PassportProviderRepository;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(ConnectionInterface $connection)
    {
        Session::extend(
            'databaseWithCache',
            function ($app) use ($connection) {
                return new DatabaseSessionHandler(
                    $connection,
                    $app['config']['session.table'],
                    $app['config']['session.lifetime'],
                    $app
                );
            }
        );

        if (config('app.env') == 'development') {
            DB::listen(
                function ($sql) {
                    Log::debug(json_encode($sql));
                }
            );
        }

        $tenant = null;

        if (!app()->runningInConsole()) {
            if (preg_match('/^((?!manage).+?)\./', request()->getHttpHost(), $matches)) {
                $subdomain = $matches[1];

                $tenant = Cache::remember(
                    'tenant:' . $subdomain,
                    10,
                    function () use ($subdomain) {
                        return Tenant::where('subdomain', $subdomain)->first();
                    }
                );

                \App\Http\Middleware\DetectTenant::activateTenant($tenant);
            }

            if ($tenant != null) {
            } else {
                throw new NoTenantException('No tenant!');
            }
        }

        if (config('serverless.driver') != null) {
            $this->app->singleton(HandlerInterface::class, match (config('serverless.driver')) {
                'openwhisk' => OpenWhiskHandler::class,
                'digitalocean' => DigitalOceanHandler::class,
                default => throw new Exception('Invalid serverless.driver specified')
            });
        }

        Token::observe(TokenObserver::class);

        $this->app->singleton(ChainRepository::class);
        $this->app->singleton(UserRepository::class);
        $this->app->singleton(AuthLevelRepository::class);

        $this->app->singleton(
            'ArieTimmerman\Laravel\SAML\Repository\RemoteServiceProviderConfigRepositoryInterface',
            RemoteServiceProviderConfigRepository::class
        );
        $this->app->singleton(
            'ArieTimmerman\Laravel\SAML\Repository\HostedIdentityProviderConfigRepositoryInterface',
            HostedIdentityProviderConfigRepository::class
        );

        $this->app->singleton(\Idaas\Passport\KeyRepository::class, KeyRepository::class);
        $this->app->singleton(\Idaas\Passport\PassportConfig::class, PassportConfig::class);
        $this->app->singleton(\Idaas\OpenID\Session::class, OIDCSession::class);

        $this->app->singleton(PassportProviderRepository::class, ProviderRepository::class);

        // TOOD: dit de reden dat clients niet goed opslaan?
        $this->app->singleton(\Laravel\Passport\ClientRepository::class, RepositoryClientRepository::class);


        $this->app->singleton('ArieTimmerman\Laravel\SCIMServer\PolicyDecisionPoint', PolicyDecisionPoint::class);
        $this->app->singleton('ArieTimmerman\Laravel\SCIMServer\SCIMConfig', SCIMConfig::class);
        $this->app->singleton('ArieTimmerman\Laravel\SAML\SAMLConfig', SAMLConfig::class);


        $this->app->singleton(
            \App\Repository\ConsentRepository::class
        );

        $this->app->singleton(UserRepositoryInterface::class, OIDCUserRepository::class);

        $this->app->singleton(\Idaas\Passport\TokenCache::class, TokenCache::class);
        $this->app->singleton(\Laravel\Passport\TokenRepository::class, TokenRepository::class);
        $this->app->singleton(\Idaas\Passport\Bridge\ClaimRepository::class, ClaimRepository::class);
        $this->app->singleton(\LAravel\Passport\Bridge\UserRepository::class, UserRepository::class);

        // $this->app->singleton(
        //     \Laravel\Passport\Bridge\RefreshTokenRepository::class,
        //     RefreshTokenRepository::class
        // );

        $this->app->singleton(
            RememberStorage::class
        );

        // TODO: weer aanzetten
        // $this->app->singleton(
        //     ResourceServer::class,
        //     function () {
        //         return new ResourceServerCustom(
        //             $this->app->make(AccessTokenRepository::class),
        //             new AdvancedBearerTokenValidator($this->app->make(AccessTokenRepository::class))
        //         );
        //     }
        // );

        // allowed types
        AuthChain::addType(TOTP::class);
        AuthChain::addType(OpenIDConnect::class);
        AuthChain::addType(Passwordless::class);
        AuthChain::addType(Anonymous::class);
        AuthChain::addType('\App\AuthTypes\Activation');
        AuthChain::addType('\App\AuthTypes\PasswordForgotten');
        AuthChain::addType('\App\AuthTypes\Register');
        AuthChain::addType(OtpMail::class);

        AuthChain::addType('\App\AuthTypes\Facebook');
        AuthChain::addType('\App\AuthTypes\Google');
        AuthChain::addType('\App\AuthTypes\Github');
        AuthChain::addType('\App\AuthTypes\Linkedin');
        AuthChain::addType('\App\AuthTypes\Twitter');
        AuthChain::addType('\App\AuthTypes\Password');
        AuthChain::addType('\App\AuthTypes\Consent');
        AuthChain::addType('\App\AuthTypes\Start');
        AuthChain::addType(Fido::class);

        View::composer(
            '*',
            function ($view) {
                $view->with('nonce', Controller::nonce());
            }
        );
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Passport::ignoreMigrations();
    }
}
