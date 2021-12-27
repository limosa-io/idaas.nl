<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;

class SCIMTest extends TestCase
{
    use RefreshDatabase;

    public function testBasic()
    {
        $response = $this->get(
            'https://master.manage.test.dev/api/scim/v2/Users?count=20&startIndex=0&sortBy=id', [
            'Authorization' => sprintf('Bearer %s', $this->getAccessToken())
            ]
        );
        
        $response->assertStatus(200);
        
        $json = $response->json();

        $this->assertArrayHasKey('totalResults', $json);
        $this->assertArrayHasKey('itemsPerPage', $json);
        $this->assertArrayHasKey('startIndex', $json);
        $this->assertArrayHasKey('Resources', $json);
        $this->assertArrayHasKey('schemas', $json);

    }

    public function testCreate()
    {

        $response = $this->post(
            'https://master.manage.test.dev/api/scim/v2/Users', 
            [
            "schemas" => [
                "urn:ietf:params:scim:schemas:core:2.0:User"
            ],
            "urn:ietf:params:scim:schemas:core:2.0:User" => [
                "userName" => "johndoe",
                "emails" => [
                    [
                        "value" => "johndoe@example.com"
                    ]
                ]
            ]
            ],
            [
            'Authorization' => sprintf('Bearer %s', $this->getAccessToken())
            ]
        );

        $response->assertStatus(201);

        $this->assertNotNull(User::where('email', 'johndoe@example.com')->first());
        
    }

    public function testIsNull()
    {
        $this->assertNull(User::where('email', 'johndoe@example.com')->first());
    }

}