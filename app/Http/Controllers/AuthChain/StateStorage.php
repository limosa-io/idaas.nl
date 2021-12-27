<?php
/**
 * Store the state for the authentication process.
 * 
 * The state keeps track of the current result and desired result. And ensures all steps in a multi-factor authentication are full filled.
 * 
 * Preferably, the state storage should be super fast and temporary.
 */
namespace App\Http\Controllers\AuthChain;

use ArieTimmerman\Laravel\AuthChain\StateStorage as BaseStateStorage;
use ArieTimmerman\Laravel\AuthChain\State;
use Illuminate\Support\Facades\Redis;
use App\State as EloquentState;

class StateStorage extends BaseStateStorage
{

    public function getEloquentClass()
    {
        return EloquentState::class;
    }  

    public function saveState(State $state)
    {
        if (config('database.redis.enabled')) {
            Redis::set('state:' . $state->getstateId(), $state, 'EX', 3600);
        } else {
            return parent::saveState($state);
        }

    }

    public function getStateFromSession($stateId)
    {
        if (config('database.redis.enabled')) {
            return Redis::get('state:' . $stateId);
        } else {
            return parent::getStateFromSession($stateId);
        }
    }

    public function deleteState(State $state)
    {

        if (config('database.redis.enabled')) {
            return Redis::del('state:' . $stateId);
        } else {
            return parent::deleteState($state);
        }

    }


}
