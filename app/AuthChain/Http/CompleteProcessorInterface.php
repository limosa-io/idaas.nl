<?php

namespace App\AuthChain\Http;

use Illuminate\Http\Request;
use App\AuthChain\State;
use Illuminate\Contracts\Auth\Authenticatable;

interface CompleteProcessorInterface
{
    public function onFinish(Request $request, State $state, Authenticatable $subject);

    public function onCancel(Request $request, ?State $state);
}
