<?php

namespace Uasoft\Badaso\Module\LMSModule\Tests\Feature;

use Tests\TestCase;
use Uasoft\Badaso\Module\LMSModule\Enums\CourseUserRole;
use Uasoft\Badaso\Module\LMSModule\Models\Assignment;
use Uasoft\Badaso\Module\LMSModule\Models\Course;
use Uasoft\Badaso\Module\LMSModule\Models\Topic;
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
            'due_date' => '2022-05-24T23:55:00+07:00',
        ]);

        $this->assertEquals(1, Assignment::count());
        $this->assertDatabaseHas(
            app(Assignment::class)->getTable(),
            [
                'course_id' => $course->id,
                'title' => 'test title',
                'due_date' => '2022-05-24 16:55:00',
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
            'due_date' => '2022-05-24T23:55:00+07:00',
        ]);

        $assignmentData = $response->json('data');

        $response->assertStatus(200);
        $this->assertArrayHasKey('id', $assignmentData);
        $this->assertEquals($assignmentData['courseId'], $course->id);
        $this->assertEquals($assignmentData['title'], 'test title');
        $this->assertEquals($assignmentData['createdBy'], $user->id);
        $this->assertEquals(
            new \DateTime($assignmentData['dueDate']),
            new \DateTime('2022-05-24T23:55:00+07:00'),
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

    public function testReadAssignmentGivenValidDataExpectRespondsWithAssignment()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::STUDENT])
            ->create();

        $topic = Topic::factory()
            ->for($course)
            ->create();

        $assignment = Assignment::factory()
            ->for($course)
            ->for($topic)
            ->create();

        $url = route('badaso.assignment.read', ['id' => $assignment->id]);
        $response = AuthHelper::asUser($this, $user)->json('GET', $url);

        $assignmentData = $response->json('data');

        $response->assertStatus(200);
        $this->assertArrayHasKey('id', $assignmentData);
        $this->assertEquals($assignmentData['courseId'], $course->id);
        $this->assertEquals($assignmentData['title'], $assignment->title);
        $this->assertEquals($assignmentData['createdBy']['name'], $assignment->createdBy->name);
        $this->assertEquals($assignmentData['topic']['title'], $assignment->topic->title);
    }

    public function testReadAssignmentGivenValidDataExpectRespondsWithCorrectDueDate()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::STUDENT])
            ->create();

        $topic = Topic::factory()
            ->for($course)
            ->create();

        $assignment = Assignment::factory()
            ->for($course)
            ->for($topic)
            ->create([
                'due_date' => '2022-05-24T16:55:00Z',
            ]);

        $url = route('badaso.assignment.read', ['id' => $assignment->id]);
        $response = AuthHelper::asUser($this, $user)->json('GET', $url);

        $assignmentData = $response->json('data');

        $response->assertStatus(200);
        $this->assertEquals(
            new \DateTime($assignmentData['dueDate']),
            new \DateTime('2022-05-24T23:55:00+07:00'),
        );
    }

    public function testEditAssignmentWithoutLoginExpectResponse401()
    {
        $url = route('badaso.assignment.edit', ['id' => 1]);
        $response = $this->json('PUT', $url);
        $response->assertStatus(401);
    }

    public function testEditAssignmentGivenUserIsNotCreatorExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $assignment = Assignment::factory()
            ->for($course)
            ->create();

        $url = route('badaso.assignment.edit', ['id' => $assignment->id]);
        $response = AuthHelper::asUser($this, $user)->json('PUT', $url);

        $response->assertStatus(400);
    }

    public function testEditAssignmentGivenCreatorHasUnenrolledTheCourseExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $assignment = Assignment::factory()->create([
            'created_by' => $user->id,
        ]);

        $url = route('badaso.assignment.edit', ['id' => $assignment->id]);
        $response = AuthHelper::asUser($this, $user)->json('PUT', $url);

        $response->assertStatus(400);
    }

    public function testEditAssignmentGivenValidDataExpectUpdated()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $topic = Topic::factory()
            ->for($course)
            ->create();

        $assignment = Assignment::factory()
            ->for($course)
            ->for($topic)
            ->create([
                'created_by' => $user->id,
            ]);

        $url = route('badaso.assignment.edit', ['id' => $assignment->id]);
        AuthHelper::asUser($this, $user)->json('PUT', $url, [
            'title' => 'new title',
            'topic_id' => null,
            'due_date' => '2022-05-24T23:55:00+07:00',
            'description' => 'new description',
            'point' => 100,
            'file_url' => 'http://new-file-url.com',
            'link_url' => 'http://new-link-url.com',
        ]);

        $this->assertDatabaseHas(
            app(Assignment::class)->getTable(),
            [
                'id' => $assignment->id,
                'topic_id' => null,
                'due_date' => '2022-05-24 16:55:00',
                'description' => 'new description',
                'point' => 100,
                'file_url' => 'http://new-file-url.com',
                'link_url' => 'http://new-link-url.com',
            ]
        );
    }

    public function testEditAssignmentGivenValidDataExpectReturnUpdatedData()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $topic = Topic::factory()
            ->for($course)
            ->create();

        $assignment = Assignment::factory()
            ->for($course)
            ->for($topic)
            ->create([
                'created_by' => $user->id,
            ]);

        $url = route('badaso.assignment.edit', ['id' => $assignment->id]);
        $response = AuthHelper::asUser($this, $user)->json('PUT', $url, [
            'title' => 'new title',
            'topic_id' => null,
            'due_date' => '2022-05-24T23:55:00+07:00',
            'description' => 'new description',
            'point' => 100,
            'file_url' => 'http://new-file-url.com',
            'link_url' => 'http://new-link-url.com',
        ]);

        $assignmentData = $response->json('data');

        $response->assertStatus(200);
        $this->assertArrayHasKey('id', $assignmentData);
        $this->assertEquals($assignmentData['topicId'], null);
        $this->assertEquals($assignmentData['title'], 'new title');
        $this->assertEquals($assignmentData['point'], 100);
        $this->assertEquals($assignmentData['fileUrl'], 'http://new-file-url.com');
        $this->assertEquals($assignmentData['linkUrl'], 'http://new-link-url.com');
        $this->assertEquals(
            new \DateTime($assignmentData['dueDate']),
            new \DateTime('2022-05-24T23:55:00+07:00'),
        );
    }

    public function testDeleteAssignmentWithoutLoginExpectResponse401()
    {
        $url = route('badaso.assignment.delete', ['id' => 1]);
        $response = $this->json('DELETE', $url);
        $response->assertStatus(401);
    }

    public function testDeleteAssignmentGivenUnknownAssignmentExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $url = route('badaso.assignment.delete', ['id' => 17]);
        $response = AuthHelper::asUser($this, $user)->json('DELETE', $url);

        $response->assertStatus(400);
    }

    public function testDeleteAssignmentGivenUserIsNotCreatorExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $assignment = Assignment::factory()
            ->for($course)
            ->create();

        $url = route('badaso.assignment.delete', ['id' => $assignment->id]);
        $response = AuthHelper::asUser($this, $user)->json('DELETE', $url);

        $response->assertStatus(400);
    }

    public function testDeleteAssignmentGivenCreatorHasUnenrolledTheCourseExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $assignment = Assignment::factory()->create([
            'created_by' => $user->id,
        ]);

        $url = route('badaso.assignment.delete', ['id' => $assignment->id]);
        $response = AuthHelper::asUser($this, $user)->json('DELETE', $url);

        $response->assertStatus(400);
    }

    public function testDeleteAssignmentGivenValidDataExpectDeleted()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $assignment = Assignment::factory()
            ->for($course)
            ->create([
                'created_by' => $user->id,
            ]);

        $url = route('badaso.assignment.delete', ['id' => $assignment->id]);
        AuthHelper::asUser($this, $user)->json('DELETE', $url);

        $this->assertDatabaseMissing(
            app(Assignment::class)->getTable(),
            [
                'id' => $assignment->id,
            ]
        );
    }

    public function testDeleteAssignmentGivenValidDataExpectResponse200()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $assignment = Assignment::factory()
            ->for($course)
            ->create([
                'created_by' => $user->id,
            ]);

        $url = route('badaso.assignment.delete', ['id' => $assignment->id]);
        $response = AuthHelper::asUser($this, $user)->json('DELETE', $url);

        $response->assertStatus(200);
    }
}
