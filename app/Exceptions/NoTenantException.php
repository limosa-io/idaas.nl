<?php

namespace App\Exceptions;

use Exception;
use Session;

class NoTenantException extends Exception
{
    /**
     * Report the exception.
     *
     * @return void
     */
    public function report()
    {
        //
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        // var_dump(session('test'));exit;
        // session(['test'=>rand(0,9999)]);
        // Session::put('test','test');

        return response()->view('errors/404-NoTenant', [], 404);
    }
}
