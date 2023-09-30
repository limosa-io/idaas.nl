<?php

namespace App;

use App\Scopes\TenantTrait;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Support\Str;

class Model extends BaseModel
{
    use TenantTrait {
        doCreating as traitDoCreating;
    }

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * The guarded attributes on the model.
     *
     * @var array
     */
    protected $guarded = ['id', 'tenant_id'];

    protected $hidden = ['tenant_id'];

    /**
     * The trait TenantTrait ensures this method gets called
     */
    protected static function doCreating($model)
    {
        self::traitDoCreating($model);

        $model->{$model->getKeyName()} = (string) Str::orderedUuid();
    }
}
