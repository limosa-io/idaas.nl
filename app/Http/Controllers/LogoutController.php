<?php
/**
 * Logs the user out via CORS.
 * 
 * Note: refers to Session from the authentication chain module.
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use ArieTimmerman\Laravel\AuthChain\Session;

class LogoutController extends Controller
{

    public function logout(Request $request)
    {
        Session::logout($request);

        return "ok";
    }
}
