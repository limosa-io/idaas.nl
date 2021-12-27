<?php

namespace App\Stats;

use Illuminate\Support\Facades\Facade;

class Statter extends Facade
{

    protected static function getFacadeAccessor()
    {
        return StatterFacade::class;
    }

}