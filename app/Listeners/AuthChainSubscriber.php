<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\AuthChain\Events\Authenticated;
use App\Stats\Statter;
use App\AuthChain\Events\LoggedOut;
use App\Token;
use App\Http\Controllers\AuthChain\RememberStorage;
use App\TenantSetting;
use App\AuthLevel;

class AuthChainSubscriber
{
    public function onLoggedOut(LoggedOut $event)
    {
        $accessTokens = Session::get('access_tokens', []);

        foreach ($accessTokens as $accessToken) {
            $token = Token::find($accessToken);

            if ($token != null) {
                if ($token->can('online_access')) {
                    $token->revoke();
                }
            } else {
                Log::debug('Could not find token: ' . $accessToken);
            }
        }

        resolve(RememberStorage::class)->clearModuleResults(request());
    }

    public function onAuthenticated(Authenticated $event)
    {
        /**
         * Depending on the registration settings,
         * make a user active when he has reached a certain authentication level.
         */
        Log::debug('received log in event!');

        if ($event->state->getSubject() != null &&  ($user = $event->state->getSubject()->getUser()) != null) {
            if (!$user->active) {
                $setting = TenantSetting::where('key', 'registration.level_active')->first();

                $level = $setting != null ?
                    AuthLevel::find($setting->value) :
                    AuthLevel::where('level', 'activation')->first();

                if ($level != null) {
                    if (in_array($level, $event->state->getLevels())) {
                        $user->active = true;
                        $user->save();
                    }
                }
            }

            $user->last_successful_login_date = new \DateTime();
            $user->save();

            Statter::emit($user, 'login', $event->state->appId);
        } elseif ($event->state->getSubject() != null) {
            // This is a login of a client
            // Statter::emit($user,'login',null);
        }
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'App\AuthChain\Events\Authenticated',
            'App\Listeners\AuthChainSubscriber@onAuthenticated'
        );

        $events->listen(
            'App\AuthChain\Events\LoggedOut',
            'App\Listeners\AuthChainSubscriber@onLoggedOut'
        );
    }
}
