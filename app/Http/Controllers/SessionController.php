<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\AuthChain\RememberStorage;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function index(Request $request)
    {
        //TODO: join module. Return rememberLifetime, time, rememberAlways, rememberForSession, prompted, levels
        return resolve(RememberStorage::class)->getRememberedModuleResults($request);
    }
}
