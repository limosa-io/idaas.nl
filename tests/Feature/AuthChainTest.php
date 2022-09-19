<?php

namespace Tests\Feature;

use App\AuthChain as AppAuthChain;
use App\AuthChain\AuthChain;
use App\AuthChain\Object\Eloquent\State;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthChainTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExampleTest()
    {

        // $authchain = new AuthChain(request(), new \App\AuthChain\State());

        // $this->assertArrayHasKey(
        //     'test',
        //     []
        // );
    }
}
