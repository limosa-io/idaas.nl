<?php

namespace App\AuthChain\Types;

use Illuminate\Http\Request;
use App\AuthChain\State;
use App\AuthChain\Module\ModuleInterface;

class NullType extends AbstractType
{
    public function process(Request $request, State $state, ModuleInterface $module)
    {
        return null;
    }
}
