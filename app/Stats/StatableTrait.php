<?php

namespace App\Stats;

trait StatableTrait
{
    public function stats()
    {
        return $this->morphMany('App\Stat', 'statable');
    }
}
