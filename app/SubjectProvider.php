<?php

namespace App;

use App\AuthChain\Providers\UserProvider;

class SubjectProvider extends UserProvider
{
    public function retrieveById($identifier)
    {
        //TODO: This is somewhat of a hack! Ensures client_credentials grant works
        if (strpos($identifier, 'client_') !== false) {
            return Client::with('roles')->find(substr($identifier, 7));
        } else {
            return parent::retrieveById($identifier);
        }
    }
}
