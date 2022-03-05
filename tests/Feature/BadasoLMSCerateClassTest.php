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
        $url = route('badaso.course.store');
        
        // TODO: log user in first, then use it in the request
        // Or just use a valid dummy token
        $response = $this->withHeader(
            'Authorization', 'Bearer ' . 'dummy token'
        )->postJson($url, [
            'name' => 'PPL with Badaso',
            'subject' => 'PPL',
            'room' => '2-2403',
        ]);

        $response->assertStatus(201);
    }
}
