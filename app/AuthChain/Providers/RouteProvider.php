<?php

namespace App\AuthChain\Providers;

use Illuminate\Support\Facades\Route;

/**
 * Helper class for the URL shortener
 */
class RouteProvider
{
    protected static $prefix = 'authchain';
    protected static $version = 'v2';

    public static function routesWeb(array $options = [])
    {
        $prefix = self::$prefix;

        Route::prefix(self::$prefix)->namespace('App\AuthChain\Http\Controllers')->group(
            function () use ($options, $prefix) {
                Route::prefix(self::$version)->group(
                    function () use ($options) {
                        self::webRoutes($options);
                    }
                );
            }
        );
    }

    public static function routesApi(array $options = [])
    {
        $prefix = self::$prefix;

        Route::prefix(self::$prefix)->namespace('App\AuthChain\Http\Controllers')->group(
            function () use ($options, $prefix) {
                Route::prefix(self::$version)->group(
                    function () use ($options) {
                        self::apiRoutes($options);
                    }
                );
            }
        );
    }

    public static function manageRoutes(array $options = [])
    {
        $prefix = self::$prefix;
        Route::prefix(self::$prefix)->namespace('App\AuthChain\Http\Controllers')->group(
            function () use ($options, $prefix) {
                Route::prefix(self::$version)->group(
                    function () use ($options) {
                        Route::get(
                            '/manage/types',
                            '\App\AuthChain\Http\Controllers\Manage\TypeController@index'
                        );

                        Route::get(
                            '/manage/modules',
                            '\App\AuthChain\Http\Controllers\Manage\AuthModuleController@index'
                        );

                        Route::get(
                            '/manage/modules/{module_id}',
                            '\App\AuthChain\Http\Controllers\Manage\AuthModuleController@get'
                        );
                        Route::get(
                            '/manage/modules/info/{module_id}',
                            '\App\AuthChain\Http\Controllers\Manage\AuthModuleController@info'
                        );

                        Route::delete(
                            '/manage/modules/{module_id}',
                            '\App\AuthChain\Http\Controllers\Manage\AuthModuleController@delete'
                        );
                        Route::put(
                            '/manage/modules/{module_id}',
                            '\App\AuthChain\Http\Controllers\Manage\AuthModuleController@update'
                        );

                        Route::post(
                            '/manage/modules',
                            '\App\AuthChain\Http\Controllers\Manage\AuthModuleController@create'
                        );

                        Route::get(
                            '/manage/chain',
                            '\App\AuthChain\Http\Controllers\Manage\ChainController@index'
                        );
                        Route::post(
                            '/manage/chain',
                            '\App\AuthChain\Http\Controllers\Manage\ChainController@add'
                        );
                        Route::delete(
                            '/manage/chain/{chain_id}',
                            '\App\AuthChain\Http\Controllers\Manage\ChainController@delete'
                        );


                        Route::get(
                            '/manage/authlevels',
                            '\App\AuthChain\Http\Controllers\Manage\AuthLevelController@index'
                        );
                        Route::get(
                            '/manage/authlevel/{authlevel_id}',
                            '\App\AuthChain\Http\Controllers\Manage\AuthLevelController@get'
                        );
                        Route::delete(
                            '/manage/authlevel/{authlevel_id}',
                            '\App\AuthChain\Http\Controllers\Manage\AuthLevelController@delete'
                        );

                        Route::post(
                            '/manage/authlevels',
                            '\App\AuthChain\Http\Controllers\Manage\AuthLevelController@create'
                        );
                        Route::put(
                            '/manage/authlevel/{authlevel_id}',
                            '\App\AuthChain\Http\Controllers\Manage\AuthLevelController@update'
                        );
                    }
                );
            }
        );
    }

    private static function webRoutes(array $options = [])
    {
        Route::prefix('p')->group(
            function () {
                Route::get(
                    '/redirect/{module}/{state}',
                    '\App\AuthChain\Http\Controllers\AuthChainController@redirect'
                );

                // web routes
                Route::get(
                    '/complete',
                    '\App\AuthChain\Http\Controllers\AuthChainController@complete'
                )->name('authchain.complete.get');
                Route::post(
                    '/complete',
                    '\App\AuthChain\Http\Controllers\AuthChainController@complete'
                )->name('authchain.complete');
            }
        );
    }

    private static function apiRoutes(array $options = [])
    {
        Route::prefix('p')->group(
            function () {

                /**
                 *
                 */
                Route::get(
                    '/authresponse/{state}',
                    '\App\AuthChain\Http\Controllers\AuthChainController@getAuthResponse'
                );

                /**
                 * State inspection endpoint
                 */

                Route::options(
                    '/{module}/{state}',
                    '\App\AuthChain\Http\Controllers\AuthChainController@processOptions'
                );
                Route::get(
                    '/{module}/{state}',
                    '\App\AuthChain\Http\Controllers\AuthChainController@process'
                );

                Route::post(
                    '/{module}',
                    '\App\AuthChain\Http\Controllers\AuthChainController@process'
                )->name('chainProcessor');

                Route::fallback(
                    '\App\AuthChain\Http\Controllers\AuthChainController@notFound'
                );
            }
        );
    }
}
