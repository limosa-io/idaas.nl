<?php

namespace App;

use App\Model;
use App\Scopes\TenantTrait;

class Stat extends Model
{
 
    protected $guarded = ['id'];

    public $timestamps = false;

    protected $dates = [
        'created_at',
    ];

    public function statable()
    {
        return $this->morphTo();
    }
    
}
