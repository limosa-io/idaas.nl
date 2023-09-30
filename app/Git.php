<?php

namespace App;

use App\Scopes\TenantTrait;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Git extends Model
{
    use HasFactory;
    use TenantTrait;

    protected $casts = [
        'settings' => AsArrayObject::class,
        'pull_start_time' => 'datetime',
        'push_start_time' => 'datetime',
    ];

    protected $attributes = [
        'type' => 'none',
        'settings' => '{"token":""}',
    ];
}
