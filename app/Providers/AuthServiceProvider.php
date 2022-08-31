<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\SubjectProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Tenant' => 'App\Policies\TenantPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Auth::provider(
            'chainusers',
            function ($app, array $config) {
                // Return an instance of Illuminate\Contracts\Auth\UserProvider...
                return new SubjectProvider();
            }
        );

        Passport::enableImplicitGrant();
    }
}
