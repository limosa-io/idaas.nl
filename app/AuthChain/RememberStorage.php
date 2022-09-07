<?php

namespace App\AuthChain;

use App\AuthChain\Module\ModuleResultList;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Authenticatable;

class RememberStorage
{
    public function clearModuleResults(Request $request)
    {
        session()->flush();
    }

    public function saveModuleResults(Authenticatable $subject, State $state)
    {

        // Always save what was remembered before
        $remember = $state->moduleResultsRemembered;

        foreach ($state->getModuleResults()->toArray() as $moduleResult) {
            if ($moduleResult->getPrompted()) {
                if ($moduleResult->rememberAlways) {
                    $remember->overwrite($moduleResult);
                } elseif ($moduleResult->rememberForSession) {
                    $remember->overwrite($moduleResult);
                }
            }
        }

        session(['remembered' => json_encode($remember)]);
    }

    public function getRememberedModuleResults(Request $request)
    {

        // First load the modules from the session
        return ModuleResultList::fromJson($request->session()->get('remembered'));
    }
}
