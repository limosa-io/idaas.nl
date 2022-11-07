<?php

namespace App\SAML;

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
        // TODO: implement this in order to return more attributes. By invoking the cloud function...
        return [
            'user_id' => $this->subject->getUserId()
        ];
    }
}
