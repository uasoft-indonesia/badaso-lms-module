<?php

namespace Uasoft\Badaso\Module\LMSModule\Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBehaviorWithConditionExpectResult()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
