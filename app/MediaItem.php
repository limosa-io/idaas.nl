<?php

namespace App;

use App\Model;

class MediaItem extends Model
{
    protected $hidden = ['exernal_id'];

    protected $casts = [
        'meta' => 'array'
    ];
}
