<?php

namespace App\AuthChain\Repository;

class ConsentRepository
{
    public function getDescriptions($scopes)
    {
        return [
            'scope' => 'description'
        ];
    }
}
