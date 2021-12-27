<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\AuthModule;
use App\AuthChain;
use Tests\Helper\OpenIDHelper;

class ConsentTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
     
        $redirect_uri = 'https://what.ever.com';
        // Create Client
        $response = $this->post(
            '/oauth/connect/register',
            [
                "client_name" => "test",
                "application_type" => "web",
                'redirect_uris' => [
                    'https://what.ever.com'
                ]
            ],
            [
                'Authorization' => sprintf('Bearer %s', $this->getAccessToken())
            ]
        );

        $response->assertStatus(201);

        $clientId = $response->json('client_id');

        OpenIDHelper::init($this, $clientId, ['redirect_uri' => 'https://what.ever.com' ])
            ->expect('password')
            ->expect('consent')
            ->expectFinish();

        OpenIDHelper::init($this, $clientId, ['redirect_uri' => 'https://what.ever.com' ])
            ->expect('password')
            ->expectFinishWithError();

    }
    
}
