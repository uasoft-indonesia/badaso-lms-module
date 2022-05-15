<?php

namespace Uasoft\Badaso\Module\LMSModule\Tests\Feature;

use Tests\TestCase;
use Uasoft\Badaso\Module\LMSModule\Enums\CourseUserRole;
use Uasoft\Badaso\Module\LMSModule\Models\Assignment;
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
            'due_date' => '2022-05-24 23:55:00+07:00',
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
            'due_date' => '2022-05-24 23:55:00+07:00',
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
            'due_date' => '2022-05-24 23:55:00+07:00',
        ]);

        $response->assertStatus(400);
    }

    public function testCreateAssignmentAsTeacherWithNoDueDateExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $url = route('badaso.assignment.add');
        $response = AuthHelper::asUser($this, $user)->json('POST', $url, [
            'course_id' => $course->id,
            'title' => 'test title',
        ]);

        $response->assertStatus(400);
    }

    public function testCreateAssignmentAsTeacherWithValidDataExpectInsertedToDatabase()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $url = route('badaso.assignment.add');
        AuthHelper::asUser($this, $user)->json('POST', $url, [
            'course_id' => $course->id,
            'title' => 'test title',
            'due_date' => '2022-05-24 23:55:00+07:00',
        ]);

        $this->assertEquals(1, Assignment::count());
        $this->assertDatabaseHas(
            app(Assignment::class)->getTable(),
            [
                'course_id' => $course->id,
                'title' => 'test title',
                'due_date' => '2022-05-24 23:55:00+07:00',
                'created_by' => $user->id,
            ]
        );
    }

    public function testCreateAssignmentAsTeacherWithValidDataExpectReturnCreatedAssignment()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $url = route('badaso.assignment.add');
        $response = AuthHelper::asUser($this, $user)->json('POST', $url, [
            'course_id' => $course->id,
            'title' => 'test title',
            'due_date' => '2022-05-24 23:55:00+07:00',
        ]);

        $assignmentData = $response->json('data');

        $response->assertStatus(200);
        $this->assertArrayHasKey('id', $assignmentData);
        $this->assertEquals($assignmentData['courseId'], $course->id);
        $this->assertEquals($assignmentData['title'], 'test title');
        $this->assertEquals($assignmentData['createdBy'], $user->id);
        $this->assertEquals(
            new \DateTime($assignmentData['dueDate']),
            new \DateTime('2022-05-24 23:55:00+07:00'),
        );
    }

    public function testReadAssignmentWithoutLoginExpectResponse401()
    {
        $url = route('badaso.assignment.read', ['id' => 1]);
        $response = $this->json('GET', $url);
        $response->assertStatus(401);
    }

    public function testReadAssignmentGivenUnenrolledCourseExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $assignment = Assignment::factory()->create();

        $url = route('badaso.assignment.read', ['id' => $assignment->id]);
        $response = AuthHelper::asUser($this, $user)->json('GET', $url);

        $response->assertStatus(400);
    }
}
