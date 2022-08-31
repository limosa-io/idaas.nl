<?php

namespace App\Repository;

use ArieTimmerman\Laravel\SAML\Repository\HostedIdentityProviderConfigRepositoryInterface;
use App\HostedIdentityProvider;

class HostedIdentityProviderConfigRepository implements HostedIdentityProviderConfigRepositoryInterface
{
    /**
     * @return HostedIdentityProviderConfigInterface
     */
    public function get()
    {
        return HostedIdentityProvider::first();
    }

    public function patch(array $remoteIdentityProviderConfigArray)
    {
        $hostedIdentityProvider = HostedIdentityProvider::first();

        if ($hostedIdentityProvider == null) {
            $hostedIdentityProvider = new HostedIdentityProvider();
        }

        $hostedIdentityProvider->previousSession = $remoteIdentityProviderConfigArray['PreviousSession'];

        $hostedIdentityProvider->signAuthnrequest = $remoteIdentityProviderConfigArray['sign.authnrequest'] ?? true;
        // phpcs:ignore Generic.Files.LineLength.TooLong
        $hostedIdentityProvider->metadataSignEnable = $remoteIdentityProviderConfigArray['metadata.sign.enable'] ?? true;
        $hostedIdentityProvider->redirectSign = $remoteIdentityProviderConfigArray['redirect.sign'] ?? true;
        $hostedIdentityProvider->ssoHttpPostEnabled = $remoteIdentityProviderConfigArray['ssoHttpPostEnabled'];
        $hostedIdentityProvider->ssoHttpRedirectEnabled = $remoteIdentityProviderConfigArray['ssoHttpRedirectEnabled'];
        $hostedIdentityProvider->sloHttpPostEnabled = $remoteIdentityProviderConfigArray['sloHttpPostEnabled'];
        $hostedIdentityProvider->sloHttpRedirectEnabled = $remoteIdentityProviderConfigArray['sloHttpRedirectEnabled'];


        $hostedIdentityProvider->keys = $remoteIdentityProviderConfigArray['keys'];
        // phpcs:ignore Generic.Files.LineLength.TooLong
        $hostedIdentityProvider->supportedNameIDFormat = $remoteIdentityProviderConfigArray['supportedNameIDFormat'] ?? [];
        $hostedIdentityProvider->contacts = $remoteIdentityProviderConfigArray['contacts'] ?? [];
        $hostedIdentityProvider->organization = $remoteIdentityProviderConfigArray['organization'] ?? [];

        $hostedIdentityProvider->save();

        return $hostedIdentityProvider;
    }
}
