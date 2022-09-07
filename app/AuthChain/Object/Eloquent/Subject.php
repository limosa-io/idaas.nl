<?php

namespace App\AuthChain\Object\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\AuthChain\Object\Subject as RealSubject;
use App\AuthChain\Object\AuthenticableTrait;
use Illuminate\Contracts\Auth\Authenticatable;
use App\AuthChain\Repository\SubjectRepositoryInterface;

//TODO: Change this to an InterFace. Serialize to UserInfo. Id = userid || merged|subjectid
class Subject extends Model implements Authenticatable
{
    use AuthenticableTrait;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'subject' => 'array',
        'levels' => 'array'
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'authchain_subjects';

    /**
     * The guarded attributes on the model.
     *
     * @var array
     */
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::creating(
            function ($model) {
                if (empty($model->{$model->getKeyName()})) {
                    $model->{$model->getKeyName()} = (string) Str::orderedUuid();
                }
            }
        );
    }

    public function getAuthIdentifier()
    {
        return $this->id;
    }

    public function getAuthIdentifierName()
    {
        return 'id';
    }

    /**
     * @return RealSubject
     */
    public function getSubject()
    {
        return RealSubject::fromJson($this->subject);
    }
}
