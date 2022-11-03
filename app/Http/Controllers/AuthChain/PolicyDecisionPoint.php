<?php

/**
 * Allows blocking access if a user does not belong to a certain group, defined per OAuth client
 */

namespace App\Http\Controllers\AuthChain;

use App\AuthChain\State;
use App\AuthChain\Subject;
use App\Client;
use App\RemoteServiceProvider;

class PolicyDecisionPoint
{
    public function isAllowed(?Subject $subject, State $state)
    {
        //TODO: Check why this is, because of client_Credentials grant perhaps?
        if ($subject == null) {
            return true;
        }

        $application = null;

        // TODO: dit alleen als $state->appId een guid is
        if (\Ramsey\Uuid\Uuid::isValid($state->appId)) {
            $application = Client::with('groups')->find($state->appId);
        } else {
            $application = RemoteServiceProvider::where("entityid", $state->appId)->first();
        }

        //Check because the app could also be a SAML service provider
        if ($application != null && $application->groups->isNotEmpty()) {
            $user = $subject->getUser();

            if ($user == null) {
                return "You're using a federated user. Only registered users are allowed.";
            }

            $userHasAllGroups = $application->groups->pluck('id')
                ->intersect($user->groups->pluck('id'))->count() == $application->groups->count();

            return $userHasAllGroups ? true : "The user doesn't have the required groups";
        }

        return true;
    }
}
