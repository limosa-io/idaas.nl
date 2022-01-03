<?php

namespace Tests\Feature\Import;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportTest extends TestCase
{
    use RefreshDatabase;

    public function testBasicTest()
    {
        // TODO: deze test faalt met GitHub 
        
        var_dump(file_get_contents(__DIR__ . '/import.yml'));
        var_dump(sprintf('Bearer %s', $this->getAccessToken()));

        var_dump("continue");

        $response = $this->post(
            'https://master.manage.test.dev/api/import', [
            'yaml' => file_get_contents(__DIR__ . '/import.yml')
            ], [
            'Authorization' => sprintf('Bearer %s', $this->getAccessToken())
            ]
        );

        $response->assertStatus(200);
    }

    // public function testExportTest()
    // {
    //     $response = $this->get('https://master.manage.test.dev/api/export', [
    //         'Authorization' => sprintf('Bearer %s', $this->getAccessToken())
    //     ]);
        
    //     $response->assertStatus(200);
    // }

}