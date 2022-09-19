<?php

namespace App;

use App\AuthChain\Module\Module;
use App\Model;
use App\AuthChain\Module\ModuleTrait;
use App\AuthChain\Module\ChainInterface;
use App\Scopes\TenantTrait;
use App\Scopes\SortChainScope;

class AuthChain extends Model implements ChainInterface
{
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new SortChainScope());
    }

    /**
     * @return \App\AuthChain\Module\Module
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return \App\AuthChain\Module\Module
     */
    public function getTo()
    {
        return $this->to;
    }
}
