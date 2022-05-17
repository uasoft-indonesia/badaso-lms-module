<?php

namespace Uasoft\Badaso\Module\LMS\Tests\Feature;

use Tests\TestCase;
use Uasoft\Badaso\Module\LMSModule\Enums\CourseUserRole;
use Uasoft\Badaso\Module\LMSModule\Models\Assignment;
use Uasoft\Badaso\Module\LMSModule\Models\Course;
use Uasoft\Badaso\Module\LMSModule\Models\LessonMaterial;
use Uasoft\Badaso\Module\LMSModule\Models\Topic;
use Uasoft\Badaso\Module\LMSModule\Models\User;
use Uasoft\Badaso\Module\LMSModule\Tests\Helpers\AuthHelper;

class TopicApiTest extends TestCase
{
    public function testCreateTopicWithoutLogin()
    {
        $url = route('badaso.topic.add');
        $response = $this->json('POST', $url);
        $response->assertStatus(401);
    }

    public function testCreateTopicInAnUnerolledCourse()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $url = route('badaso.topic.add');
        $response = AuthHelper::asUser($this, $user)->json('POST', $url, [
            'course_id' => 1,
            'title' => 'Topic 1',
        ]);

        $response->assertStatus(400);
    }

    public function testCreateTopicInEnrolledCourseWithNoContent()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $url = route('badaso.topic.add');
        $response = AuthHelper::asUser($this, $user)->json('POST', $url, [
            'course_id' => $course->id,
        ]);

        $response->assertStatus(400);
    }

    public function testCreateTopicInEnrolledCourseWithValidDataExpectInsertedToDatabase()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $url = route('badaso.topic.add');
        AuthHelper::asUser($this, $user)->json('POST', $url, [
            'course_id' => $course->id,
            'title' => 'Topic 1',
        ]);

        $this->assertEquals(1, Topic::count());
        $this->assertDatabaseHas(
            app(Topic::class)->getTable(),
            [
                'course_id' => $course->id,
                'title' => 'Topic 1',
                'created_by' => $user->id,
            ]
        );
    }

    public function testCreateTopicInEnrolledCourseWithValidDataExpectReturnCreatedTopic()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $url = route('badaso.topic.add');
        $response = AuthHelper::asUser($this, $user)->json('POST', $url, [
            'course_id' => $course->id,
            'title' => 'Topic 1',
        ]);

        $topicData = $response->json('data');

        $response->assertStatus(200);
        $this->assertArrayHasKey('id', $topicData);
        $this->assertEquals($topicData['courseId'], $course->id);
        $this->assertEquals($topicData['title'], 'Topic 1');
        $this->assertEquals($topicData['createdBy'], $user->id);
    }

    public function testBrowseTopicWithoutLogin()
    {
        $url = route('badaso.topic.browse');
        $response = $this->json('GET', $url);
        $response->assertStatus(401);
    }

    public function testBrowseTopicWithoutCourseId()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $url = route('badaso.topic.browse');
        $response = AuthHelper::asUser($this, $user)->json('GET', $url);

        $response->assertStatus(400);
    }

    public function testBrowseTopicGivenUnenrolledCourseId()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $url = route('badaso.topic.browse');
        $response = AuthHelper::asUser($this, $user)->json('GET', $url, [
            'course_id' => 413423,
        ]);

        $response->assertStatus(400);
    }

    public function testBrowseTopicGivenEnrolledCourseIdExpectResponseCorrectTopic()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $otherCourse = Course::factory()
            ->create();

        Topic::factory()->count(5)->for($otherCourse)->create();

        $topic = Topic::factory()
            ->for($course)
            ->create();

        $url = route('badaso.topic.browse', ['course_id' => $course->id]);
        $response = AuthHelper::asUser($this, $user)->json('GET', $url);

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
        $response->assertJson(['data' =>[
            [
                'courseId' => $course->id,
                'id' => null,
                'title' => null,
            ],
            [
                'courseId' => $course->id,
                'id' => $topic->id,
                'title' => $topic->title,
            ],
        ]]);
    }

    public function testBrowseTopicGivenNoTopicCreatedExpectResponseNullTopic()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $url = route('badaso.topic.browse', ['course_id' => $course->id]);
        $response = AuthHelper::asUser($this, $user)->json('GET', $url);

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment([
            'id' => null,
            'title' => null,
        ]);
    }

    public function testBrowseTopicGivenValidUserExpectRespondsWithLessonMaterials()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::STUDENT])
            ->create();

        $topic = Topic::factory()
            ->for($course)
            ->create();

        $lessonMaterial_A = LessonMaterial::factory()
            ->for($course)
            ->for($topic)
            ->create();

        $lessonMaterial_B = LessonMaterial::factory()
            ->for($course)
            ->for($topic)
            ->create();

        $lessonMaterial_C = LessonMaterial::factory()
            ->for($course)
            ->for($topic)
            ->create();

        $url = route('badaso.topic.browse', ['course_id' => $course->id]);
        $response = AuthHelper::asUser($this, $user)->json('GET', $url);

        $response->assertStatus(200);
        $response->assertJson(['data' =>[
            [
                'id' => null,
                'title' => null,
                'courseId' => $course->id,
                'lessonMaterials' => [],
                'quizzes' => [],
            ],
            [
                'id' => $topic->id,
                'title' => $topic->title,
                'courseId' => $course->id,
                'createdBy' => $topic->createdBy->id,
                'lessonMaterials' => [
                    [
                        'id' => $lessonMaterial_A->id,
                        'title' => $lessonMaterial_A->title,
                        'topicId' => $lessonMaterial_A->topic_id,
                    ],
                    [
                        'id' => $lessonMaterial_B->id,
                        'title' => $lessonMaterial_B->title,
                        'topicId' => $lessonMaterial_B->topic_id,
                    ],
                    [
                        'id' => $lessonMaterial_C->id,
                        'title' => $lessonMaterial_C->title,
                        'topicId' => $lessonMaterial_C->topic_id,
                    ],
                ],
            ],
        ]]);
    }

    public function testBrowseTopicGivenValidUserExpectRespondsWithQuizzes()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::STUDENT])
            ->create();

        $topic = Topic::factory()
            ->for($course)
            ->create();

        $quiz_A = LessonMaterial::factory()
            ->for($course)
            ->for($topic)
            ->create();

        $quiz_B = LessonMaterial::factory()
            ->for($course)
            ->for($topic)
            ->create();

        $quiz_C = LessonMaterial::factory()
            ->for($course)
            ->for($topic)
            ->create();

        $url = route('badaso.topic.browse', ['course_id' => $course->id]);
        $response = AuthHelper::asUser($this, $user)->json('GET', $url);

        $response->assertStatus(200);
        $response->assertJson(['data' =>[
            [
                'id' => null,
                'title' => null,
                'courseId' => $course->id,
                'lessonMaterials' => [],
                'quizzes' => [],
            ],
            [
                'id' => $topic->id,
                'title' => $topic->title,
                'courseId' => $course->id,
                'createdBy' => $topic->createdBy->id,
                'lessonMaterials' => [
                    [
                        'id' => $quiz_A->id,
                        'title' => $quiz_A->title,
                        'topicId' => $quiz_A->topic_id,
                    ],
                    [
                        'id' => $quiz_B->id,
                        'title' => $quiz_B->title,
                        'topicId' => $quiz_B->topic_id,
                    ],
                    [
                        'id' => $quiz_C->id,
                        'title' => $quiz_C->title,
                        'topicId' => $quiz_C->topic_id,
                    ],
                ],
            ],
        ]]);
    }

    public function testBrowseTopicGivenValidUserExpectRespondsWithLessonMaterialsThatHaveNoTopic()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::STUDENT])
            ->create();

        LessonMaterial::factory()
            ->for($course)
            ->create();

        $url = route('badaso.topic.browse', ['course_id' => $course->id]);
        $response = AuthHelper::asUser($this, $user)->json('GET', $url);

        $topicsData = $response->json('data');
        $nullTopic = $topicsData[0];
        $lessonMaterialsData = $nullTopic['lessonMaterials'];
        $lessonMaterialData = $lessonMaterialsData[0];

        $response->assertStatus(200);

        $this->assertNull($nullTopic['id']);
        $this->assertNull($nullTopic['title']);

        $this->assertCount(1, $lessonMaterialsData);
        $this->assertArrayHasKey('id', $lessonMaterialData);
        $this->assertArrayHasKey('title', $lessonMaterialData);
        $this->assertArrayHasKey('createdAt', $lessonMaterialData);
        $this->assertArrayHasKey('topicId', $lessonMaterialData);
    }

    public function testBrowseTopicGivenValidUserExpectRespondsWithAssignments()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::STUDENT])
            ->create();

        $topic = Topic::factory()
            ->for($course)
            ->create();

        $assignment_A = Assignment::factory()
            ->for($topic)
            ->create();

        $assignment_B = Assignment::factory()
            ->for($topic)
            ->create();

        $url = route('badaso.topic.browse', ['course_id' => $course->id]);
        $response = AuthHelper::asUser($this, $user)->json('GET', $url);

        $response->assertStatus(200);
        $response->assertJson(['data' =>[
            [
                'id' => null,
                'title' => null,
                'courseId' => $course->id,
                'lessonMaterials' => [],
                'quizzes' => [],
                'assignments' => [],
            ],
            [
                'id' => $topic->id,
                'title' => $topic->title,
                'courseId' => $course->id,
                'createdBy' => $topic->createdBy->id,
                'lessonMaterials' => [
                    [
                        'id' => $assignment_A->id,
                        'title' => $assignment_A->title,
                        'topicId' => $assignment_A->topic_id,
                    ],
                    [
                        'id' => $assignment_B->id,
                        'title' => $assignment_B->title,
                        'topicId' => $assignment_B->topic_id,
                    ],
                ],
            ],
        ]]);
    }

    public function testBrowseTopicGivenValidUserExpectRespondsWithAssignmentsThatHaveNoTopic()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::STUDENT])
            ->create();

        Assignment::factory()
            ->for($course)
            ->create();

        $url = route('badaso.topic.browse', ['course_id' => $course->id]);
        $response = AuthHelper::asUser($this, $user)->json('GET', $url);

        $topicsData = $response->json('data');
        $nullTopic = $topicsData[0];
        $assignmentsData = $nullTopic['assignments'];
        $assignmentData = $assignmentsData[0];

        $response->assertStatus(200);

        $this->assertNull($nullTopic['id']);
        $this->assertNull($nullTopic['title']);

        $this->assertCount(1, $assignmentsData);
        $this->assertArrayHasKey('id', $assignmentData);
        $this->assertArrayHasKey('title', $assignmentData);
        $this->assertArrayHasKey('createdAt', $assignmentData);
        $this->assertArrayHasKey('topicId', $assignmentData);
    }

    public function testEditTopicWithoutLogin()
    {
        $url = route('badaso.topic.edit', ['id' => 1]);
        $response = $this->json('PUT', $url);
        $response->assertStatus(401);
    }

    public function testEditTopicGivenNoContent()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $topic = Topic::factory()
            ->for($course)
            ->create();

        $url = route('badaso.topic.edit', ['id' => $topic->id]);
        $response = AuthHelper::asUser($this, $user)->json('PUT', $url);

        $response->assertStatus(400);
    }

    public function testEditTopicGivenTopicDoesNotExist()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $url = route('badaso.topic.edit', ['id' => 1]);
        $response = AuthHelper::asUser($this, $user)->json('PUT', $url, [
            'title' => 'Title 2',
        ]);

        $response->assertStatus(400);
    }

    public function testEditTopicGivenUserIsNotCreator()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $topic = Topic::factory()->create();

        $url = route('badaso.topic.edit', ['id' => $topic->id]);
        $response = AuthHelper::asUser($this, $user)->json('PUT', $url, [
            'title' => 'Title 2',
        ]);

        $response->assertStatus(400);
    }

    public function testEditTopicGivenCorrectUserHasUnenrolledTheCourse()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $topic = Topic::factory()->create([
            'created_by' => $user->id,
        ]);

        $url = route('badaso.topic.edit', ['id' => $topic->id]);
        $response = AuthHelper::asUser($this, $user)->json('PUT', $url, [
            'title' => 'Title 2',
        ]);

        $response->assertStatus(400);
    }

    public function testEditTopicGivenValidDataExpectUpdated()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $topic = Topic::factory()
            ->for($course)
            ->create([
                'created_by' => $user->id,
                'title' => 'Title 1',
            ]);

        $url = route('badaso.topic.edit', ['id' => $topic->id]);
        $response = AuthHelper::asUser($this, $user)->json('PUT', $url, [
            'title' => 'Topic 2',
        ]);

        $newTopic = Topic::find($topic->id);
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $topic->id,
            'title' => 'Topic 2',
        ]);
        $this->assertEquals($newTopic->title, 'Topic 2');
    }

    public function testDeleteTopicWithoutLogin()
    {
        $url = route('badaso.topic.delete', ['id' => 1]);
        $response = $this->json('DELETE', $url);
        $response->assertStatus(401);
    }

    public function testDeleteTopicGivenTopicDoesNotExist()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $url = route('badaso.topic.delete', ['id' => 1]);
        $response = AuthHelper::asUser($this, $user)->json('DELETE', $url);

        $response->assertStatus(400);
    }

    public function testDeleteTopicGivenUserIsNotCreator()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $topic = Topic::factory()->create();

        $url = route('badaso.topic.delete', ['id' => $topic->id]);
        $response = AuthHelper::asUser($this, $user)->json('DELETE', $url);

        $response->assertStatus(400);
    }

    public function testDeleteTopicGivenCorrectUserHasUnenrolledTheCourse()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $topic = Topic::factory()->create([
            'created_by' => $user->id,
        ]);

        $url = route('badaso.topic.delete', ['id' => $topic->id]);
        $response = AuthHelper::asUser($this, $user)->json('DELETE', $url);

        $response->assertStatus(400);
    }

    public function testDeleteTopicGivenValidDataExpectRemoved()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $topic = Topic::factory()
            ->for($course)
            ->create([
                'created_by' => $user->id,
                'title' => 'Topic',
            ]);

        $url = route('badaso.topic.delete', ['id' => $topic->id]);
        $response = AuthHelper::asUser($this, $user)->json('DELETE', $url);
        $removedTopic = Topic::find($topic->id);

        $this->assertEmpty($removedTopic);
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $topic->id,
            'title' => $topic->title,
        ]);
    }
}
