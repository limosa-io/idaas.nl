<?php

namespace App;

use App\Model;
use ArieTimmerman\Laravel\AuthChain\AuthChain;
use App\Scopes\SystemScope;
use App\Scopes\TenantTrait;
use ArieTimmerman\Laravel\AuthChain\Module\Module;
use Illuminate\Support\Str;

class AuthModule extends Module
{
    use TenantTrait {
        doCreating as traitDoCreating;
    }

    public $withConfig = false;

    protected $with = ['authLevels'];

    protected $casts = [
        'config' => 'array',
        'skippable' => 'boolean',
        'system' => 'boolean',
        'enabled' => 'boolean',
        'hide_if_not_requested' => 'boolean'
    ];

    protected $guarded = ['id', 'tenant_id'];
    protected $hidden = ['tenant_id'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new SystemScope());

        static::creating(
            function ($model) {
                return self::doCreating($model);
            }
        );
    }

    protected static function doCreating($model)
    {
        self::traitDoCreating($model);

        $model->skippable = true;
        $model->{$model->getKeyName()} = (string) Str::orderedUuid();
    }

    public function fromChain()
    {
        return $this->hasMany('App\AuthChain', 'from');
    }

    public function toChain()
    {
        return $this->hasMany('App\AuthChain', 'to');
    }

    public function getIdentifier()
    {
        return $this->attributes['id'];
    }

    public function getPublicConfig()
    {
        return $this->attributes['public_config'];
    }

    public function setPublicConfig(?array $config)
    {
        $this->public_config = $this->attributes['public_config'];
    }

    public function authLevels()
    {
        return $this->belongsToMany(
            '\App\AuthLevel',
            'authmodule_authlevel'
        )->wherePivot('tenant_id', resolve('App\Tenant')->id)->using('App\TenantPivot');
    }

    public function getLevels()
    {
        return $this->authLevels->all();
    }

    public function syncLevels(array $levels)
    {
        $this->authLevels()->sync($levels);
    }

    /**
     * Ensures 'config' is added to the json representation
     */
    public function withConfig()
    {
        $this->withConfig = true;

        return $this;
    }

    public function jsonSerialize(): array
    {
        $result = parent::jsonSerialize();

        if (!$this->withConfig && isset($result['config'])) {
            $allowed = $this->getTypeObject()->getPublicConfigKeys();
            $result['config'] = array_intersect_key($result['config'], array_combine($allowed, $allowed));
        }

        $result['levels'] = $this->authLevels;

        $result['system'] = $this->system;

        if (isset($this->relations['fromChain'])) {
            $result['chained'] = $this->fromChain()->exists() || $this->toChain()->exists();
        }

        if (!isset($result['skippable'])) {
            $result['skippable'] = false;
        }

        return $result;
    }
}
