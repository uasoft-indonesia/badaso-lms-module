<?php

namespace Uasoft\Badaso\Module\LMSModule\Tests\Feature;

use Tests\TestCase;

class LessonMaterialApiTest extends TestCase
{
    public function testCreateLessonMaterialWithoutLoginExpectResponse401()
    {
        $url = route('badaso.lesson_material.add');
        $response = $this->json('POST', $url);
        $response->assertStatus(401);
    }
}
