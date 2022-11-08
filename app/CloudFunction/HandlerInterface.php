<?php

namespace App\CloudFunction;

use App\CloudFunction;

interface HandlerInterface
{
    public function deploy(CloudFunction $cloudFunction);
    public function invoke(CloudFunction $cloudFunction, $arguments);
}
