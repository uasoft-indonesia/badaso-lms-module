<?php

namespace Uasoft\Badaso\Module\LMSModule\Tests\Feature;

use Tests\TestCase;
use Uasoft\Badaso\Module\LMSModule\Enums\CourseUserRole;
use Uasoft\Badaso\Module\LMSModule\Models\Course;
use Uasoft\Badaso\Module\LMSModule\Models\User;
use Uasoft\Badaso\Module\LMSModule\Tests\Helpers\AuthHelper;

class AssignmentApiTest extends TestCase
{
    public function testCreateAssignmentWithoutLoginExpectResponse401()
    {
        $url = route('badaso.assignment.add');
        $response = $this->json('POST', $url);
        $response->assertStatus(401);
    }

    public function testCreateAssignmentInAnUnerolledCourseExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $url = route('badaso.assignment.add');
        $response = AuthHelper::asUser($this, $user)->json('POST', $url, [
            'course_id' => 1,
            'title' => 'test title',
            'due_date' => '2022-05-24T23:55:00+07:00',
        ]);

        $response->assertStatus(400);
    }

    public function testCreateAssignmentAsStudentExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::STUDENT])
            ->create();

        $url = route('badaso.assignment.add');
        $response = AuthHelper::asUser($this, $user)->json('POST', $url, [
            'course_id' => $course->id,
            'title' => 'test title',
            'due_date' => '2022-05-24T23:55:00+07:00',
        ]);

        $response->assertStatus(400);
    }

    public function testCreateAssignmentAsTeacherWithNoTitleExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $url = route('badaso.assignment.add');
        $response = AuthHelper::asUser($this, $user)->json('POST', $url, [
            'course_id' => $course->id,
            'due_date' => '2022-05-24T23:55:00+07:00',
        ]);

        $response->assertStatus(400);
    }
}
