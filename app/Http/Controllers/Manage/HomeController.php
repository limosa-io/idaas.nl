<?php

/**
 * The home controller for the admin ui.\App\Repository\AuthCodeRepository
 *
 * Shows the admin page.
 */

namespace App\Http\Controllers\Manage;

class HomeController extends \App\Http\Controllers\Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return config('app.env') == 'development' ? view('admin_debug') : view('admin');
    }

    /**
     * Useful to check if the logged in user has management permissions.
     */
    public function ping()
    {
        return response(null, 200);
    }
}
