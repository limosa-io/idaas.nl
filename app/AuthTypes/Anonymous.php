<?php

namespace App\AuthTypes;

use App\AuthChain\ModuleInterface;
use App\AuthChain\ModuleResult;
use App\AuthChain\State;
use App\AuthChain\Subject;
use Illuminate\Http\Request;

class Anonymous extends AbstractType
{
    public function isPassive()
    {
        return true;
    }

    public function init(Request $request, State $state, ModuleInterface $module)
    {
    }

    public function getDefaultName()
    {
        return 'Anonymous';
    }

    public function isEnabled(?Subject $subject)
    {
        return true;
    }

    public function process(Request $request, State $state, ModuleInterface $module)
    {
        if ($state->getSubject() == null) {

            return $module->baseResult()->setSubject(
                $this->createSubject('anonymous', $this, $module)
            )->complete()->setPrompted(true);

        } else {
            return (new ModuleResult())
                ->setCompleted(false)
                ->setResponse(
                    response(['error' => 'Could not use anonymous as the second factor.'], 422)
                );
        }
    }
}
