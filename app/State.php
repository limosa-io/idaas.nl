<?php

namespace App;

use App\Scopes\TenantTrait;

class State extends \ArieTimmerman\Laravel\AuthChain\Object\Eloquent\State
{
    use TenantTrait;
}
