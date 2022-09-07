<?php

/**
 * Model of an authentication level, like used in SAML or OIDC
 */

namespace App\AuthChain;

use App\AuthChain\AuthLevelInterface;

class AuthLevel implements AuthLevelInterface
{
    public const TYPE_OIDC = 'oidc';
    public const TYPE_SAML = 'saml';

    /**
     * For example, 'saml' or 'oidc'
     */
    protected $type;

    /**
     * For example 'urn:oasis:names:tc:SAML:2.0:ac:classes:unspecified'
     */
    public $level;

    public function __construct($type, $level)
    {
        $this->type = $type;
        $this->level = $level;
    }

    public function equals(?AuthLevelInterface $authLevel)
    {
        if ($authLevel == null) {
            return false;
        }

        return $this->getType() == $authLevel->getType() && $this->getLevel() == $authLevel->getLevel();
    }

    public function compare(?AuthLevelInterface $authLevel)
    {
        $result = -1;

        if ($this->equals($authLevel)) {
            $result = 0;
        }

        //TODO: implement the rest

        return $result;
    }

    /**
     * Get the value of type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the value of type
     *
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the value of level
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set the value of level
     *
     * @return self
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     *
     */
    public static function fromJsonObject($json)
    {
        $result = [];

        if (is_array($json)) {
            foreach ($json as $k) {
                $result[] = new self($k->type, $k->level);
            }
        } else {
            $result[] = new self($json->type, $json->level);
        }

        return $result;
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => $this->type,
            'level' => $this->level
        ];
    }

    public static function oidc($level)
    {
        if ($level == null || (is_array($level) && count($level) == 0)) {
            return null;
        }
        return new self(self::TYPE_OIDC, $level);
    }

    public static function oidcAll(?array $level)
    {
        if ($level == null || count($level) == 0) {
            return null;
        }

        $result = [];

        foreach ($level as $l) {
            if (($r = self::oidc($l)) != null) {
                $result[] = $r;
            }
        }

        return $result;
    }

    public static function samlAll(?array $level)
    {
        if ($level == null || count($level) == 0) {
            return null;
        }

        $result = [];

        foreach ($level as $l) {
            if (($r = self::saml($l)) != null) {
                $result[] = $r;
            }
        }

        return $result;
    }

    public static function saml($level)
    {
        return new self(self::TYPE_SAML, $level);
    }

    public function getIdentifier()
    {
        return $this->type . ':' . $this->value;
    }
}
