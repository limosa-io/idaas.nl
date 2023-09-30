<?php

namespace App;

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
