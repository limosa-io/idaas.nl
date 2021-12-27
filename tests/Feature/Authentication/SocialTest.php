<?php

namespace Tests\Feature\Authentication;

use App\AuthModule;
use App\AuthTypes\Facebook;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helper\OpenIDHelper;

class SocialTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testFacebook()
    {

        AuthModule::firstOrCreate(
            [
                'name' => (new Facebook)->getDefaultName()
            ],
            [
                'type' => (new Facebook)->getIdentifier(),
                'config' => [
                    'client_id' => env('FACEBOOK_CLIENT_ID'),
                    'client_secret' => env('FACEBOOK_CLIENT_SECRET')
                ]
            ]
        );

        OpenIDHelper::initWithNewClient(
            $this,
            [
                'trusted' => true,
                'grant_type' => [
                    'authorization_code',
                    'implicit'
                ],
                'response_types' => [
                    'code',
                    'token',
                    'id_token'
                ]
            ]
        )
            ->expect('facebook')
            ->expectAutoFinish();
    }
}
