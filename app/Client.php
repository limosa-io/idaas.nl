<?php

namespace App;

use App\Scopes\TenantTrait;
use App\Stats\StatableInterface;
use App\Stats\StatableTrait;
use Idaas\Passport\Model\Client as IdaasClient;
use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Passport\HasApiTokens;

class Client extends IdaasClient implements ApplicationInterface, Authenticatable, StatableInterface, SubjectInterface
{
    use HasApiTokens;
    use StatableTrait;
    use TenantTrait;

    protected $hidden = [
        'tenant_id',
        'created_at',
        'updated_at',
        'user_id',
        'personal_access_client',
        'password_client',
        'revoked',
    ];

    protected $attributes = [
        'personal_access_client' => false,
        'password_client' => false,
        'revoked' => false,
    ];

    protected $with = [];

    protected $primaryKey = 'client_id';

    public function defaultAcrValues()
    {
        return $this->belongsToMany(
            AuthLevel::class,
            'client_authlevel',
            'client_id',
            'auth_level_id'
        )->wherePivot('tenant_id', resolve(Tenant::class)->id)->using(TenantPivot::class);
    }

    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'client_role',
            'client_id',
            'role_id'
        )->wherePivot('tenant_id', resolve(Tenant::class)->id)->using(TenantPivot::class);
    }

    public function groups()
    {
        return $this->belongsToMany(
            Group::class,
            'client_group',
            'client_id',
            'group_id'
        )->wherePivot('tenant_id', resolve(Tenant::class)->id)->using(TenantPivot::class);
    }

    public function getRoles()
    {
        return $this->roles->pluck('id');
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'client_id';
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->client_id;
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        throw new \Exception('Not supported');
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string
     */
    public function getRememberToken()
    {
        throw new \Exception('Not supported');
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string  $value
     * @return void
     */
    public function setRememberToken($value)
    {
        throw new \Exception('Not supported');
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        throw new \Exception('Not supported');
    }

    public function confidential()
    {
        return $this->public == 'confidential';
    }
}
