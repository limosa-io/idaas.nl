<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Route;

// tenant123.manage.test.com
// tenant123.test.com

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['domain' => '{tenant}.manage.' . config('app.domain')], function ()
{

    Route::get('/completelogin', '\App\Http\Controllers\Manage\HomeController@index')->name('ice.manage.completelogin');
    Route::get('/logout', '\App\Http\Controllers\Manage\HomeController@index')->name('ice.manage.completelogout');

    //This route simply redirects to the vue/html application
    Route::get('/users/edit/{user_id}','\App\Http\Controllers\Manage\HomeController@index')->name('ice.manage.profile');

    Route::get('/', '\App\Http\Controllers\Manage\HomeController@index')->name('ice.manage.home');

    Route::fallback('\App\Http\Controllers\Manage\HomeController@index');

});




