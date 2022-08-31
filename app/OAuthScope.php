<?php

namespace App;

use App\Model;
use App\Scopes\TenantTrait;
use App\Scopes\TenantScope;
use App\Scopes\SystemScope;

class OAuthScope extends Model
{
    protected $hidden = [

    ];

    protected $casts = [
        'system'    => 'boolean'
    ];

    public function provider()
    {
        return $this->belongsTo('App\OpenIDProvider', 'provider_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new SystemScope());
    }
}
