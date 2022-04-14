<?php

namespace Uasoft\Badaso\Module\LMSModule\Tests\Feature;

use Tests\TestCase;
use Uasoft\Badaso\Module\LMSModule\Models\User;
use Uasoft\Badaso\Module\LMSModule\Tests\Helpers\AuthHelper;

class LessonMaterialApiTest extends TestCase
{
    public function testCreateLessonMaterialWithoutLoginExpectResponse401()
    {
        $url = route('badaso.lesson_material.add');
        $response = $this->json('POST', $url);
        $response->assertStatus(401);
    }

    public function testCreateLessonMaterialInAnUnerolledCourseExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $url = route('badaso.lesson_material.add');
        $response = AuthHelper::asUser($this, $user)->json('POST', $url, [
            'course_id' => 1,
            'title' => 'test title',
        ]);

        $response->assertStatus(400);
    }
}
