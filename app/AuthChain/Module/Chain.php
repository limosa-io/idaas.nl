<?php

/**
 * A module is a configured type.
 */

namespace App\AuthChain\Module;

class Chain implements \JsonSerializable
{
    public $from;
    public $to;

    public function __construct($from, $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    public function jsonSerialize(): array
    {
        return [
            'to' => $this->to,
            'from' => $this->from
        ];
    }

    public function getFrom()
    {
        return $this->from;
    }
    public function getTo()
    {
        return $this->to;
    }
}
