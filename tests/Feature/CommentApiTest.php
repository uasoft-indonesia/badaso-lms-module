<?php

namespace Uasoft\Badaso\Module\LMSModule\Tests\Feature;

use Tests\TestCase;
use Uasoft\Badaso\Module\LMSModule\Enums\CourseUserRole;
use Uasoft\Badaso\Module\LMSModule\Models\Announcement;
use Uasoft\Badaso\Module\LMSModule\Models\Comment;
use Uasoft\Badaso\Module\LMSModule\Models\Course;
use Uasoft\Badaso\Module\LMSModule\Models\User;
use Uasoft\Badaso\Module\LMSModule\Tests\Helpers\AuthHelper;

class CommentApiTest extends TestCase
{
    public function testCreateCommentWithoutLoginExpectResponse401()
    {
        $url = route('badaso.announcement.comment');
        $response = $this->json('POST', $url);
        $response->assertStatus(401);
    }

    public function testCreateCommentInNonExistingAnnouncementExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $url = $url = route('badaso.announcement.comment');
        $response = AuthHelper::asUser($this, $user)->json('POST', $url, [
            'announcement_id' => 1,
            'content' => 'Test content',
        ]);

        $response->assertStatus(400);
    }

    public function testCreateCommentInExistingAnnouncementWithNoContentExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $url = $url = route('badaso.announcement.comment');
        $response = AuthHelper::asUser($this, $user)->json('POST', $url, [
            'announcement_id' => 1,
        ]);

        $response->assertStatus(400);
    }

    public function testCreateCommentInExistingAnnouncementWithValidDataExpectInsertedToDatabase()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $announcement = Announcement::factory()->create();

        $url = $url = route('badaso.announcement.comment');
        AuthHelper::asUser($this, $user)->json('POST', $url, [
            'announcement_id' => $announcement->id,
            'content' => 'Test content',
        ]);

        $this->assertEquals(1, Comment::count());
        $this->assertDatabaseHas(
            app(Comment::class)->getTable(),
            [
                'announcement_id' => $announcement->id,
                'content' => 'Test content',
                'created_by' => $user->id,
            ]
        );
    }

    public function testCreateCommentInExistingAnnouncementWithValidDataExpectReturnCreatedComment()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $announcement = Announcement::factory()->create();

        $url = $url = route('badaso.announcement.comment');
        $response = AuthHelper::asUser($this, $user)->json('POST', $url, [
            'announcement_id' => $announcement->id,
            'content' => 'This is my comment',
        ]);

        $commentData = $response->json('data');

        $response->assertStatus(200);
        $this->assertArrayHasKey('id', $commentData);
        $this->assertEquals($commentData['announcementId'], $announcement->id);
        $this->assertEquals($commentData['content'], 'This is my comment');
        $this->assertEquals($commentData['createdBy'], $user->id);
    }
}
