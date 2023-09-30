<?php

namespace App\AuthChain;

class UIServer implements \JsonSerializable
{
    protected $origins = [];

    protected $redirectionUrls = [];

    public function __construct($origins, $redirectionUrls)
    {
        $this->origins = $origins;
        $this->redirectionUrls = $redirectionUrls;
    }

    /**
     * Get the value of redirectionUrls
     */
    public function getRedirectionUrls()
    {
        return $this->redirectionUrls;
    }

    /**
     * Get the value of origins
     *
     * @return string[]
     */
    public function getOrigins()
    {
        return $this->origins;
    }

    public function jsonSerialize(): array
    {
        return [
            'origins' => $this->origins,
            'redirectionUrls' => $this->redirectionUrls,
        ];
    }

    public static function fromJson($json)
    {
        return $json == null ? null : new self($json->origins, $json->redirectionUrls);
    }
}
