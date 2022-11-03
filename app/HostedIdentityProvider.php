<?php

namespace App;

use App\Model;
use ArieTimmerman\Laravel\SAML\SAML2\Entity\HostedIdentityProviderConfigInterface;

class HostedIdentityProvider extends Model implements HostedIdentityProviderConfigInterface
{
    protected $casts = [
        'signAuthnrequest' => 'boolean',
        'metadataSignEnable' => 'boolean',
        'redirectSign' => 'boolean',
        'ssoHttpPostEnabled' => 'boolean',
        'ssoHttpRedirectEnabled' => 'boolean',
        'sloHttpPostEnabled' => 'boolean',
        'sloHttpRedirectEnabled' => 'boolean',

        'keys' => 'array',
        'contacts' => 'array',
        'organization' => 'array',
        'supportedNameIDFormat' => 'array',

        'expire' => 'integer',
        'cacheDuration' => 'integer'

    ];

    public function jsonSerialize(): array
    {
        return $this->toSimpleSAMLArray();
    }

    public function toSimpleSAMLArray()
    {
        return [
            'entityId' => 'urn:' . $this->id,

            // what authentication context level for single-sign on cases
            'PreviousSession' => $this->previousSession ?? 'urn:oasis:names:tc:SAML:2.0:ac:classes:PreviousSession',

            'sign.authnrequest' => $this->signAuthnrequest,

            'redirect.sign' => $this->redirectSign,
            'ssoHttpPostEnabled' => $this->ssoHttpPostEnabled,
            'ssoHttpRedirectEnabled' => $this->ssoHttpRedirectEnabled,
            'sloHttpPostEnabled' => $this->sloHttpPostEnabled,
            'sloHttpRedirectEnabled' => $this->sloHttpRedirectEnabled,
            'keys' => $this->keys,
            'supportedNameIDFormat' => $this->supportedNameIDFormat ?? [],

            /*
            [[
        'emailAddress' => 'someonase@example.com',
        'name' => 'John Doe',
        'contactType' => 'technical'
            ]]
            */
            'contacts' => $this->contacts ?? [],
            'organization' => $this->organization ?? [],

            //metadata:
            'expire' => time() + 3600,
            'cacheDuration' => 3600,
            'metadata.sign.enable' => $this->metadataSignEnable,

        ];
    }

    public function fromSimpleSAMLArray(array $array)
    {
        //not implemented
        return null;
    }
}
