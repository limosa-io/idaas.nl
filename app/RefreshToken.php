<?php

namespace App;

use App\Scopes\TenantTrait;
use Laravel\Passport\RefreshToken as LaravelRefreshToken;

class RefreshToken extends LaravelRefreshToken
{
    use TenantTrait;
}
