<?php

/**
 * Calls Openwhisk apis. See https://console.bluemix.net/apidocs/functions
 */

namespace App;

use App\CloudFunction\HandlerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\StandardMail;
use Ramsey\Uuid\Uuid;

class CloudFunctionHelper
{
    public static function invoke(CloudFunction $cloudFunction, $parameters = [])
    {
        /** @var HandlerInterface */
        $handler = resolve(HandlerInterface::class);
        return $handler->invoke(
            CloudFunction::find($cloudFunction->id),
            $parameters
        );
    }
}
