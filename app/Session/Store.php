<?php

namespace App\Session;

use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class Store extends \Illuminate\Session\Store
{
    /**
     * Improves performance
     */
    protected function generateSessionId()
    {
        return Str::uuid();
    }

    public function isValidId($id)
    {
        return $id != null && Uuid::isValid($id);
    }
}
