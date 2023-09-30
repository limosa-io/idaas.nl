<?php

/**
 * Store the state for the authentication process.
 *
 * The state keeps track of the current result and desired result.
 * And ensures all steps in a multi-factor authentication are full filled.
 *
 * Preferably, the state storage should be super fast and temporary.
 */

namespace App\Http\Controllers\AuthChain;

use App\AuthChain\State;
use App\State as EloquentState;
use Illuminate\Support\Facades\Redis;

class StateStorage
{
    public static function saveState(State $state)
    {
        if (config('database.redis.enabled')) {
            Redis::set('state:'.$state->getstateId(), $state, 'EX', 3600);
        } else {
            return EloquentState::updateOrCreate(
                [
                    'id' => $state->getstateId(),
                ],
                [
                    'state' => $state,
                ]
            );
        }
    }

    public static function getStateFromSession($stateId)
    {
        if (config('database.redis.enabled')) {
            return Redis::get('state:'.$stateId);
        } else {
            $eloquentState = EloquentState::find($stateId);

            return $eloquentState ? $eloquentState->state : null;
        }
    }

    public static function deleteState(State $state)
    {
        if (config('database.redis.enabled')) {
            return Redis::del('state:'.$state->getstateId());
        } else {
            return EloquentState::destroy($state->getstateId());
        }
    }
}
