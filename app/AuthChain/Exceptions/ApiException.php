<?php

namespace App\AuthChain\Exceptions;

class ApiException extends \Exception implements Exception
{
    public function render($request)
    {
        return response(
            [
            'error_description' => $this->getMessage()
            ],
            500
        );
    }
}
