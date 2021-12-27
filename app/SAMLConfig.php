<?php

namespace App;

use ArieTimmerman\Laravel\AuthChain\Helper;
use ArieTimmerman\Laravel\AuthChain\State;
use ArieTimmerman\Laravel\AuthChain\AuthLevel;
use ArieTimmerman\Laravel\AuthChain\UIServer;
use ArieTimmerman\Laravel\SAML\SAML2\State\SamlState;
use ArieTimmerman\Laravel\SAML\Helper as SAMLHelper;
use ArieTimmerman\Laravel\AuthChain\Session;

class SAMLConfig extends \ArieTimmerman\Laravel\SAML\SAMLConfig
{

    /**
     * A non-null response will be returned as a HTTP response. Else, the logout flow continues.
     */
    public function doLogoutResponse()
    {
        
        Session::logout(request());

        return null;
    }

    public function doAuthenticationResponse(SamlState $samlState)
    {

        $isPassive = $samlState->getRequest()->getIsPassive();
        $isForce = $samlState->getRequest()->getForceAuthn();
        $requestedAuthnContext = $samlState->getRequest()->getRequestedAuthnContext() ?? [];

        $loginUrl = route('ice.login.ui', []);
        $parsed = parse_url($loginUrl);

        $uiServer = new UIServer(
            [
                $parsed['scheme'] . '://' . $parsed['host']
            ],
            [
                $loginUrl
            ]
        );
        
        $state = State::fromRequest(request());
        $state->setData($samlState);
        // This removes the need from storing the state in a session ...
        app()->instance(State::class, $state);
        
        //route('ssourl.continue')
        return Helper::getAuthResponseAsRedirect(
            request(), $state
            // ->setRequiredAuthLevel(AuthLevel::samlAll($requestedAuthnContext))
                ->setPrompt($isForce)
                ->setPassive($isPassive)
                ->setUiServer($uiServer)
                ->setOnFinishUrl(route('ssourl.continue'))
                ->setOnCancelUrl('http://cancel')
                ->setRetryUrl(url()->full())
        );

    }
}