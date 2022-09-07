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

namespace App\AuthChain\Types;

use App\AuthChain\Module\ModuleResult;
use Illuminate\Http\Request;
use App\AuthChain\State;
use App\AuthChain\Module\ModuleInterface;

class Register extends AbstractType
{
    protected $remembered = false;

    /**
     * @return self
     */
    public function init(Request $request, State $state, ModuleInterface $module)
    {
        return $this;
    }

    public function getDefaultName()
    {
        return "Registration";
    }

    public function remembered()
    {
        return false;
    }

    /**
     * @return ModuleResult
     */
    public function process(Request $request, State $state, ModuleInterface $module)
    {

        /*
        Frontend:
            - Send SCIM Create request to /Me endpoint
            - Complete this step and show message that mail has been sent??
        Backend:
            - Listen to SCIM User self-service creation event, send mail with something like ?activate=123&state=13443
            - If received activation event, set authentication level and continue ...
        */

        // TODO: Listen to SCIM User self-service creation event

        return $module->baseResult()->setCompleted(true)->setLevels(null)->setResponse(
            response(
                [
                'test' => 'test'
                ]
            )
        );
    }
}
