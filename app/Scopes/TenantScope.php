<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Role;
use App\Tenant;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @param  \Illuminate\Database\Eloquent\Model   $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {   
        //TODO: This is a non-ideal check. Used to make the job runner work
        if(resolve('App\Tenant') == null && app()->runningInConsole()) {

        } else if(resolve('App\Tenant')->master && $model instanceof Role) {
            
        }else{
            $builder->where($model->getTable() . '.tenant_id', '=', resolve('App\Tenant')->id);
        }
        
    }
    
}