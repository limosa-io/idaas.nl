<?php

namespace App\AuthTypes;

use Laravel\Socialite\One\TwitterProvider;

class Twitter extends Generic
{
    public function getSocialProvider()
    {
        return TwitterProvider::class;
    }

    public function getDefaultName()
    {
        return 'Twitter';
    }
}
