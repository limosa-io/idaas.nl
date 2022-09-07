<?php

namespace App\AuthChain;

interface AuthLevelInterface extends \JsonSerializable
{
    public function getIdentifier();

    /**
     * Returns a string reporesentation of the level
     */
    public function getLevel();

    public function setLevel($level);

    /**
     * Returns a string representation of the type
     */
    public function getType();


    public function setType($type);

    /**
     * Convers a json object to the AuthLevel
     */
    public static function fromJsonObject($json);


    /**
     * Returns 0 if objects are equal. -1 if provider object is lower. 1 if provider object represents a higher level
     */
    public function compare(?AuthLevelInterface $authLevel);
}
