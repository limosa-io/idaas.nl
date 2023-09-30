<?php

namespace Tests\Feature\OIDC;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpenIDBasicTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $response = $this->get('/.well-known/jwks.json');
        $response->assertStatus(200);
    }

    public function testOpenIDConfiguration()
    {
        $response = $this->get('/.well-known/openid-configuration');
        $response->assertStatus(200);

        $json = $response->json();

        $this->assertArrayHasKey('authorization_endpoint', $json);
        $this->assertArrayHasKey('token_endpoint', $json);
        $this->assertArrayHasKey('userinfo_endpoint', $json);
        $this->assertArrayHasKey('jwks_uri', $json);
        $this->assertArrayHasKey('issuer', $json);
        $this->assertArrayHasKey('end_session_endpoint', $json);
        $this->assertArrayHasKey('code_challenge_methods_supported', $json);
        $this->assertArrayHasKey('introspection_endpoint', $json);
        $this->assertArrayHasKey('introspection_endpoint_auth_methods_supported', $json);
        $this->assertArrayHasKey('token_endpoint_auth_methods_supported', $json);
        $this->assertArrayHasKey('revocation_endpoint', $json);
        $this->assertArrayHasKey('service_documentation', $json);
        $this->assertArrayHasKey('ui_locales_supported', $json);
    }
}
