<?php

namespace App\AuthTypes;

use App\AuthChain\Subject;
use Laravel\Socialite\Contracts\User;

class SocialSubject extends Subject
{
    /**
     * @var User
     */
    protected $socialUser;

    public function __construct($type, User $user)
    {
        $this->socialUser = $user;
        $this->attributes = (array) $user;
        $this->setIdentifier($type.'|'.$this->socialUser->getId());
    }
}
