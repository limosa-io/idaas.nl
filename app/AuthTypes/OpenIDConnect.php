<?php

namespace App\AuthTypes;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use ArieTimmerman\Laravel\AuthChain\State;
use ArieTimmerman\Laravel\AuthChain\Module\ModuleInterface;
use ArieTimmerman\Laravel\AuthChain\Repository\SubjectRepositoryInterface;
use ArieTimmerman\Laravel\AuthChain\Types\AbstractType;
use ArieTimmerman\Laravel\AuthChain\Helper;
use GuzzleHttp\Exception\RequestException;
use ArieTimmerman\Laravel\AuthChain\Module\Message;

class OpenIDConnect extends AbstractType
{
    // Guzzle handler. Set for testing
    public static $handler = null;

    /**
     * Validate configuration parameters
     */
    public function getConfigValidation()
    {
        return [
            'config.client_id' => 'required',
            'config.client_secret' => 'required',
            'config.userinfo_endpoint' => 'required|url',
            // 'config.introspection_endpoint' => 'required|url',
            'config.scopes' => 'required',
            'config.authorization_endpoint' => 'required',
            'config.token_endpoint' => 'required',

            'config.button_color' => 'nullable',
            'config.button_text' => 'nullable',
        ];
    }

    public function getDefaultName()
    {
        return "OpenID Connect";
    }

    public function getPublicConfigKeys()
    {
        return [
            'button_color',
            'button_text'
        ];
    }

    /**
     * Info gets presented in the management ui
     */
    public function getInfo()
    {
        return [
            'callback' => route('ice.login.openid')
        ];
    }

    public function init(Request $request, State $state, ModuleInterface $module)
    {
        $this->remembered = $state->getRememberedModuleResult($module) != null;
    }

    public function remembered()
    {
        return $this->remembered;
    }

    public function getDefaultGroup()
    {
        return 'social';
    }

    public function processCallback(Request $request)
    {
        $state = decrypt($request->query->get('state'));

        $state = Helper::loadStateFromSession(app(), $state['state']);

        $module = $state->getIncomplete()->getModule();

        $config = $module->config;

        $guzzle = new \GuzzleHttp\Client(
            [
                'verify' => false,
                'handler' => self::$handler
            ]
        );

        try {
            //exchange authorization code
            $response = $guzzle->request(
                'POST',
                $config['token_endpoint'],
                [
                    'form_params' => [
                        'grant_type' => 'authorization_code',
                        'code' => $request->input('code'),
                        'redirect_uri' => route('ice.login.openid'),
                        'client_id' => $config['client_id'],
                        'client_secret' => $config['client_secret'],
                    ]
                ]
            );

            $result = json_decode((string) $response->getBody(), true);

            $accessToken = $result['access_token'];

            $response = $guzzle->get(
                $config['userinfo_endpoint'],
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken
                    ]
                ]
            );

            $body = (string) $response->getBody();
            $userinfo = json_decode($body, true);

            $state->addResult(
                $module->baseResult()
                    ->setCompleted(true)
                    ->setSubject(
                        resolve(SubjectRepositoryInterface::class)
                            ->with(
                                $userinfo['sub'],
                                $this,
                                $module
                            )->setTypeIdentifier(
                                $this->getIdentifier()
                            )->setAttributes($userinfo)
                    )
            );

            return Helper::getAuthResponseAsRedirect($request, $state);
        } catch (RequestException $e) {
            Log::error($e);
            $state->addResult($module->baseResult()->addMessage(Message::error('Could not authenticate using this method. Is the authorization host reachable?')));

            return Helper::getAuthResponseAsRedirect($request, $state);
        }
    }

    protected function getUrl(State $state, ModuleInterface $module)
    {
        $config = $module->config;

        $parameters = [
            'response_type' => 'code',
            'client_id' => $config['client_id'],
            'scope' => $config['scopes'],
            'redirect_uri' => route('ice.login.openid'),
            'state' => encrypt(['state' => (string) $state, 'url' => 'https://test123.nl'])
        ];

        if ($state->needsPrompt() && !$state->getModuleResults()->hasPrompted()) {
            $parameters['prompt'] = 'login';
        }

        $url = $config['authorization_endpoint'] . '?' . http_build_query($parameters);

        return $url;
    }

    public function canAutoRedirect()
    {
        return true;
    }

    public function getRedirectResponse(Request $request, State $state, ModuleInterface $module)
    {
        return $module->baseResult()->setResponse(response(null, 302, ['location' => $this->getUrl($state, $module)]))->setCompleted(false)->setPrompted(false);
    }

    public function process(Request $request, State $state, ModuleInterface $module)
    {
        if (($remembered = $state->getRememberedModuleResult($module)) != null) {
            return $remembered->setPrompted(false);
        } elseif ($request->input('init')) {
            // TODO: on init return /sp_init url?
            $url = $this->getUrl($state, $module);

            return $module->baseResult()->setResponse(
                response(
                    [
                        'url' => $url
                    ]
                )
            )->setCompleted(false);
        } else {
        }
    }

    public static function setGuzzleHandler($handler)
    {
        self::$handler = $handler;
    }
}
