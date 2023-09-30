<?php

use App\Providers\AuthRouteProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//TODO: Return clients, apis, users, whatever, based on 'scope'??
Route::group(['domain' => '{tenant}.'.config('app.domain')], function () {

    //TODO: should return sessions
    Route::get('/sessions', 'SessionController@index')->name('ice.login.sessions');

    // Route::get('/session', function(Request $request){

    //     return $request->session()->getId();

    // });

    Route::get(
        '/continue/{state}',
        '\App\Http\Controllers\OAuth\AuthorizationController@continueAuthorizeWtithState'
    )->name('authorize_continue_chain');

    Route::get('/init_default', 'HomeController@initDefault')->name('ice.login.default');

    Route::get('/', 'HomeController@index')->name('ice.login.ui');
    Route::get('/isLoggedIn', 'HomeController@isLoggedIn')->name('ice.login.isLoggedIn');

    Route::fallback('HomeController@index');

    Route::get('/register', 'HomeController@index')->name('ice.login.register');

    Route::get('/passwordless', '\App\AuthTypes\Passwordless@processCallback')->name('ice.login.passwordless');
    Route::get(
        '/passwordforgotten',
        '\App\AuthTypes\PasswordForgotten@processCallback'
    )->name('ice.login.passwordforgotten');
    Route::get('/activateaccount', '\App\AuthTypes\Activation@processCallback')->name('ice.login.activation');

    Route::get('/oidc/callback', '\App\AuthTypes\OpenIDConnect@processCallback')->name('ice.login.openid');

    AuthRouteProvider::routesWeb();

    \ArieTimmerman\Laravel\SAML\RouteProvider::routes();

    Route::get(
        'login/social/callback/{type}',
        '\App\Http\Controllers\SocialLoginController@process'
    )->name('authchain.social.callback');
});
