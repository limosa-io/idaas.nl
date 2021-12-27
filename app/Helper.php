<?php

namespace App;

use Illuminate\Support\Facades\Route;
use Laravel\Passport\HasApiTokens;

class Helper
{

    use HasApiTokens;

    //TODO: Introduce something like hasRole('admin'), to protect admin apis
    public static function getRoutes()
    {
        $routeCollection = Route::getRoutes();

        $urls = [

        ];

        foreach ($routeCollection as $value) {
            /**
 * @var Illuminate\Routing\Route $value 
*/
            if($value->getName()) {
                $urls[$value->getName()] = url($value->uri()); 
                
            }
            
        }

        return json_encode($urls);
    }

}