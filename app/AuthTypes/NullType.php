<?php

namespace App\AuthTypes;

use App\AuthChain\ModuleInterface;
use App\AuthChain\State;
use Illuminate\Http\Request;

class NullType extends AbstractType
{
    public function process(Request $request, State $state, ModuleInterface $module)
    {
        return null;
    }
}
