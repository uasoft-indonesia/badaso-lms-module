<?php

namespace Uasoft\Badaso\Module\LMSModule\Tests\Feature;

use Tests\TestCase;
use Uasoft\Badaso\Module\LMSModule\Enums\CourseUserRole;
use Uasoft\Badaso\Module\LMSModule\Models\Course;
use Uasoft\Badaso\Module\LMSModule\Models\Quiz;
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
            'name' => 'test name',
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
            'name' => 'test name',
            'start_time' => '2022-05-24 19:55:00+07:00',
            'end_time' => '2022-05-24 23:55:00+07:00',
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
            'name' => 'test name',
            'link_url' => 'google.com',
        ]);

        $this->assertEquals(1, Quiz::count());
        $this->assertDatabaseHas(
            app(Quiz::class)->getTable(),
            [
                'course_id' => $course->id,
                'name' => 'test name',
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
            'name' => 'test name',
            'link_url' => 'google.com',
        ]);

        $quizData = $response->json('data');

        $response->assertStatus(200);
        $this->assertArrayHasKey('id', $quizData);
        $this->assertEquals($quizData['courseId'], $course->id);
        $this->assertEquals($quizData['name'], 'test name');
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
            'name' => $quiz->name,
            'linkUrl' => $quiz->link_url,
        ]]);
    }
}
