<?php

Route::group(['domain'=>'{tenant}.' . config('app.domain')], function ()
{
    
    \ArieTimmerman\Laravel\AuthChain\Providers\RouteProvider::routesApi();


    Route::get('/language/{locale}','LanguageController@get')->name('ice.login.sessions');

    Route::get('/uiSettings','TenantSettingController@uiSettings');


});
