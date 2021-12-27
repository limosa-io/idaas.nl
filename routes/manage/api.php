<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


// Route::get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['domain' => '{tenant}.manage.' . config('app.domain') ], function ()
{

    Route::get('/ping', '\App\Http\Controllers\Manage\HomeController@ping')->name('ice.ping');
        
    //require scope: manage:users
    \ArieTimmerman\Laravel\SCIMServer\RouteProvider::routes();

    \ArieTimmerman\Laravel\SAML\RouteProvider::routesManage();
    
    Route::get('oAuthScope/mapping', 'OAuthScopeController@mapping');
    Route::resource('oAuthScope', 'OAuthScopeController')->only([
        'index', 'store', 'update', 'destroy'
    ]);

    Route::post('/preview_mail_template/{mail_template}', 'MailTemplateController@preview');
    Route::resource('mail_template', 'MailTemplateController')->only([
        'index', 'store', 'show', 'update', 'destroy'
    ]);

    Route::resource('mediaItems', 'MediaItemController')->only([
        'index', 'store', 'show', 'destroy'
    ]);

    Route::get('/example_client', 'MediaItemController@getExampleClient');

    Route::resource('openidKey', 'OpenIDKeyController')->only([
        'index', 'store', 'destroy', 'update'
    ]);

    Route::post('openidKey/createGenerated','OpenIDKeyController@createGenerated');

    Route::get('/routes', 'GetRoutes@index');
    
    Route::delete('/tenants/{id}', 'TenantController@destroy'); // ->middleware('can:manageOther:App\Tenant')

    Route::post('/cloudFunctions/invoke/{cloudFunction}', 'CloudFunctionController@invoke');
    
    Route::resource('cloudFunctions', 'CloudFunctionController')->only([
        'index', 'store', 'show', 'update', 'destroy'
    ]);

    Route::post('/tokens/revoke', 'TokenController@revoke');
    Route::get('/tokens', 'TokenController@index');
    
    Route::delete('/moduleResults/{moduleResultId}', 'ModuleResultController@delete');
    Route::get('/moduleResults', 'ModuleResultController@index');

    Route::get('/stats/loginsPerDay30Days', 'StatController@loginsPerDay30Days');
    Route::get('/stats/dashboard', 'StatController@dashboard');

    Route::get('/language/defaults/{locale}', 'LanguageController@defaults');
    Route::get('/language/customizations/{locale}', 'LanguageController@customizations');
    Route::put('/language/customizations/{locale}', 'LanguageController@store');

    Route::post('/s3sign', 'UploadController@s3sign');

    Route::put('settings/bulk','TenantSettingController@updateMany');
    Route::resource('settings', 'TenantSettingController')->only([
        'index', 'store', 'show', 'update', 'destroy'
    ]);

    Route::post('/initTest', '\App\Http\Controllers\OAuth\AuthorizationController@initTest')->name('authorize_init_test');

    Route::post('/import', 'ImportController@index');
    Route::get('/export', 'ImportController@export');

    Route::resource('uiServers', 'UIServerController')->only([
        'index', 'store', 'show', 'update', 'destroy'
    ]);

});
