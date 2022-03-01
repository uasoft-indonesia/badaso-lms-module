<?php

namespace Uasoft\Badaso\Module\LMS\Tests\Feature;

use Tests\TestCase;
use Uasoft\Badaso\Helpers\CallHelperTest;

class BadasoLMSCreateClassTest extends TestCase
{
    public function testStartInit()
    {
        // init user login as normal user
        CallHelperTest::handleUserAdminAuthorize($this);
    }

    public function testCreateClassWithAllFieldsValidExpectResponseStatus201()
    {
        $response = $this->postJson('/classes', [
            'name' => 'PPL with Badaso',
            'subject' => 'PPL',
            'room' => '2-2403',
        ]);

        $response->assertStatus(201);
    }
}
