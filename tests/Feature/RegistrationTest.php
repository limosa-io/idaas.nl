<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\AuthModule;
use App\AuthChain;
use Tests\Helper\ChainHelper;
use Tests\Helper\OpenIDHelper;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test registration using the SCIM Me endpoint
     *
     * @return void
     */
    public function testBasicTest()
    {

        $response = $this->get('https://master.manage.test.dev/api/settings/bulk?namespace=registration');
        $response->assertStatus(401);

        $response = $this->get(
            'https://master.manage.test.dev/api/settings/bulk?namespace=registration', [
            'Authorization' => sprintf('Bearer %s', $this->getAccessToken())
            ]
        );
        $response->assertStatus(200);

        $response = $this->post(
            'https://master.manage.test.dev/api/scim/v2/Me',
            [
                "schemas" => ["urn:ietf:params:scim:schemas:core:2.0:User"],
                "urn:ietf:params:scim:schemas:core:2.0:User" =>
                [
                    "emails" => ["value" => "whatever@test.nl"]
                ]
            ]
        );

        $response->assertStatus(403);

        $response = $this->put(
            'https://master.manage.test.dev/api/settings/bulk?namespace=registration',
            [
                "allow" => true,
                "allow_active" => true,
                "level_active" => "activation",
                "attributes_create" => ["emails"],
                "attributes_update" => []
            ],
            [
                'Authorization' => sprintf('Bearer %s', $this->getAccessToken())
            ]
        );

        $response->assertStatus(200);

        $response = $this->post(
            'https://master.manage.test.dev/api/scim/v2/Me',
            [
                "schemas" => ["urn:ietf:params:scim:schemas:core:2.0:User"],
                "urn:ietf:params:scim:schemas:core:2.0:User" =>
                [
                    "emails" => ["value" => "whatever@test.nl"]
                ]
            ]
        );

        $response->assertStatus(201);
    }

    public function testRegistrationFlow()
    {
        // Enable registration
        $response = $this->put(
            'https://master.manage.test.dev/api/settings/bulk?namespace=registration',
            [
                "allow" => true,
                "allow_active" => true,
                "level_active" => "activation",
                "attributes_create" => ["emails"],
                "attributes_update" => []
            ],
            [
                'Authorization' => sprintf('Bearer %s', $this->getAccessToken())
            ]
        );

        // (1) Create registration module
        $authModule = ChainHelper::create($this, 'register');
        ChainHelper::link($this, AuthModule::where('type', 'start')->first()->id, $authModule->id);
        
        OpenIDHelper::initWithNewClient(
            $this, [
            'trusted' => true
            ]
        )->expect('registration')->expectFinish()->expectCodeToToken();

    }
}
