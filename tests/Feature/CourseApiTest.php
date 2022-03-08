<?php

namespace Uasoft\Badaso\Module\LMSModule\Tests\Feature;

use Tests\TestCase;
use Uasoft\Badaso\Module\LMSModule\Models\Course;
use Uasoft\Badaso\Module\LMSModule\Models\User;

class BadasoCourseApiTest extends TestCase
{
    public function testCreateCourseWithoutLoginExpectResponse401()
    {
        $url = route('badaso.course.add');
        $response = $this->json('POST', $url);
        $response->assertStatus(401);
    }

    public function testCreateCourseAsLoggedInUserWithValidDataExpectResponse200()
    {
        $loginUrl = '/admin/v1/auth/login';
        $user = User::factory()->create();

        $loginResponse = $this->json('POST', $loginUrl, [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $token = $loginResponse->json('accessToken');

        $url = route('badaso.course.add');
        $response = $this->json('POST', $url, [
            'name' => 'Test course',
            'subject' => 'Test subject',
            'room' => 'Test room',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $response->assertStatus(200);
    }

    public function testCreateCourseAsLoggedInUserWithValidDataExpectResponseCreatedCourseWithId()
    {
        $loginUrl = '/admin/v1/auth/login';
        $user = User::factory()->create();

        $loginResponse = $this->json('POST', $loginUrl, [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $token = $loginResponse->json('accessToken');

        $url = route('badaso.course.add');
        $response = $this->json('POST', $url, [
            'name' => 'Test course',
            'subject' => 'Test subject',
            'room' => 'Test room',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $response->assertStatus(200);

        $courseData = $response->json('data');
        $this->assertArrayHasKey('id', $courseData);
        $this->assertNotNull($courseData['id']);
        $this->assertEquals('Test course', $courseData['name']);
        $this->assertEquals('Test subject', $courseData['subject']);
        $this->assertEquals('Test room', $courseData['room']);
    }
}
