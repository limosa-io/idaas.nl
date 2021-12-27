<?php

namespace Tests\Feature\OIDC;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Client;

class OpenIDConnectRegisterTest extends TestCase
{
    use RefreshDatabase;

    public function testBasic()
    {
        $response = $this->get(
            '/oauth/connect/register',
            [
                'Authorization' => sprintf('Bearer %s', $this->getAccessToken())
            ]
        );

        $response->assertStatus(200);
    }

    public function testCreateAndUpdate()
    {
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

        $json = $response->json();

        $this->assertArrayHasKey('name', $json);
        $this->assertArrayHasKey('client_name', $json);
        $this->assertArrayHasKey('secret', $json);
        $this->assertArrayHasKey('redirect_uris', $json);
        $this->assertArrayHasKey('client_id', $json);
        $this->assertArrayHasKey('application_type', $json);

        $response = $this->get(
            '/oauth/connect/register',
            [
                'Authorization' => sprintf('Bearer %s', $this->getAccessToken())
            ]
        );

        $response->assertStatus(200);

        $this->assertCount(3, $response->json());

        $this->assertContains('test', collect($response->json())->pluck('client_name'));

        $first = collect($response->json())->first(
            function ($value, $key) {
                return $value['client_name'] == 'test';
            }
        );
        $first['logo_uri'] = 'https://asdgadgsgads';

        $response = $this->put(
            '/oauth/connect/register/' . $first['client_id'],
            $first,
            [
                'Authorization' => sprintf('Bearer %s', $this->getAccessToken())
            ]
        );

        $response->assertStatus(200);

        $this->assertContains('logo_uri', $response->json());
        $this->assertEquals($first['logo_uri'], $response->json('logo_uri'));
        $this->assertNotNull(Client::find($first['client_id']));

        $response = $this->delete('oauth/connect/register/' . $first['client_id']);

        $response->assertStatus(200);

        $this->assertNull(Client::where('revoked', false)->where('id', $first['client_id'])->first());
    }
}
