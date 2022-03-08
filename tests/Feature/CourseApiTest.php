<?php

namespace Uasoft\Badaso\Module\LMSModule\Tests\Feature;

use Tests\TestCase;
use Uasoft\Badaso\Module\LMSModule\Enums\CourseUserRole;
use Uasoft\Badaso\Module\LMSModule\Models\Course;
use Uasoft\Badaso\Module\LMSModule\Models\CourseUser;
use Uasoft\Badaso\Module\LMSModule\Models\User;
use Uasoft\Badaso\Module\LMSModule\Tests\Helpers\AuthHelper;

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
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $url = route('badaso.course.add');

        $response = AuthHelper::asUser($this, $user)->json('POST', $url, [
            'name' => 'Test course',
            'subject' => 'Test subject',
            'room' => 'Test room',
        ]);
        $response->assertStatus(200);
    }

    public function testCreateCourseAsLoggedInUserWithValidDataExpectResponseCreatedCourseWithId()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $url = route('badaso.course.add');
        $response = AuthHelper::asUser($this, $user)->json('POST', $url, [
            'name' => 'Test course',
            'subject' => 'Test subject',
            'room' => 'Test room',
        ]);

        $courseData = $response->json('data');
        $this->assertArrayHasKey('id', $courseData);
        $this->assertNotNull($courseData['id']);
        $this->assertEquals('Test course', $courseData['name']);
        $this->assertEquals('Test subject', $courseData['subject']);
        $this->assertEquals('Test room', $courseData['room']);
    }

    public function testCreateCourseAsLoggedInUserWithValidDataExpectCourseCreated()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $courseCountBefore = Course::count();

        $url = route('badaso.course.add');
        AuthHelper::asUser($this, $user)->json('POST', $url, [
            'name' => 'Test course',
            'subject' => 'Test subject',
            'room' => 'Test room',
        ]);

        $courseCountAfter = Course::count();
        $course = Course::first();

        $this->assertEquals(0, $courseCountBefore);
        $this->assertEquals(1, $courseCountAfter);
        $this->assertEquals('Test course', $course->name);
        $this->assertEquals('Test subject', $course->subject);
        $this->assertEquals('Test room', $course->room);
    }

    public function testCreateCourseAsLoggedInUsertWithValidDataExpectUserHasTheRoleTeacherForTheCreatedCourse()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $url = route('badaso.course.add');
        AuthHelper::asUser($this, $user)->json('POST', $url, [
            'name' => 'Test course',
            'subject' => 'Test subject',
            'room' => 'Test room',
        ]);

        $user->fresh();
        $this->assertEquals(1, CourseUser::count());
        $this->assertEquals(1, $user->courses->count());
        $this->assertEquals(CourseUserRole::TEACHER, $user->courses->first()->pivot->role);
    }

    public function testCreateCourseAsLoggedInUserWithoutEitherNameSubjectOrRoomExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $url = route('badaso.course.add');
        $response1 = AuthHelper::asUser($this, $user)->json('POST', $url, [
            'subject' => 'Test subject',
            'room' => 'Test room',
        ]);
        $response2 = AuthHelper::asUser($this, $user)->json('POST', $url, [
            'name' => 'Test course',
            'room' => 'Test room',
        ]);
        $response3 = AuthHelper::asUser($this, $user)->json('POST', $url, [
            'name' => 'Test course',
            'subject' => 'Test subject',
        ]);

        $response1->assertStatus(400);
        $response2->assertStatus(400);
        $response3->assertStatus(400);
    }

    public function testCreateCourseAsLoggedInUserWithoutEitherNameSubjectOrRoomExpectNoCourseAndCourseUserCreated()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $url = route('badaso.course.add');
        AuthHelper::asUser($this, $user)->json('POST', $url, [
            'subject' => 'Test subject',
            'room' => 'Test room',
        ]);
        AuthHelper::asUser($this, $user)->json('POST', $url, [
            'name' => 'Test course',
            'room' => 'Test room',
        ]);
        AuthHelper::asUser($this, $user)->json('POST', $url, [
            'name' => 'Test course',
            'subject' => 'Test subject',
        ]);

        $this->assertEquals(0, Course::count());
        $this->assertEquals(0, CourseUser::count());
    }
}
