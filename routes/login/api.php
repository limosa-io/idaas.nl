<?php

Route::group(['domain'=>'{tenant}.' . config('app.domain')], function ()
{
    
    \App\AuthChain\Providers\RouteProvider::routesApi();


    Route::get('/language/{locale}','LanguageController@get');

    Route::get('/uiSettings','TenantSettingController@uiSettings');


});
