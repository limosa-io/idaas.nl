<?php

namespace App\AuthChain;

class Client implements \JsonSerializable
{
    //Allow multi language
    protected $logoUri;

    //Allow multi language
    protected $clientName;

    /**
     * Use to unprotected information about this client
     */
    protected $clientUri;

    /**
     * @var array Mail address of contact persons
     */
    protected $contacts;

    /**
     * Terms of services
     */
    protected $tosUri;

    /**
     * Policy
     */
    protected $policyUri;

    public function jsonSerialize(): array
    {
        return [];
    }
}
