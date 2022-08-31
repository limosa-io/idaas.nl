<?php

namespace App\AuthTypes;

use Laravel\Socialite\Two\GithubProvider;

class Github extends Generic
{
    public function getSocialProvider()
    {
        return GithubProvider::class;
    }

    public function getDefaultName()
    {
        return "GitHub";
    }
}
