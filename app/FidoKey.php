<?php

namespace App;

use App\Scopes\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FidoKey extends Model
{
    use HasFactory;
    use TenantTrait;

    /**
     * The guarded attributes on the model.
     *
     * @var array
     */
    protected $guarded = [];

    protected $casts = [
        'created_at' => 'timestamp',
    ];
}
