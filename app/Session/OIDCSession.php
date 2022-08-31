<?php

/**
 * Custom SessionManager to allow returning a custom Session Store.
 */

namespace App\Session;

use ArieTimmerman\Laravel\AuthChain\State;
use Idaas\OpenID\Session;

class OIDCSession extends Session
{
    public function getAuthTime()
    {
        $state = resolve(State::class);
        $results = $state->getModuleResults();
        $authTime = new \DateTime();

        // Get the last completed authentication module that was not consent
        foreach (array_reverse($results->toArray()) as $r) {
            if ($r->getModule()->getTypeObject()->getIdentifier() != 'consent') {
                $authTime = $r->getAuthenticationTime();
                break;
            }
        }

        return $authTime;
    }
}
