<?php

namespace App\AuthChain\Object\Eloquent;

interface UserInterface
{
    public function getId();
    public function getUsername();
    public function getAttributes();
}
