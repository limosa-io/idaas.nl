<?php

namespace App;

class MediaItem extends Model
{
    protected $hidden = ['exernal_id'];

    protected $casts = [
        'meta' => 'array',
    ];
}
