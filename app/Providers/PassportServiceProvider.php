<?php

/**
 * Overrides the default PassportServiceProvider in order to set custom expiration times.
 * 
 * Authorization code expires after 10m
 * Personal access tokens in 1 years
 * 
 * TODO: delete this class
 */

namespace App\Providers;

use App\AuthCode;
use App\Client;
use App\OAuthScope;
use App\RefreshToken;
use Idaas\Passport\Passport as IdaasPassport;

class PassportServiceProvider extends \Idaas\Passport\PassportServiceProvider
{

    public function boot()
    {
        IdaasPassport::useAuthCodeModel(AuthCode::class);
        IdaasPassport::useRefreshTokenModel(RefreshToken::class);

        parent::boot();
    }

    protected function getClientModel()
    {
        return Client::class;
    }
    
    public function makeAuthorizationServer()
    {
        
        $scopes = OAuthScope::all()->pluck('description', 'name')->all();

        IdaasPassport::tokensCan($scopes);

        return parent::makeAuthorizationServer();
    }
}
