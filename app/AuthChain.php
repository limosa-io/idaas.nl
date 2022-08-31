<?php

namespace App;

use ArieTimmerman\Laravel\AuthChain\Module\Module;
use App\Model;
use ArieTimmerman\Laravel\AuthChain\Module\ModuleTrait;
use ArieTimmerman\Laravel\AuthChain\Module\ChainInterface;
use App\Scopes\TenantTrait;
use App\Scopes\SortChainScope;

class AuthChain extends Model implements ChainInterface
{
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new SortChainScope());
    }

    public function getFrom()
    {
        return $this->from;
    }
    public function getTo()
    {
        return $this->to;
    }
}
