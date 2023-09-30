<?php

namespace App\Http\Controllers;

use App\AuthTypes\Facebook;
use App\AuthTypes\Google;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SocialLoginController extends Controller
{
    public function process(Request $request, string $type)
    {
        if (! preg_match('/^[a-zA-Z]+/', $type) || strlen($type) == 0) {
            throw new \Exception('Type is invalid');
        }

        $class = 'App\AuthTypes\\'.ucfirst($type);

        return ($class)::processCallback($request);
    }

    public function processFacebook(Request $request)
    {
        return Facebook::processCallback($request);
    }

    public function processGoogle(Request $request)
    {
        return Google::processCallback($request);
    }
}
