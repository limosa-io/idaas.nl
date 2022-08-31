<?php

namespace App\Scopes;

use Auth;

trait TenantTrait
{
    // abstract protected function doCreating($model);

    public static function bootTenantTrait()
    {
        // Scope queries
        static::addGlobalScope(new TenantScope());

        static::creating(
            function ($model) {
                return self::doCreating($model);
            }
        );
    }

    protected static function doCreating($model)
    {
        // Set the tenant_id upon creation
        $model->tenant_id = resolve('App\Tenant')->id;
    }

    public function tenant()
    {
        return $this->belongsTo('App\Tenant');
    }
}
