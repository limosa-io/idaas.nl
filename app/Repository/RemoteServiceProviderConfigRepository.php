<?php

namespace App\Repository;

use ArieTimmerman\Laravel\SAML\Repository\RemoteServiceProviderConfigRepositoryInterface;
use App\RemoteServiceProvider;

class RemoteServiceProviderConfigRepository implements RemoteServiceProviderConfigRepositoryInterface
{

    /**
     * @return RemoteServiceProviderConfigInterface[]
     */
    public function all()
    {
        return RemoteServiceProvider::all();
    }
    
    /**
     * @return RemoteServiceProviderConfigInterface
     */
    public function get($id)
    {

        $result = RemoteServiceProvider::where('entityid', $id)->first();

        return $result;
    }

    public function getById($id)
    {
        return RemoteServiceProvider::find($id);
    }

    public function patch(string $id, array $remoteServiceProviderConfigArray)
    {

        $remoteServiceProvider = RemoteServiceProvider::findOrFail($id);

        $remoteServiceProvider->entityid = $remoteServiceProviderConfigArray['entityid'];
        $remoteServiceProvider->assertionConsumerService = $remoteServiceProviderConfigArray['AssertionConsumerService'];
        $remoteServiceProvider->singleLogoutService = isset($remoteServiceProviderConfigArray['SingleLogoutService']) ? $remoteServiceProviderConfigArray['SingleLogoutService'] : null;
        $remoteServiceProvider->keys = isset($remoteServiceProviderConfigArray['keys']) ? $remoteServiceProviderConfigArray['keys'] : null;

        $remoteServiceProvider->wantSignedAuthnResponse = $remoteServiceProviderConfigArray['wantSignedAuthnResponse'] ?? false;
        $remoteServiceProvider->wantSignedAssertions = $remoteServiceProviderConfigArray['wantSignedAssertions'] ?? false;
        $remoteServiceProvider->wantSignedLogoutResponse = $remoteServiceProviderConfigArray['wantSignedLogoutResponse'] ?? false;
        $remoteServiceProvider->wantSignedLogoutRequest = $remoteServiceProviderConfigArray['wantSignedLogoutRequest'] ?? false;

        $remoteServiceProvider->save();

        return $remoteServiceProvider;
    }

    /**
     * @return RemoteServiceProviderConfigInterface
     */
    public function add(array $remoteServiceProviderConfigArray)
    {

        $remoteServiceProvider = new RemoteServiceProvider();

        $remoteServiceProvider->entityid = $remoteServiceProviderConfigArray['entityid'];
        $remoteServiceProvider->assertionConsumerService = isset($remoteServiceProviderConfigArray['AssertionConsumerService']) ? $remoteServiceProviderConfigArray['AssertionConsumerService'] : null;
        $remoteServiceProvider->singleLogoutService = isset($remoteServiceProviderConfigArray['SingleLogoutService']) ? $remoteServiceProviderConfigArray['SingleLogoutService'] : null;
        $remoteServiceProvider->keys = isset($remoteServiceProviderConfigArray['keys']) ? $remoteServiceProviderConfigArray['keys'] : null;

        $remoteServiceProvider->wantSignedAuthnResponse = $remoteServiceProviderConfigArray['wantSignedAuthnResponse'] ?? false;
        $remoteServiceProvider->wantSignedAssertions = $remoteServiceProviderConfigArray['wantSignedAssertions'] ?? false;
        $remoteServiceProvider->wantSignedLogoutResponse = $remoteServiceProviderConfigArray['wantSignedLogoutResponse'] ?? false;
        $remoteServiceProvider->wantSignedLogoutRequest = $remoteServiceProviderConfigArray['wantSignedLogoutRequest'] ?? false;
        
        $remoteServiceProvider->save();

        return $remoteServiceProvider;
        
    }

    public function deleteById(string $id)
    {
        RemoteServiceProvider::findOrFail($id)->delete();
    }


}