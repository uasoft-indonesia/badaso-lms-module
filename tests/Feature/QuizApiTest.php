<?php

namespace Uasoft\Badaso\Module\LMSModule\Tests\Feature;

use Tests\TestCase;
use Uasoft\Badaso\Module\LMSModule\Enums\CourseUserRole;
use Uasoft\Badaso\Module\LMSModule\Models\Course;
use Uasoft\Badaso\Module\LMSModule\Models\Quiz;
use Uasoft\Badaso\Module\LMSModule\Models\Topic;
use Uasoft\Badaso\Module\LMSModule\Models\User;
use Uasoft\Badaso\Module\LMSModule\Tests\Helpers\AuthHelper;

class QuizApiTest extends TestCase
{
    public function testCreateQuizWithoutLoginExpectResponse401()
    {
        $url = route('badaso.quiz.add');
        $response = $this->json('POST', $url);
        $response->assertStatus(401);
    }

    public function testCreateQuizInAnUnerolledCourseExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $url = route('badaso.quiz.add');
        $response = AuthHelper::asUser($this, $user)->json('POST', $url, [
            'course_id' => 1,
            'title' => 'test title',
        ]);

        $response->assertStatus(400);
    }

    public function testCreateQuizAsStudentExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::STUDENT])
            ->create();

        $url = route('badaso.quiz.add');
        $response = AuthHelper::asUser($this, $user)->json('POST', $url, [
            'course_id' => 1,
            'title' => 'test title',
            'link_url' => 'google.com',
        ]);

        $response->assertStatus(400);
    }

    public function testCreateQuizAsTeacherWithNoNameExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $url = route('badaso.quiz.add');
        $response = AuthHelper::asUser($this, $user)->json('POST', $url, [
            'course_id' => $course->id,
        ]);

        $response->assertStatus(400);
    }

    public function testCreateQuizAsTeacherWithValidDataExpectInsertedToDatabase()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $url = route('badaso.quiz.add');
        AuthHelper::asUser($this, $user)->json('POST', $url, [
            'course_id' => $course->id,
            'title' => 'test title',
            'link_url' => 'google.com',
        ]);

        $this->assertEquals(1, Quiz::count());
        $this->assertDatabaseHas(
            app(Quiz::class)->getTable(),
            [
                'course_id' => $course->id,
                'title' => 'test title',
                'link_url' => 'google.com',
                'created_by' => $user->id,
            ]
        );
    }

    public function testCreateQuizAsTeacherWithValidDataExpectReturnCreatedQuiz()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $url = route('badaso.quiz.add');
        $response = AuthHelper::asUser($this, $user)->json('POST', $url, [
            'course_id' => $course->id,
            'title' => 'test title',
            'link_url' => 'google.com',
        ]);

        $quizData = $response->json('data');

        $response->assertStatus(200);
        $this->assertArrayHasKey('id', $quizData);
        $this->assertEquals($quizData['courseId'], $course->id);
        $this->assertEquals($quizData['title'], 'test title');
        $this->assertEquals($quizData['linkUrl'], 'google.com');
        $this->assertEquals($quizData['createdBy'], $user->id);
    }

    public function testReadQuizWithoutLoginExpectResponse401()
    {
        $url = route('badaso.quiz.read', ['id' => 1]);
        $response = $this->json('GET', $url);
        $response->assertStatus(401);
    }

    public function testReadQuizGivenUnenrolledCourseExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $teacher = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($teacher, ['role' => CourseUserRole::TEACHER])
            ->create();

        $quiz = Quiz::factory()
            ->create([
                'course_id' => $course->id,
            ]);

        $url = route('badaso.quiz.read', ['id' => $quiz->id]);
        $response = AuthHelper::asUser($this, $user)->json('GET', $url);

        $response->assertStatus(400);
    }

    public function testReadQuizGivenValidDataExpectRespondsWithQuiz()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::STUDENT])
            ->create();

        $quiz = Quiz::factory()
            ->create([
                'course_id' => $course->id,
            ]);

        $url = route('badaso.quiz.read', ['id' => $quiz->id]);
        $response = AuthHelper::asUser($this, $user)->json('GET', $url);

        $response->assertStatus(200);
        $response->assertJson(['data' =>[
            'id' => $quiz->id,
            'courseId' => $course->id,
            'title' => $quiz->title,
            'linkUrl' => $quiz->link_url,
        ]]);
    }

    public function testEditQuizWithoutLoginExpectResponse401()
    {
        $url = route('badaso.quiz.edit', ['id' => 1]);
        $response = $this->json('PUT', $url);
        $response->assertStatus(401);
    }

    public function testEditQuizGivenUserIsNotCreatorExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $quiz = Quiz::factory()
            ->for($course)
            ->create();

        $url = route('badaso.quiz.edit', ['id' => $quiz->id]);
        $response = AuthHelper::asUser($this, $user)->json('PUT', $url);

        $response->assertStatus(400);
    }

    public function testEditQuizGivenCreatorHasUnenrolledTheCourseExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $quiz = Quiz::factory()->create([
            'created_by' => $user->id,
        ]);

        $url = route('badaso.quiz.edit', ['id' => $quiz->id]);
        $response = AuthHelper::asUser($this, $user)->json('PUT', $url);

        $response->assertStatus(400);
    }

    public function testEditQuizGivenValidDataExpectUpdated()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $topic = Topic::factory()
            ->for($course)
            ->create();

        $quiz = Quiz::factory()
            ->for($course)
            ->for($topic)
            ->create([
                'created_by' => $user->id,
            ]);

        $url = route('badaso.quiz.edit', ['id' => $quiz->id]);
        AuthHelper::asUser($this, $user)->json('PUT', $url, [
            'title' => 'new title',
            'description' => 'new description',
            'link_url' => 'http://new-link-url.com',
        ]);

        $this->assertDatabaseHas(
            app(Quiz::class)->getTable(),
            [
                'id' => $quiz->id,
                'title' => 'new title',
                'description' => 'new description',
                'link_url' => 'http://new-link-url.com',
            ]
        );
    }

    public function testEditQuizGivenValidDataExpectReturnUpdatedQuiz()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $topic = Topic::factory()
            ->for($course)
            ->create();

        $quiz = Quiz::factory()
            ->for($course)
            ->for($topic)
            ->create([
                'created_by' => $user->id,
            ]);

        $url = route('badaso.quiz.edit', ['id' => $quiz->id]);
        $response = AuthHelper::asUser($this, $user)->json('PUT', $url, [
            'title' => 'new title',
            'description' => 'new description',
            'link_url' => 'http://new-link-url.com',
        ]);

        $quizData = $response->json('data');

        $response->assertStatus(200);
        $this->assertArrayHasKey('id', $quizData);
        $this->assertEquals($quizData['title'], 'new title');
        $this->assertEquals($quizData['description'], 'new description');
        $this->assertEquals($quizData['linkUrl'], 'http://new-link-url.com');
    }

    public function testDeleteQuizWithoutLoginExpectResponse401()
    {
        $url = route('badaso.quiz.delete', ['id' => 1]);
        $response = $this->json('DELETE', $url);
        $response->assertStatus(401);
    }

    public function testDeleteQuizGivenUserIsNotCreatorExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $quiz = Quiz::factory()
            ->for($course)
            ->create();

        $url = route('badaso.quiz.delete', ['id' => $quiz->id]);
        $response = AuthHelper::asUser($this, $user)->json('DELETE', $url);

        $response->assertStatus(400);
    }

    public function testDeleteQuizGivenCreatorHasUnenrolledTheCourseExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $quiz = Quiz::factory()->create([
            'created_by' => $user->id,
        ]);

        $url = route('badaso.quiz.delete', ['id' => $quiz->id]);
        $response = AuthHelper::asUser($this, $user)->json('DELETE', $url);

        $response->assertStatus(400);
    }

    public function testDeleteQuizGivenValidDataExpectDeleted()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $quiz = Quiz::factory()
            ->for($course)
            ->create([
                'created_by' => $user->id,
            ]);

        $url = route('badaso.quiz.delete', ['id' => $quiz->id]);
        AuthHelper::asUser($this, $user)->json('DELETE', $url);

        $this->assertDatabaseMissing(
            app(Quiz::class)->getTable(),
            [
                'id' => $quiz->id,
            ]
        );
    }

    public function testDeleteQuizGivenValidDataExpectResponse200()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $quiz = Quiz::factory()
            ->for($course)
            ->create([
                'created_by' => $user->id,
            ]);

        $url = route('badaso.quiz.delete', ['id' => $quiz->id]);
        $response = AuthHelper::asUser($this, $user)->json('DELETE', $url);

        $response->assertStatus(200);
    }
}
