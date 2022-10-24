<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\TenantTrait;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

class Git extends Model
{
    use HasFactory;
    use TenantTrait;

    protected $casts = [
        'settings' => AsArrayObject::class
    ];

    protected $attributes = [
        'type' => 'none',
        'settings' => '{"token":""}',
    ];
}
