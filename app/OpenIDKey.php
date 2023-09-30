<?php

namespace App;

class OpenIDKey extends Model
{
    protected $hidden = [
        'private_key',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function provider()
    {
        return $this->belongsTo('App\OpenIDProvider', 'provider_id');
    }
}
