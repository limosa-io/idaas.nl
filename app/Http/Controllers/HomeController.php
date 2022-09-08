<?php

/**
 * Used by the login form
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\OpenIDProvider;
use App\Tenant;
use App\AuthChain\Session;
use Illuminate\Contracts\Encryption\DecryptException;

class HomeController extends Controller
{
    public const COOKIE_FRAME_INFO = 'frame-info';

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->render(config('app.env') == 'development' ? 'login_debug' : 'login', [], $request);
    }

    public function isLoggedIn(Request $request)
    {
        return !empty(Session::getRemembered($request)->toArray()) ? 'true' : 'false';
    }

    public function render($view = 'login', $data = [], Request $request = null)
    {
        $tenant = resolve(Tenant::class);

        $response = response()->view(
            $view,
            $data + [
            'information' => [
                'manage' => route('ice.manage.home'),
                'resources_version' => $tenant->resources_version
            ]
            ]
        );

        // With Vue development/webpack server, it will make use of eval and unsafe-inline without nonce.
        // Therefore, only enable this for production

        // phpcs:ignore Generic.Files.LineLength.TooLong
        // $response = $response->header('Content-Security-Policy', 'default-src \'self\'; img-src *; style-src \'self\' \'unsafe-inline\'; script-src \'self\' \'unsafe-inline\' \'nonce-' . Controller::nonce() . '\'; frame-ancestors ' . route('ice.manage.home',[
        //     'tenant'=>$tenant->subdomain
        // ]) . ' ;');;

        if ($request->input('designer')) {
            $response = $response->header(
                'Content-Security-Policy',
                'frame-ancestors ' . route('ice.manage.home') . ';'
            );
        }

        return $response;
    }

    public function initDefault()
    {
        $provider = OpenIDProvider::first();

        return redirect($provider->init_url ?: route('ice.manage.home'));
    }
}
