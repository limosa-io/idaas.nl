<?php

namespace App;

use App\Scopes\TenantTrait;

class State extends \App\AuthChain\Object\Eloquent\State
{
    use TenantTrait;
}
