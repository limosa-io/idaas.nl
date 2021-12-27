<?php

namespace App\AuthTypes;

use Laravel\Socialite\Two\LinkedInProvider;

class Linkedin extends Generic
{
    
    public function getSocialProvider()
    {
        return LinkedInProvider::class;
    }

    public function getDefaultName()
    {
        return "LinkedIn";
    }

}