<?php

namespace App;

use App\Scopes\TenantTrait;
use Laravel\Passport\Token as LaravelToken;

class Token extends LaravelToken
{
    use TenantTrait;

    protected $casts = [
        'scopes' => 'array',
        'revoked' => 'bool',
        'claims' => 'array'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'client_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'user_id', 'id');
    }
}
