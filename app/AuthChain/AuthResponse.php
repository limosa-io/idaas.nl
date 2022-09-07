<?php

namespace App\AuthChain;

use App\AuthChain\Module\ModuleList;
use App\AuthChain\Module\ModuleResult;

class AuthResponse implements \JsonSerializable
{
    /**
     * @var State
     */
    protected $state;

    /**
     * @var ModuleList
     */
    protected $next;

    /**
     * @var ModuleResult
     */
    protected $incomplete;

    /**
     * @var Client
     */
    protected $client;

    /**
     * Get the value of state
     *
     * @return State
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set the value of state
     *
     * @param State $state
     *
     * @return self
     */
    public function setState(State $state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get the value of next
     *
     * @return ModuleList
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * Set the value of next
     *
     * @param Module[] $next
     *
     * @return self
     */
    public function setNext(ModuleList $next)
    {
        $this->next = $next;

        return $this;
    }

    /**
     * Get the value of incomplete
     *
     * @return ModuleResult
     */
    public function getIncomplete()
    {
        return $this->incomplete;
    }

    /**
     * Set the value of incomplete
     *
     * @param ModuleResult $incomplete
     *
     * @return self
     */
    public function setIncomplete(ModuleResult $incomplete)
    {
        $this->incomplete = $incomplete;

        return $this;
    }

    public function toArray()
    {
        return [
            'stateId' => (string) $this->getState(),
            'next'  => $this->getNext(),
            //'last'  => $this->getState()->getLastCompleted(),
            'info'  => $this->getState()->toArrayPublic(),
            'client'   => $this->client
        ];
    }
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Used to set x-authrequest value.
     */
    public function __toString()
    {
        return base64_encode(json_encode($this->toArray()));
    }


    /**
     * Set the value of client
     *
     * @param Client $client
     *
     * @return self
     */
    public function setClient(Client $client)
    {
        $this->client = $client;

        return $this;
    }
}
