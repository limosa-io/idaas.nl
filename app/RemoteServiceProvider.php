<?php

namespace App;

use App\Model;
use ArieTimmerman\Laravel\SAML\SAML2\Entity\RemoteServiceProviderConfigInterface;
use App\Scopes\TenantTrait;

class RemoteServiceProvider extends Model implements RemoteServiceProviderConfigInterface, ApplicationInterface
{
    protected $casts = [
        'assertionConsumerService' => 'array',
        'singleLogoutService' => 'array',
        'keys' => 'array',
        'sync' => 'boolean',
        'wantSignedAuthnResponse' => 'boolean',
        'wantSignedAssertions' => 'boolean',
        'wantSignedLogoutResponse' => 'boolean',
        'wantSignedLogoutRequest' => 'boolean'
    ];

    protected $guarded = [ 'id' ];

    public function jsonSerialize(): array
    {
        $result = parent::jsonSerialize();

        $result['AssertionConsumerService'] = $result['assertionConsumerService'];
        $result['SingleLogoutService'] = $result['singleLogoutService'];
        $result['entityid'] = $result['entityid'];

        unset($result['assertionConsumerService']);
        unset($result['singleLogoutService']);
        // unset($result['entityid']);

        return $result;
    }

    public function toSimpleSAMLArray()
    {
        return [
            'entityid' => $this->entityid,
            'metadata-set' => 'saml20-sp-remote',
            'AssertionConsumerService' => $this->assertionConsumerService,
            'SingleLogoutService' => $this->singleLogoutService,
            'keys' => $this->keys,

            'wantSignedAuthnResponse' => $this->wantSignedAuthnResponse,
            'wantSignedAssertions' => $this->wantSignedAssertions,
            'wantSignedLogoutResponse' => $this->wantSignedLogoutResponse,
            'wantSignedLogoutRequest' => $this->wantSignedLogoutRequest

        ];
    }

    public function fromSimpleSAMLArray(array $array)
    {
        //not implemented
        return null;
    }

    public function groups()
    {
        return $this->belongsToMany(
            Group::class,
            'serviceprovider_group',
            'serviceprovider_id',
            'group_id'
        )->wherePivot('tenant_id', resolve(Tenant::class)->id)->using(TenantPivot::class);
    }
}
