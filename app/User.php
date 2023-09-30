<?php

namespace App;

use App\Scopes\TenantTrait;
use App\Stats\StatableInterface;
use App\Stats\StatableTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements StatableInterface
{
    use HasApiTokens;
    use Notifiable;
    use StatableTrait;
    use TenantTrait;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'last_successful_login_date',
        'birthDate',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'picture', 'otp_secret',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $with = [

    ];

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtolower($value);
    }

    public $incrementing = false;

    protected $keyType = 'string';

    public function links()
    {
        return $this->hasMany('\App\Link');
    }

    public function roles()
    {
        //Allow a user to get roles from other tenants ...
        //wherePivot('tenant_id', resolve('App\Tenant')->id)->
        return $this->belongsToMany('App\Role')->using('App\TenantPivot');
    }

    public function groups()
    {
        return $this->belongsToMany('App\Group')
            ->wherePivot('tenant_id', resolve('App\Tenant')->id)->using('App\TenantPivot');
    }

    public function fidoKeys(): HasMany
    {
        return $this->hasMany(FidoKey::class);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->name;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    // public function jsonSerialize()
    // {
    //     return Helper::objectToSCIMArray($this, ResourceType::user());
    // }

    protected static function boot()
    {
        parent::boot();

        static::deleting(
            function ($user) {
                // before delete() method call this

                foreach ($user->links as $link) {
                    $link->delete();
                }
            }
        );

        static::creating(
            function ($model) {
                $model->{$model->getKeyName()} = (string) Str::orderedUuid();
            }
        );
    }
}
