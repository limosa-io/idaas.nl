<?php

namespace App;

use App\AuthChain\Helper;
use App\AuthChain\State;
use App\AuthChain\UIServer;
use ArieTimmerman\Laravel\SAML\SAML2\State\SamlState;
use App\AuthChain\Session;
use Illuminate\Support\Facades\URL;

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
        /** @var SAML2\AuthnRequest */
        $request = $samlState->getRequest();
        $isPassive = $request->getIsPassive();
        $isForce = $request->getForceAuthn();
        $requestedAuthnContext = $request->getRequestedAuthnContext() ?? [];

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
            request(),
            $state
            // ->setRequiredAuthLevel(AuthLevel::samlAll($requestedAuthnContext))
                ->setPrompt($isForce)
                ->setPassive($isPassive)
                ->setUiServer($uiServer)
                ->setOnFinishUrl(route('ssourl.continue'))
                ->setOnCancelUrl('http://cancel')
                ->setRetryUrl(URL::full())
        );
    }
}
