<?php

namespace App;

use App\AuthChain\Object\Eloquent\Link as BaseLink;
use App\Scopes\TenantTrait;

class Link extends BaseLink
{
    use TenantTrait;
}
