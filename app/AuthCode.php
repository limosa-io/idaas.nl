<?php

namespace App;

use App\Scopes\TenantTrait;
use Laravel\Passport\AuthCode as LaravelAuthCode;

class AuthCode extends LaravelAuthCode
{
    use TenantTrait;
}
