<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helper\OpenIDHelper;
use Tests\TestCase;

class PasswordlessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {

        OpenIDHelper::initWithNewClient(
            $this, [
                'trusted' => true,
                'grant_type' => [
                    'authorization_code',
                    'implicit',
                ],
                'response_types' => [
                    'code',
                    'token',
                    'id_token',
                ],
            ]
        )->expect('passwordless')->expectAutoFinish();

    }

    public function testWithConsent()
    {
        OpenIDHelper::initWithNewClient(
            $this, [
                'trusted' => false,
            ]
        )->expect('passwordless')->expect('consent')->expectFinish()->expectCodeToToken();
    }

    public function testCodeIdToken()
    {
        OpenIDHelper::initWithNewClient(
            $this, [
                'trusted' => false,
                'grant_types' => [
                    'authorization_code',
                    'implicit',
                ],
                'response_types' => [
                    'code',
                    'token',
                    'id_token',
                ],
            ], [
                'response_type' => 'token',
            ]
        )->expect('passwordless')->expect('consent')->expectFinish()->expectToken();
    }

    public function testWithoutOpenID()
    {
        OpenIDHelper::initWithNewClient(
            $this, [
                'trusted' => false,
                'scope' => 'roles',
            ]
        )->expect('passwordless')->expect('consent')->expectFinish()->expectCodeToToken();
    }
}
