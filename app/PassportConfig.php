<?php

namespace App;

use ArieTimmerman\Laravel\AuthChain\Helper;
use ArieTimmerman\Laravel\AuthChain\State;
use ArieTimmerman\Laravel\AuthChain\AuthLevel;
use ArieTimmerman\Laravel\AuthChain\UIServer;
use ArieTimmerman\Laravel\AuthChain\Session;
use App\Http\Controllers\HomeController;
use Idaas\OpenID\RequestTypes\AuthenticationRequest;
use Idaas\Passport\ClientRepository;
use Idaas\Passport\PassportConfig as IdaasPassportConfig;
use Illuminate\Support\Facades\URL;

class PassportConfig extends IdaasPassportConfig
{
    public function doAuthenticationResponse(AuthenticationRequest $authenticationRequest)
    {
        $client = resolve(ClientRepository::class)
            ->find($authenticationRequest->getClient()->getIdentifier());

        $loginUrl = route('ice.login.ui', []);
        $parsed = parse_url($loginUrl);

        // TODO: Only set a different UI server for the non-master server
        // TODO: Get UIServer from Client settings??? $client->ui_server ??
        $index = 0;

        // In case of a popup request, the UIServer list is not checked.
        if ($authenticationRequest->getDisplay() == 'popup') {
            $uiServer = new UIServer(
                [
                    $parsed['scheme'] . '://' . $parsed['host'],
                ],
                [
                    $authenticationRequest->getRedirectUri(), // the default
                    $loginUrl // for the first call
                ]
            );
            $index = 1;
        } else {
            // TODO: Check the configured $client->user_interface. If not null, set that as the userinterface.
            $uiServer = new UIServer(
                [
                    $parsed['scheme'] . '://' . $parsed['host']
                ],
                [
                    $loginUrl
                ]
            );
        }

        $acrValues =
            (
                $client->default_acr_values_allow_override ?
                AuthLevel::oidcAll($authenticationRequest->getAcrValues())
                : null
            )
            ?? $client->defaultAcrValues->all();

        // from request allows reading cookies and session_ids
        $state = State::fromRequest(request());
        $state->setData($authenticationRequest);

        // This removes the need from storing the state in a session ...
        app()->instance(State::class, $state);

        $scopeStringArray = (array) json_decode(json_encode($authenticationRequest->getScopes()));

        $frameAncestor = substr(
            $authenticationRequest->getRedirectUri(),
            0,
            strpos($authenticationRequest->getRedirectUri(), '/', 8)
        );

        $prompt = false;

        if ($client->default_prompt_allow_override) {
            $prompt = $authenticationRequest->getPrompt() == 'login';
        } else {
            $prompt = $client->default_prompt == 'login';
        }

        /**
         * Returns a regular AuthChain response.
         *
         * If the request is completed, uses CompleteProcessorInterface App\Http\AuthChainCompleteProcessor
         */
        return Helper::getAuthResponseAsRedirect(
            request(),
            $state
                ->setRequiredAuthLevel(empty($acrValues) ? null : $acrValues)
                ->setPrompt(
                    $prompt
                )
                ->setPassive((
                    (
                        $client->default_prompt_allow_override ?
                        $authenticationRequest->getPrompt() :
                        null
                    ) ?? $client->default_prompt) == 'none')
                ->setLoginHint($authenticationRequest->getLoginHint())
                ->setMaxAge($prompt ? 1 : $authenticationRequest->getMaxAge())
                //->setUserSubjectHint(null)
                ->setUiServer($uiServer)
                ->setLogo($client->logo_uri)
                ->setName($client->name)
                ->setDisplay($authenticationRequest->getDisplay())
                ->setAppId($client->client_id)
                ->setTermsUrl($client->tos_uri)
                ->setPolicyUrl($client->policy_uri)
                ->setRequestedScopes($scopeStringArray)
                ->setDefaultScopes($client->trusted ? $scopeStringArray : [])
                ->setRequestedClaims(['claim1'])
                ->setOnFinishUrl(route('authorize_continue_chain', ['state' => $state->getstateId()]))
                ->setOnCancelUrl(route('authorize_continue_chain', ['state' => $state->getstateId()]))
                ->setRetryUrl(
                    URL::full()
                ),
            $index,
            true
        )->header(
            'Content-Security-Policy',
            'frame-ancestors ' . $frameAncestor . ';'
        )->withCookie(
            cookie(HomeController::COOKIE_FRAME_INFO, encrypt($frameAncestor), 20, '/')
        );
    }

    public function doLogoutResponse(\Illuminate\Http\Request $request, $valid, $redirectUri, $state)
    {
        Session::logout($request);

        //TODO: add frame headers
        $frameAncestor = substr($redirectUri, 0, strpos($redirectUri, '/', 8));

        return app(HomeController::class)->render(
            'passport::logout',
            [
                'data' => [
                    'post_logout_redirect_uri' => $redirectUri,
                    'response_mode' => $request->input('response_mode', 'query'),
                    'state' => $state,
                    'valid' => $valid
                ]
            ],
            $request
        )
            ->header(
                'Content-Security-Policy',
                'frame-ancestors ' . $frameAncestor . ';'
            )->withCookie(
                cookie(HomeController::COOKIE_FRAME_INFO, encrypt($frameAncestor), 20, '/')
            );
    }
}
