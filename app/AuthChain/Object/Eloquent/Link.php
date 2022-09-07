<?php

namespace App\AuthChain\Object\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Contracts\Auth\Authenticatable;

class Link extends Model implements LinkInterface
{
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
