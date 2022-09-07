<?php

namespace App\AuthChain\Module;

interface ChainInterface
{
    public function getFrom();
    public function getTo();
}
