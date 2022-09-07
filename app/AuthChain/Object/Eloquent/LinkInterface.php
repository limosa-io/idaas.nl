<?php

namespace App\AuthChain\Object\Eloquent;

interface LinkInterface
{
    /**
     * @return UserInterface
     */
    public function getUser();
}
