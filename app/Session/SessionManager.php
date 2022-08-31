<?php

/**
 * Custom SessionManager to allow returning a custom Session Store.
 */

namespace App\Session;

use App\Session\Store;

class SessionManager extends \Illuminate\Session\SessionManager
{
    protected function buildSession($handler)
    {
        return new Store($this->config->get('session.cookie'), $handler);
    }
}
