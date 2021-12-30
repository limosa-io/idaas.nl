<?php

namespace Tests\Feature\OIDC;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helper\OpenIDHelper;

use function GuzzleHttp\json_encode;

class OpenIDConnectRequestTest extends TestCase
{
    use RefreshDatabase;

    public function testPromptNone()
    {
        $response = $this->get(
            sprintf(
                '%s?response_type=code&prompt=none&client_id=%s&redirect_uri=%s&scope=openid+applications:manage&state=k2m2flx5own&nonce=mpcv24xia6c',
                route('oauth.authorize'),
                urlencode(resolve('App\Tenant')->client_id),
                urlencode(route('ice.manage.completelogin'))
            )
        );

        //TODO: Implement prompt=none
        //$response->assertStatus(302);

        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public static function base64url_decode($data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }


    public function testLoginFlow()
    {

        OpenIDHelper::init(
            $this,
            resolve('App\Tenant')->client_id,
            [
                'redirect_uri' => route('ice.manage.completelogin'),
                'state' => 'k2m2flx5own',
                'nonce' => '12345678910',
                'scope' => 'openid applications:manage'
            ]
        )
            ->expectInternalOpenIDConnect()
            ->expect('password')
            ->expectFinishInternalOpenIDConnect()
            // Auto redirect to oidc callback
            ->expectAutoFinish()
            ->expectCodeToToken();
    }

    public function testInvalidScope()
    {
        OpenIDHelper::init(
            $this,
            resolve('App\Tenant')->client_id,
            [
                'redirect_uri' => route('ice.manage.completelogin'),
                'scope' => 'unknown scopes'
            ]
        )->expectAutoFinish(true, 'invalid_scope');
    }

    public function testClaims()
    {
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
            ],
            [
                'claims' =>   json_encode([
                    "userinfo" =>
                    [
                        "given_name" => ["essential" => true],
                        "nickname" => null,
                    ],
                    "id_token" =>
                    [
                        "auth_time" => ["essential" => true],
                        "acr" => ["values" => ["urn:mace:incommon:iap:silver"]]
                    ]
                ])
            ]
        )->expect('passwordless')
        ->expectAutoFinish()
        ->expectCodeToToken();
    }
}
