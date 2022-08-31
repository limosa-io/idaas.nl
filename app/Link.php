<?php

namespace App;

use ArieTimmerman\Laravel\AuthChain\Object\Eloquent\Link as BaseLink;
use App\Scopes\TenantTrait;

class Link extends BaseLink
{
    use TenantTrait;
}
