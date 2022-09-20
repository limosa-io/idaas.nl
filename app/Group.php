<?php

namespace App;

use App\Model;
use App\Scopes\TenantTrait;

class Group extends Model
{
    public function members()
    {
        return $this
            ->belongsToMany(User::class)
            ->wherePivot('tenant_id', resolve('App\Tenant')->id)
            ->using(TenantPivot::class);
    }
}
