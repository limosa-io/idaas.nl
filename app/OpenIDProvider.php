<?php

namespace App;

use App\Scopes\TenantTrait;
use Idaas\Passport\Model\ProviderInterface;
use Illuminate\Database\Eloquent\Model;

class OpenIDProvider extends Model implements ProviderInterface
{
    use TenantTrait;

    protected $casts = [
        'response_types_supported' => 'array'
    ];

    protected $hidden = [
        'liftime_access_token',
        'liftime_refresh_token',
        'liftime_id_token',
        'created_at',
        'updated_at',
        'tenant_id',
        'profile_url_template'
    ];

    protected $guarded = [ 'id', 'tenant_id' ];

    protected $with = [ 'scopesSupported', 'acrValuesSupported' ];

    public function scopesSupported()
    {
        return $this->hasMany('App\OAuthScope', 'provider_id');
    }

    public function acrValuesSupported()
    {
        return $this->hasMany('App\AuthLevel', 'provider_id');
    }

    public function relationsToArray()
    {
        return [
            'scopes_supported' => array_merge(
                ['openid','online_access'],
                $this->scopesSupported->pluck('name')->toArray()
            ),
            'acr_values_supported' => $this->acrValuesSupported()
                ->where(['type' => 'oidc'])
                ->get()
                ->pluck('level')
                ->toArray(),
        ];
    }

    public function toArray()
    {
        $result = parent::toArray();

        unset($result['id']);
        unset($result['init_url']);
        unset($result['tenant_id']);

        //TODO: Set subdomain based on current tenant!
        $result['authorization_endpoint'] = route('oauth.authorize', []);
        $result['token_endpoint'] = route('oauth.token', []);

        $result['userinfo_endpoint'] = route('oidc.userinfo', []);

        $result['jwks_uri'] = route('oidc.jwks', []);

        $result['issuer'] = url('/');

        $result['claims_supported'] = [
            'sub',
            'iss',
            'scim_id',
            'roles',
            'acr',
            'picture',
            'profile'
        ];

        $result['end_session_endpoint'] = route('oidc.logout', []);

        $result['code_challenge_methods_supported'] = ['S256'];
        $result['introspection_endpoint'] = route('oauth.introspect');
        $result['introspection_endpoint_auth_methods_supported'] = ['client_secret_jwt'];
        $result['token_endpoint_auth_methods_supported'] = ['none','client_secret_post','client_secret_basic'];

        $result['revocation_endpoint'] = route('oauth.revoke');

        $result['service_documentation'] = url('/');

        $result['ui_locales_supported'] = TenantSetting::where('key', 'ui:languages')->value('value') ?? [];

        return $result;
    }

    public function getProfileURL($userId)
    {
        return $this->profile_url_template ?
            str_replace('{userid}', urlencode($userId), $this->profile_url_template) :
            route('ice.manage.profile', ['user_id' => $userId]);
    }
}
