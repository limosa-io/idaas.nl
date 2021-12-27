<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Model;

class TenantPivot extends Pivot
{
    
    protected $guarded = ['id'];

    public static function fromRawAttributes(Model $parent, $attributes, $table, $exists = false)
    {

        $attributes['tenant_id'] = resolve('App\Tenant')->id;
        return parent::fromRawAttributes($parent, $attributes, $table, $exists);

    }

}