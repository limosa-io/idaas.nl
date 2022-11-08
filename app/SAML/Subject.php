<?php

namespace App\SAML;

use App\CloudFunction;
use App\CloudFunctionHelper;
use ArieTimmerman\Laravel\SAML\Subject as SAMLSubject;

class Subject extends SAMLSubject
{
    protected $subject;

    public function __construct(\App\Subject $subject)
    {
        $this->subject = $subject;
    }

    public function getNameIdValue()
    {
        return $this->subject->getAuthIdentifier();
    }

    public function getAttributes(\SAML2\AuthnRequest $authnRequest)
    {
        $result = [
            'user_id' => $this->subject->getUserId()
        ];

        $cloudFunction = CloudFunction::where('type', CloudFunction::TYPE_ATTRIBUTE)->first();

        if (
            config('serverless.openwhisk_enabled') && $cloudFunction != null
        ) {
            $cloudResult = CloudFunctionHelper::invoke(
                $cloudFunction,
                [
                    'subject' => $this->subject,
                    'context' => [

                    ]
                ]
            );

            $result = array_merge($result, $cloudResult);
        }

        return $result;
    }
}
