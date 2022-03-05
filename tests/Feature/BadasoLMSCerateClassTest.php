<?php

namespace Uasoft\Badaso\Module\LMSModule\Tests\Feature;

use Uasoft\Badaso\Module\LMSModule\Tests\TestCase;
use Uasoft\Badaso\Helpers\CallHelperTest;

class BadasoLMSCreateClassTest extends TestCase
{
    public function testCreateClassWithoutLoginExpectResponseStatus401()
    {
        $url = route('badaso.course.store');
        $response = $this->postJson($url, [
            'name' => 'PPL with Badaso',
            'subject' => 'PPL',
            'room' => '2-2403',
        ]);

        $response->assertStatus(401);
    }

    public function testCreateClassWithAllFieldsValidExpectResponseStatus201()
    {
        // TODO: log user in first
        $url = route('badaso.course.store');
        $response = $this->postJson($url, [
            'name' => 'PPL with Badaso',
            'subject' => 'PPL',
            'room' => '2-2403',
        ]);

        $response->assertStatus(201);
    }
}
