<?php

namespace Tests\Helper;

use App\AuthModule;
use Tests\TestCase;

class ChainHelper
{
    protected $testCase;

    public static function create(TestCase $testCase, $module)
    {

        $response = $testCase->post(
            'https://master.manage.test.dev/authchain/v2/manage/modules', [
                'type' => $module,
                'enabled' => false,
                'skippable' => true,
                'hide_if_not_requested' => false,
            ], [
                'Authorization' => sprintf('Bearer %s', $testCase->getAccessToken()),
            ]
        );

        $result = AuthModule::find($response->json('id'));
        $testCase->assertNotNull($result);

        return $result;

    }

    public static function link(TestCase $testCase, $fromId, $toId)
    {

        $response = $testCase->post(
            'https://master.manage.test.dev/authchain/v2/manage/chain', [
                'from' => $fromId,
                'to' => $toId,
            ], [
                'Authorization' => sprintf('Bearer %s', $testCase->getAccessToken()),
            ]
        );

        $response->assertStatus(201);

    }
}
