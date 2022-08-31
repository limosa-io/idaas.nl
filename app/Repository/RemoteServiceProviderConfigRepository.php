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

        // phpcs:ignore Generic.Files.LineLength.TooLong
        $remoteServiceProvider->entityid = $remoteServiceProviderConfigArray['entityid'];
        // phpcs:ignore Generic.Files.LineLength.TooLong
        $remoteServiceProvider->assertionConsumerService = $remoteServiceProviderConfigArray['AssertionConsumerService'];
        // phpcs:ignore Generic.Files.LineLength.TooLong
        $remoteServiceProvider->singleLogoutService = isset($remoteServiceProviderConfigArray['SingleLogoutService']) ? $remoteServiceProviderConfigArray['SingleLogoutService'] : null;
        // phpcs:ignore Generic.Files.LineLength.TooLong
        $remoteServiceProvider->keys = isset($remoteServiceProviderConfigArray['keys']) ? $remoteServiceProviderConfigArray['keys'] : null;
        // phpcs:ignore Generic.Files.LineLength.TooLong
        $remoteServiceProvider->wantSignedAuthnResponse = $remoteServiceProviderConfigArray['wantSignedAuthnResponse'] ?? false;
        // phpcs:ignore Generic.Files.LineLength.TooLong
        $remoteServiceProvider->wantSignedAssertions = $remoteServiceProviderConfigArray['wantSignedAssertions'] ?? false;
        // phpcs:ignore Generic.Files.LineLength.TooLong
        $remoteServiceProvider->wantSignedLogoutResponse = $remoteServiceProviderConfigArray['wantSignedLogoutResponse'] ?? false;
        // phpcs:ignore Generic.Files.LineLength.TooLong
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
        // phpcs:ignore Generic.Files.LineLength.TooLong
        $remoteServiceProvider->assertionConsumerService = isset($remoteServiceProviderConfigArray['AssertionConsumerService']) ? $remoteServiceProviderConfigArray['AssertionConsumerService'] : null;
        // phpcs:ignore Generic.Files.LineLength.TooLong
        $remoteServiceProvider->singleLogoutService = isset($remoteServiceProviderConfigArray['SingleLogoutService']) ? $remoteServiceProviderConfigArray['SingleLogoutService'] : null;
        // phpcs:ignore Generic.Files.LineLength.TooLong
        $remoteServiceProvider->keys = isset($remoteServiceProviderConfigArray['keys']) ? $remoteServiceProviderConfigArray['keys'] : null;
        // phpcs:ignore Generic.Files.LineLength.TooLong
        $remoteServiceProvider->wantSignedAuthnResponse = $remoteServiceProviderConfigArray['wantSignedAuthnResponse'] ?? false;
        // phpcs:ignore Generic.Files.LineLength.TooLong
        $remoteServiceProvider->wantSignedAssertions = $remoteServiceProviderConfigArray['wantSignedAssertions'] ?? false;
        // phpcs:ignore Generic.Files.LineLength.TooLong
        $remoteServiceProvider->wantSignedLogoutResponse = $remoteServiceProviderConfigArray['wantSignedLogoutResponse'] ?? false;
        // phpcs:ignore Generic.Files.LineLength.TooLong
        $remoteServiceProvider->wantSignedLogoutRequest = $remoteServiceProviderConfigArray['wantSignedLogoutRequest'] ?? false;

        $remoteServiceProvider->save();

        return $remoteServiceProvider;
    }

    public function deleteById(string $id)
    {
        RemoteServiceProvider::findOrFail($id)->delete();
    }
}
