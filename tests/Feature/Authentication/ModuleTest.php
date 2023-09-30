<?php

namespace Tests\Feature\Authentication;

use App\AuthModule;
use App\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ModuleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {

        Passport::useClientModel(Client::class);
        $response = $this->get('https://master.manage.test.dev/authchain/v2/manage/modules');
        $response->assertStatus(401);

        $response = $this->get(
            'https://master.manage.test.dev/authchain/v2/manage/modules',
            [
                'Authorization' => sprintf('Bearer %s', $this->getAccessToken()),
            ]
        );

        $response->assertStatus(200);
        $json = $response->json();

        $this->assertTrue(is_array($json));
    }

    public function testCreateAndUpdate()
    {
        $response = $this->post(
            'https://master.manage.test.dev/authchain/v2/manage/modules',
            [
                'type' => 'register',
                'enabled' => false,
                'skippable' => true,
                'hide_if_not_requested' => false,
            ],
            [
                'Authorization' => sprintf('Bearer %s', $this->getAccessToken()),
            ]
        );

        $response->assertStatus(200);

        $json = $response->json();
        $this->assertArrayHasKey('remember_lifetime', $json);
        $this->assertEquals(3600, $json['remember_lifetime']);
        $this->assertArrayHasKey('hide_if_not_requested', $json);

        return $json;

        $json['remember_lifetime'] = 4000;

        $response = $this->put(
            sprintf('https://master.manage.test.dev/authchain/v2/manage/modules/%s', $json['id']),
            $json,
            [
                'Authorization' => sprintf('Bearer %s', $this->getAccessToken()),
            ]
        );

        $response->assertStatus(200);

        $module = AuthModule::find($json['id']);

        $this->assertNotNull($module);
        $this->assertEquals(4000, $module->remember_lifetime);

        return $json['id'];
    }

    public function testCreateAndChain()
    {
        $response = $this->post(
            'https://master.manage.test.dev/authchain/v2/manage/modules',
            [
                'type' => 'register',
                'enabled' => false,
                'skippable' => true,
                'hide_if_not_requested' => false,
            ],
            [
                'Authorization' => sprintf('Bearer %s', $this->getAccessToken()),
            ]
        );

        $response->assertStatus(200);

        $start = AuthModule::where('type', 'start')->first();

        $this->assertNotNull($start);

        $response = $this->post(
            'https://master.manage.test.dev/authchain/v2/manage/chain',
            [
                'from' => $start->id,
                'to' => $response->json('id'),
            ],
            [
                'Authorization' => sprintf('Bearer %s', $this->getAccessToken()),
            ]
        );

        $response->assertStatus(201);
    }
}
