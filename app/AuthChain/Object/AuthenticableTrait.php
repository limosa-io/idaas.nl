<?php

namespace App\AuthChain\Object;

use App\Exceptions\AuthFailedException;

trait AuthenticableTrait
{
    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        throw new AuthFailedException('getAuthPassword is not supported');
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string
     */
    public function getRememberToken()
    {
        return 'remember_token';
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string $value
     * @return void
     */
    public function setRememberToken($value)
    {
        throw new AuthFailedException('setRememberToken is not supported');
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        throw new AuthFailedException('getRememberTokenName is not supported');
    }
}
