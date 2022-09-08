<?php

namespace App\AuthChain\Providers;

use App\AuthChain\Exceptions\AuthFailedException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Session;
use App\AuthChain\State;
use App\AuthChain\Repository\SubjectRepositoryInterface;

/*
TODO: Rename in SubjectProvider??
*/
class UserProvider implements \Illuminate\Contracts\Auth\UserProvider
{
    public function retrieveById($identifier)
    {
        return resolve(SubjectRepositoryInterface::class)->get($identifier);
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
