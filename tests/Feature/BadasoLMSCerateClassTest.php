<?php

namespace Uasoft\Badaso\Module\LMS\Tests\Feature;

use Tests\TestCase;
use Uasoft\Badaso\Helpers\CallHelperTest;

class BadasoLMSCreateClassTest extends TestCase
{
    public function testCreateClassWithoutLoginExpectResponseStatus401()
    {
        $response = $this->postJson('/classes', [
            'name' => 'PPL with Badaso',
            'subject' => 'PPL',
            'room' => '2-2403',
        ]);

        $response->assertStatus(401);
    }

    public function testCreateClassWithAllFieldsValidExpectResponseStatus201()
    {
        // TODO: log user in first
        $response = $this->postJson('/classes', [
            'name' => 'PPL with Badaso',
            'subject' => 'PPL',
            'room' => '2-2403',
        ]);

        $response->assertStatus(201);
    }
}
