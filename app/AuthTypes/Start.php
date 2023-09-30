<?php

/**
 * Example request
 *
 * {
 *     "username": "piet",
 *     "password": "pass123",
 *  "remember": true  // (remember on browser close)
 * }
 */

namespace App\AuthTypes;

use App\AuthChain\ModuleInterface;
use App\AuthChain\ModuleResult;
use App\AuthChain\State;
use Illuminate\Http\Request;

class Start extends AbstractType
{
    public function isPassive()
    {
        return true;
    }

    /**
     * @return ModuleResult
     */
    public function process(Request $request, State $state, ModuleInterface $module)
    {
        return $module
            ->baseResult()
            ->complete()
            ->setPrompted(false)
            ->setRememberAlways(false)
            ->setRememberForSession(false);
    }
}
