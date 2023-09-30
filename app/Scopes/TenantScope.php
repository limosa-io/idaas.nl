<?php

namespace App\Scopes;

use App\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        //TODO: This is a non-ideal check. Used to make the job runner work
        if (resolve('App\Tenant') == null && app()->runningInConsole()) {
        } elseif (resolve('App\Tenant')->master && $model instanceof Role) {
        } else {
            $builder->where($model->getTable().'.tenant_id', '=', resolve('App\Tenant')->id);
        }
    }
}
