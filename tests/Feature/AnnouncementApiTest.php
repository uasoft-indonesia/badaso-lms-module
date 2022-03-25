<?php

namespace Uasoft\Badaso\Module\LMSModule\Tests\Feature;

use Tests\TestCase;
use Uasoft\Badaso\Module\LMSModule\Enums\CourseUserRole;
use Uasoft\Badaso\Module\LMSModule\Models\Announcement;
use Uasoft\Badaso\Module\LMSModule\Models\Course;
use Uasoft\Badaso\Module\LMSModule\Models\User;
use Uasoft\Badaso\Module\LMSModule\Tests\Helpers\AuthHelper;

class AnnonuncementApiTest extends TestCase
{
    public function testCreateAnnouncementWithoutLoginExpectResponse401()
    {
        $url = route('badaso.announcement.add');
        $response = $this->json('POST', $url);
        $response->assertStatus(401);
    }

    public function testCreateAnnouncementInAnUnerolledCourseExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $url = route('badaso.announcement.add');
        $response = AuthHelper::asUser($this, $user)->json('POST', $url, [
            'course_id' => 1,
            'content' => 'Test content',
        ]);

        $response->assertStatus(400);
    }

    public function testCreateAnnouncementInEnrolledCourseWithNoContentExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $url = route('badaso.announcement.add');
        $response = AuthHelper::asUser($this, $user)->json('POST', $url, [
            'course_id' => $course->id,
        ]);

        $response->assertStatus(400);
    }

    public function testCreateAnnouncementInEnrolledCourseWithValidDataExpectInsertedToDatabase()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $url = route('badaso.announcement.add');
        AuthHelper::asUser($this, $user)->json('POST', $url, [
            'course_id' => $course->id,
            'content' => 'Test content',
        ]);

        $this->assertEquals(1, Announcement::count());
        $this->assertDatabaseHas(
            app(Announcement::class)->getTable(),
            [
                'course_id' => $course->id,
                'content' => 'Test content',
                'created_by' => $user->id,
            ]
        );
    }

    public function testCreateAnnouncementInEnrolledCourseWithValidDataExpectReturnCreatedAnnouncement()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $url = route('badaso.announcement.add');
        $response = AuthHelper::asUser($this, $user)->json('POST', $url, [
            'course_id' => $course->id,
            'content' => 'Test content',
        ]);

        $announcementData = $response->json('data');

        $response->assertStatus(200);
        $this->assertArrayHasKey('id', $announcementData);
        $this->assertEquals($announcementData['courseId'], $course->id);
        $this->assertEquals($announcementData['content'], 'Test content');
        $this->assertEquals($announcementData['createdBy'], $user->id);
    }

    public function testBrowseAnnouncementWithoutLoginExpectResponse401()
    {
        $url = route('badaso.announcement.browse');
        $response = $this->json('GET', $url);
        $response->assertStatus(401);
    }

    public function testBrowseAnnouncementWithoutCourseIdExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $url = route('badaso.announcement.browse');
        $response = AuthHelper::asUser($this, $user)->json('GET', $url);

        $response->assertStatus(400);
    }

    public function testBrowseAnnouncementGivenUnenrolledCourseIdExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $url = route('badaso.announcement.browse');
        $response = AuthHelper::asUser($this, $user)->json('GET', $url, [
            'course_id' => 413423,
        ]);

        $response->assertStatus(400);
    }
}   
