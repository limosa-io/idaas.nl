<?php

/**
 * Ensures unique checks are done on tenant level
 */

namespace App\Providers;

use Illuminate\Validation\DatabasePresenceVerifier;

class ValidationServiceProvider extends \Illuminate\Validation\ValidationServiceProvider
{
    protected function registerPresenceVerifier()
    {
        $this->app->singleton(
            'validation.presence',
            function ($app) {
                return new class($app['db']) extends DatabasePresenceVerifier
                {
                    public function table($table)
                    {
                        // Apply the tenant scope for all tables, except the 'tenants' table itself
                        return $table != 'tenants' ?
                            parent::table($table)->where('tenant_id', resolve('App\Tenant')->id) :
                            parent::table($table);
                    }
                };
            }
        );
    }
}
