<?php

namespace App;

use App\Scopes\TenantTrait;
use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    use TenantTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'authchain_subject_links';

    /**
     * The guarded attributes on the model.
     *
     * @var array
     */
    protected $guarded = [];

    public function getUser()
    {
        return $this->user;
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
