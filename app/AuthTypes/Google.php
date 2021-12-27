<?php

namespace App\AuthTypes;

use Laravel\Socialite\Two\GoogleProvider;

class Google extends Generic
{

    public function getSocialProvider()
    {
        return GoogleProvider::class;
    }

    public function getDefaultName()
    {
        return "Google";
    }

}