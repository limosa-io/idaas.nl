<?php

namespace App;

class ModuleResult extends Model
{
    protected $casts = [
        'session' => 'boolean',
    ];

    protected $dates = [
        'expires_at',
    ];

    protected $hidden = ['module_result'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function subject()
    {
        return $this->belongsTo('App\Subject');
    }

    public function module()
    {
        return $this->belongsTo('App\AuthModule');
    }
}
