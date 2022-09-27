<?php

/**
 * An authentication chain is a connected set of authentication modules, structued in a directed graph.
 */

namespace App\Events;

use App\AuthChain\State;
use Illuminate\Queue\SerializesModels;

class LoggedOut
{
    use SerializesModels;

    /**
     * @var State
     */
    public $state;

    public function __construct()
    {
    }
}
