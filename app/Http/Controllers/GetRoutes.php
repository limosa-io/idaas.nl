<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

class GetRoutes extends Controller
{
    public function index()
    {
        $result = [];
        foreach (Route::getRoutes() as $route) {
            /* @var $route Illuminate\Routing\Route */

            $result[] = [
                'uri' => $route->uri,
                'methods' => $route->methods,
                'domain' => $route->action['domain'] ?? null,
            ];
        }

        return $result;
    }
}
