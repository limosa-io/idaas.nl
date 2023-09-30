<?php

/**
 * Logs the user out via CORS.
 *
 * Note: refers to Session from the authentication chain module.
 */

namespace App\Http\Controllers;

use App\AuthChain\Session;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function logout(Request $request)
    {
        Session::logout($request);

        return 'ok';
    }
}
