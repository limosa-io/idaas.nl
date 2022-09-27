<?php

namespace App;

use App\Exceptions\AuthFailedException;
use App\Repository\SubjectRepository;
use Illuminate\Contracts\Auth\Authenticatable;

class SubjectProvider implements \Illuminate\Contracts\Auth\UserProvider
{
    public function retrieveById($identifier)
    {
        //TODO: This is somewhat of a hack! Ensures client_credentials grant works
        if (strpos($identifier, 'client_') !== false) {
            return Client::with('roles')->find(substr($identifier, 7));
        } else {
            return resolve(SubjectRepository::class)->get($identifier);
        }
    }

    public function retrieveByToken($identifier, $token)
    {
        throw new AuthFailedException('Not implemented');
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        throw new AuthFailedException('updateRememberToken is not supported');
    }

    public function retrieveByCredentials(array $credentials)
    {
        throw new AuthFailedException('retrieveByCredentials is not supported');
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        throw new AuthFailedException('validateCredentials is not supported');
    }
}
