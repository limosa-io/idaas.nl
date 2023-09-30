<?php

/**
 * Calls Openwhisk apis. See https://console.bluemix.net/apidocs/functions
 */

namespace App;

use App\CloudFunction\HandlerInterface;

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
