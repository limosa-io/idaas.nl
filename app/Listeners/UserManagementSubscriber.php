<?php

namespace App\Listeners;

use App\Jobs\Webhook;
use App\Stats\Statter;
use App\Stats\StatableInterface;
use App\CloudFunction as CloudFunctionModel;
use App\Jobs\CloudFunction;
use ArieTimmerman\Laravel\SCIMServer\Helper;
use ArieTimmerman\Laravel\SCIMServer\ResourceType;
use App\User;

class UserManagementSubscriber
{
    protected $settings = null;

    public function handle(string $type, array $events)
    {
        // Do not pass GET requests to the webhook.
        if (strpos($type, '\Get') !== false) {
            return;
        }

        if (Webhook::getWebHookUrl() != null) {
            foreach ($events as $event) {
                Webhook::dispatch($event->model->toArray(), get_class($event));
            }
        }

        // On (create|attribute_change(any, email, )), => mail (user, mail-adress)
        //

        if (
            config('serverless.openwhisk_enabled') &&
            (
                $userEventCloudFunction = CloudFunctionModel::where('is_sequence', true)
                    ->where('type', CloudFunctionModel::TYPE_USER_EVENT)
                    ->first()
                ) != null
        ) {
            foreach ($events as $event) {
                // For now, only emit events for user objects
                if (!($event->model instanceof User)) {
                    continue;
                }

                $action = get_class($event);
                CloudFunction::dispatch(
                    $userEventCloudFunction,
                    [
                    'me' => $event->me,
                    'before' => $event->odlObjectArray ?? null,
                    'after' => Helper::objectToSCIMArray($event->model, ResourceType::user()),
                    'type' => strtolower(substr($action, strrpos($action, '\\') + 1))
                    ]
                );
            }
        }

        foreach ($events as $event) {
            if ($event->model instanceof StatableInterface) {
                Statter::emit($event->model, 'operation', $type);
            }

            if ($event->me) {
                Statter::emit($event->model, 'self-registration', null);
            }
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
            'ArieTimmerman\Laravel\SCIMServer\Events\*',
            'App\Listeners\UserManagementSubscriber@handle'
        );
    }
}
