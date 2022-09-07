<?php

namespace App\AuthChain\Http;

use App\AuthChain\Http\CompleteProcessorInterface;
use Illuminate\Http\Request;
use App\AuthChain\Helper;
use App\AuthChain\State;
use Illuminate\Contracts\Auth\Authenticatable;

class CompleteProcessor implements CompleteProcessorInterface
{
    public function onFinish(Request $request, State $state, Authenticatable $subject)
    {
        Helper::deleteState($state);

        return redirect($state->onFinishUrl);
    }

    public function onCancel(Request $request, ?State $state)
    {
        return redirect($state->onCancelUrl);
    }
}
