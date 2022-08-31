<?php

namespace App;

use App\Model;
use App\Scopes\TenantTrait;

class Group extends Model
{
    public function members()
    {
        return $this->belongsToMany('App\User')
            ->wherePivot('tenant_id', resolve('App\Tenant')->id)->using('App\TenantPivot');
    }
}
