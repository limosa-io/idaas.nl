<?php

/**
 * An authentication chain is a connected set of authentication modules, structued in a directed graph.
 */

namespace App\Events;

use App\AuthChain\State;
use Illuminate\Queue\SerializesModels;

class Authenticated
{
    use SerializesModels;

    /**
     * @var State
     */
    public $state;

    public function __construct(State $state)
    {
        $this->state = $state;
    }
}
