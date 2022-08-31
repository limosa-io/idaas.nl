<?php

namespace App\Repository;

use ArieTimmerman\Laravel\AuthChain\Repository\LinkRepository as BaseLinkRepository;
use App\Link;

class LinkRepository extends BaseLinkRepository
{
    public function getLinkClass()
    {
        return Link::class;
    }
}
