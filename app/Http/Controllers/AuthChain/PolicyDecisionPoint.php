<?php

/**
 * Allows blocking access if a user does not belong to a certain group, defined per OAuth client
 */

namespace App\Http\Controllers\AuthChain;

use ArieTimmerman\Laravel\AuthChain\PolicyDecisionPoint as BasePolicyDecisionPoint;
use ArieTimmerman\Laravel\AuthChain\State;
use ArieTimmerman\Laravel\AuthChain\Object\Subject;
use App\Client;

class PolicyDecisionPoint extends BasePolicyDecisionPoint
{
    public function isAllowed(?Subject $subject, State $state)
    {
        //TODO: Check why this is, because of client_Credentials grant perhaps?
        if ($subject == null) {
            return true;
        }

        $client = Client::with('groups')->find($state->appId);

        //Check because the app could also be a SAML service provider
        if ($client != null && $client->groups->isNotEmpty()) {
            $user = $subject->getUser();

            if ($user == null) {
                return "You're using a federated user. Only registered users are allowed.";
            }

            $userHasAllGroups = $client->groups->pluck('id')
                ->intersect($user->groups->pluck('id'))->count() == $client->groups->count();

            return $userHasAllGroups ? true : "The user doesn't have the required groups";
        }

        return true;
    }
}
