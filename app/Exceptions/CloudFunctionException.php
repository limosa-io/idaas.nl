<?php

namespace App\Exceptions;

use Exception;

class CloudFunctionException extends Exception
{
    /**
     * Report the exception.
     *
     * @return void
     */
    public function render()
    {
        return response(
            $this->getMessage(),
            502,
            [
                'Content-Type' => 'application/json',
            ]
        );
    }
}
