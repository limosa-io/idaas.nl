<?php

use App\Providers\AuthRouteProvider;
use Illuminate\Support\Facades\Route;

Route::group(['domain' => '{tenant}.'.config('app.domain')], function () {
    AuthRouteProvider::routesApi();
    Route::get('/language/{locale}', 'LanguageController@get');
    Route::get('/uiSettings', 'TenantSettingController@uiSettings');
});
