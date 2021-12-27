<?php

namespace App\AuthTypes;

use ArieTimmerman\Laravel\AuthChain\Types\AbstractType;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use ArieTimmerman\Laravel\AuthChain\State;
use ArieTimmerman\Laravel\AuthChain\Helper;
use ArieTimmerman\Laravel\AuthChain\Module\ModuleInterface;
use ArieTimmerman\Laravel\AuthChain\Module\Message;

abstract class Generic extends AbstractType
{

    /**
     * Return `Laravel\Socialite\Two\FacebookProvider::class` or similar
     */
    abstract public function getSocialProvider();

    /**
     * For the Admin
     */
    public function getConfigValidation()
    {
        return [
            'config' => 'nullable|array',
            'config.client_id' => 'nullable',
            'config.client_secret' => 'nullable',
            'config.create_user' => 'nullable|boolean'
        ];
    }

    public function getDefaultGroup()
    {
        return 'social';
    }

    /**
     * Module specific
     */
    public function getInfo()
    {
        return [
            'callback' => route(
                'authchain.social.callback', [
                'type' => lcfirst($this->getIdentifier())
                ]
            )
        ];
    }


    /**
     * Module specific
     */
    public static function processCallback(Request $request)
    {
        
        $state = Helper::loadStateFromSession(app(), $request->query->get('state'));

        $module = $state->getIncomplete()->getModule();
        
        $result = $module->process($request, $state);

        $state->addResult($result);

        Helper::saveState($state);

        return Helper::getAuthResponseAsRedirect($request, $state);
    }

    /**
     * Initialize the module
     */
    public function init(Request $request, State $state, ModuleInterface $module)
    {
        
        $this->remembered = $state->getRememberedModuleResult($module) != null;

    }

    public function remembered()
    {
        return false; //$this->remembered;
    }

    public function isConfigured($module)
    {
        $config = $module->config;
        
        return isset($config['client_id']) && isset($config['client_secret']);
    }

     /**
      * Module specific
      */
    public function getRedirect(ModuleInterface $module, State $state)
    {

        $provider = Socialite::buildProvider(
            $this->getSocialProvider(), [
            'client_id' => $module->config['client_id'],
            'client_secret' => $module->config['client_secret'],
            'redirect' => route(
                'authchain.social.callback', [
                'type' => $this->getIdentifier()
                ]
            ),
            ]
        );

        $result = $provider->with(['state' => (string)$state])->stateless();
        
        if($state->display == 'popup') {
            $result = $result->asPopup();
        }

        return $result->redirect();
    }

    /**
     * Process the module
     */
    public function process(Request $request, State $state, ModuleInterface $module)
    {

        if(!$this->isConfigured($module)) {
            return $module->baseResult()->addMessage(Message::error('This module has not yet been configured'))->setCompleted(false);
        }
        
        if($request->input('init')) {
            
            return $module->baseResult()->setResponse(
                response(
                    [
                    'url' => $this->getRedirect($module, $state)->getTargetUrl()
                    ]
                )
            )->setCompleted(false);

        }else{
                     
            $provider = Socialite::buildProvider(
                $this->getSocialProvider(), [
                'client_id' => $module->config['client_id'],
                'client_secret' => $module->config['client_secret'],
                'redirect' => route(
                    'authchain.social.callback', [
                    'type' => lcfirst($this->getIdentifier())
                    ]
                ),
                ]
            );
            $provider = $provider->stateless();

            $user = $provider->user();

            return $module->baseResult()->setCompleted(true)->setSubject((new SocialSubject($this->getIdentifier(), $user))->setTypeIdentifier($this->getIdentifier()));

        }
    }

    public function canAutoRedirect()
    {
        return true;
    }

    public function getRedirectResponse(Request $request, State $state, ModuleInterface $module)
    {
        return $module->baseResult()->setResponse(
            response(
                null, 302, [
                'location' => $this->getRedirect($module, $state)->getTargetUrl()
                ]
            )
        )->setCompleted(false)->setPrompted(false);
    }

    public function shouldCreateUser(ModuleInterface $module)
    {
        return isset($module->config['create_user']) && $module->config['create_user'];
    }

}