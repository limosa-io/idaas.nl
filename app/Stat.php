<?php

namespace App;

class Stat extends Model
{
    public function statable()
    {
        return $this->morphTo();
    }

    public function __construct()
    {
        parent::__construct();
        $this->hours = intval(time() / 3600);
    }
}
