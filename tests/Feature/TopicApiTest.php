<?php

namespace Uasoft\Badaso\Module\LMS\Tests\Feature;

use Tests\TestCase;
use Uasoft\Badaso\Module\LMSModule\Enums\CourseUserRole;
use Uasoft\Badaso\Module\LMSModule\Models\Course;
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

    public function testCreateTopicInEnrolledCourseWithValidDataExpectReturnCreatedAnnouncement()
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
}