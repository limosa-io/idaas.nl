<?php

namespace App\CloudFunction;

use App\CloudFunction;

class DigitalOceanHandler implements HandlerInterface
{
    public function deploy(CloudFunction $cloudFunction)
    {
        // TODO: implement. Use `doctl`
    }

    public function invoke(CloudFunction $cloudFunction, $arguments)
    {
        // TODO: implement. Invoke REST endpoint
    }
}
