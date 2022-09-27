<?php

namespace App;

use App\Model;
use App\Scopes\SortChainScope;

class AuthChain extends Model
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
