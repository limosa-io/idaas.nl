<?php

namespace App\AuthTypes;

use Laravel\Socialite\Two\FacebookProvider;

class Facebook extends Generic
{
    public function getSocialProvider()
    {
        return FacebookProvider::class;
    }

    public function getDefaultName()
    {
        return "Facebook";
    }
}
