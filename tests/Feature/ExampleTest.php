<?php

namespace Joaovdiasb\LaravelMultiTenancy\Tests\Feature;

use Joaovdiasb\LaravelMultiTenancy\Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
