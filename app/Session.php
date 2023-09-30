<?php

/**
 * Used for querying
 *
 * TODO: Where is this _exactly_ used?
 */

namespace App;

class Session extends Model
{
    protected $table = 'sessions';

    public $incrementing = false;
}
