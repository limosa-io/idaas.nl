<?php

namespace App\SAML;

use ArieTimmerman\Laravel\SAML\Subject as SAMLSubject;

class Subject extends SAMLSubject
{


    protected $subject;

    function __construct(\App\Subject $subject)
    {
        $this->subject = $subject;
    }

    public function getNameIdValue()
    {
        return $this->subject->getAuthIdentifier();
    }

    public function getAttributes(\SAML2\AuthnRequest $authnRequest)
    {
        return [
            'user_id' => $this->subject->getUserId()
        ];
    }


}