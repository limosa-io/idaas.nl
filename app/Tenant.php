<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Http\Middleware\DetectTenant;
use App\Scopes\TenantScope;
use Illuminate\Support\Str;

class Tenant extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'subdomain','master'
    ];

    protected $casts = [
        'master' => 'boolean'
    ];

    protected $guarded = ['id', 'tenant_id'];

    protected $appends = ['main_url'];

    protected static function boot()
    {
        parent::boot();

        static::creating(
            function ($model) {
                $model->resources_version = 'W/' . Str::random(8);
                $model->{$model->getKeyName()} = (string) Str::orderedUuid();
            }
        );
    }

    public function updateVersion()
    {
        $this->resources_version = 'W/' . Str::random(8);
    }

    public function clients()
    {
        return $this->hasMany('App\Client', 'tenant_id')->withoutGlobalScope(TenantScope::class);
    }

    public function users()
    {
        return $this->hasMany('App\User', 'tenant_id')->withoutGlobalScope(TenantScope::class);
    }

    public function settings()
    {
        return $this->hasMany(TenantSetting::class);
    }

    public function getMainUrlAttribute()
    {
        return route(
            'ice.manage.home',
            [
            'tenant' => $this->subdomain
            ]
        );
    }

    public function do(callable $f)
    {
        $original = resolve('App\Tenant');

        DetectTenant::activateTenant($this);

        $f($this);

        if ($original != null) {
            DetectTenant::activateTenant($original);
        }
    }
}
