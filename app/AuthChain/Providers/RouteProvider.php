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
                            '\App\Http\Controllers\AuthChain\Manage\TypeController@index'
                        );

                        Route::get(
                            '/manage/modules',
                            '\App\Http\Controllers\AuthChain\Manage\AuthModuleController@index'
                        );

                        Route::get(
                            '/manage/modules/{module_id}',
                            '\App\Http\Controllers\AuthChain\Manage\AuthModuleController@get'
                        );
                        Route::get(
                            '/manage/modules/info/{module_id}',
                            '\App\Http\Controllers\AuthChain\Manage\AuthModuleController@info'
                        );

                        Route::delete(
                            '/manage/modules/{module_id}',
                            '\App\Http\Controllers\AuthChain\Manage\AuthModuleController@delete'
                        );
                        Route::put(
                            '/manage/modules/{module_id}',
                            '\App\Http\Controllers\AuthChain\Manage\AuthModuleController@update'
                        );

                        Route::post(
                            '/manage/modules',
                            '\App\Http\Controllers\AuthChain\Manage\AuthModuleController@create'
                        );

                        Route::get(
                            '/manage/chain',
                            '\App\Http\Controllers\AuthChain\Manage\ChainController@index'
                        );
                        Route::post(
                            '/manage/chain',
                            '\App\Http\Controllers\AuthChain\Manage\ChainController@add'
                        );
                        Route::delete(
                            '/manage/chain/{chain_id}',
                            '\App\Http\Controllers\AuthChain\Manage\ChainController@delete'
                        );


                        Route::get(
                            '/manage/authlevels',
                            '\App\Http\Controllers\AuthChain\Manage\AuthLevelController@index'
                        );
                        Route::get(
                            '/manage/authlevel/{authlevel_id}',
                            '\App\Http\Controllers\AuthChain\Manage\AuthLevelController@get'
                        );
                        Route::delete(
                            '/manage/authlevel/{authlevel_id}',
                            '\App\Http\Controllers\AuthChain\Manage\AuthLevelController@delete'
                        );

                        Route::post(
                            '/manage/authlevels',
                            '\App\Http\Controllers\AuthChain\Manage\AuthLevelController@create'
                        );
                        Route::put(
                            '/manage/authlevel/{authlevel_id}',
                            '\App\Http\Controllers\AuthChain\Manage\AuthLevelController@update'
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
                    '\App\Http\Controllers\AuthChain\AuthChainController@redirect'
                );

                // web routes
                Route::get(
                    '/complete',
                    '\App\Http\Controllers\AuthChain\AuthChainController@complete'
                )->name('authchain.complete.get');
                Route::post(
                    '/complete',
                    '\App\Http\Controllers\AuthChain\AuthChainController@complete'
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
                    '\App\Http\Controllers\AuthChain\AuthChainController@getAuthResponse'
                );

                /**
                 * State inspection endpoint
                 */

                Route::options(
                    '/{module}/{state}',
                    '\App\Http\Controllers\AuthChain\AuthChainController@processOptions'
                );
                Route::get(
                    '/{module}/{state}',
                    '\App\Http\Controllers\AuthChain\AuthChainController@process'
                );

                Route::post(
                    '/{module}',
                    '\App\Http\Controllers\AuthChain\AuthChainController@process'
                )->name('chainProcessor');

                Route::fallback(
                    '\App\Http\Controllers\AuthChain\AuthChainController@notFound'
                );
            }
        );
    }
}
