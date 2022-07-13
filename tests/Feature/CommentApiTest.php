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
        $url = route('badaso.comment.add');
        $response = $this->json('POST', $url);
        $response->assertStatus(401);
    }

    public function testCreateCommentInNonExistingAnnouncementExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $url = route('badaso.comment.add');
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

        $url = route('badaso.comment.add');
        $response = AuthHelper::asUser($this, $user)->json('POST', $url, [
            'announcement_id' => 1,
        ]);

        $response->assertStatus(400);
    }

    public function testCreateCommentInExistingAnnouncementWithValidDataExpectInsertedToDatabase()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $announcement = Announcement::factory()->for($course)->create();

        $url = route('badaso.comment.add');
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

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $announcement = Announcement::factory()->for($course)->create();

        $url = route('badaso.comment.add');
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

    public function testBrowseAnnouncementAlsoReturnCommentsOfEachAnnouncementCorrectly()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $announcement_A = Announcement::factory()
            ->for($course)
            ->create();
        $comment_A = Comment::factory()
            ->for($announcement_A)
            ->create();

        $announcement_B = Announcement::factory()
            ->for($course)
            ->create();
        $comment_B = Comment::factory()
            ->for($announcement_B)
            ->create();

        $url = route('badaso.announcement.browse', ['course_id' => $course->id]);
        $response = AuthHelper::asUser($this, $user)->json('GET', $url);

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
        $response->assertJson(['data' => [
            [
                'id' => $announcement_A->id,
                'comments' => [
                    [
                        'id' => $comment_A->id,
                        'announcementId' => $announcement_A->id,
                        'content' => $comment_A->content,
                    ],
                ],
            ],
            [
                'id' => $announcement_B->id,
                'comments' => [
                    [
                        'id' => $comment_B->id,
                        'announcementId' => $announcement_B->id,
                        'content' => $comment_B->content,
                    ],
                ],
            ],
        ]]);
    }

    public function testEditCommentWithoutLoginExpectResponse401()
    {
        $url = route('badaso.comment.edit', ['id' => 1]);
        $response = $this->json('PUT', $url);

        $response->assertStatus(401);
    }

    public function testEditCommentGivenCommentDoesNotExistExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $url = route('badaso.comment.edit', ['id' => 1]);
        $response = AuthHelper::asUser($this, $user)->json('PUT', $url, [
            'content' => 'Editted content',
        ]);

        $response->assertStatus(400);
    }

    public function testEditCommentGivenNoContentExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $announcement = Announcement::factory()->create();
        $comment = Comment::factory()
            ->for($announcement)
            ->create();

        $url = route('badaso.comment.edit', ['id' => $comment->id]);
        $response = AuthHelper::asUser($this, $user)->json('PUT', $url);

        $response->assertStatus(400);
    }

    public function testEditCommentGivenUserIsNotCreatorExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $comment = Comment::factory()->create();

        $url = route('badaso.comment.edit', ['id' => $comment->id]);
        $response = AuthHelper::asUser($this, $user)->json('PUT', $url, [
            'content' => 'Editted content',
        ]);

        $response->assertStatus(400);
    }

    public function testEditCommentGivenValidDataExpectUpdated()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $announcement = Announcement::factory()
            ->for($course)
            ->create();

        $comment = Comment::factory()
            ->for($announcement)
            ->create([
                'created_by' => $user->id,
                'content' => 'Uneditted content',
            ]);

        $url = route('badaso.comment.edit', ['id' => $comment->id]);
        AuthHelper::asUser($this, $user)->json('PUT', $url, [
            'content' => 'Editted content',
        ]);

        $newComment = Comment::find($comment->id);
        $this->assertEquals($newComment->content, 'Editted content');
    }

    public function testEditCommentGivenValidDataExpectReturnUpdatedComment()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $announcement = Announcement::factory()
            ->for($course)
            ->create();

        $comment = Comment::factory()
            ->for($announcement)
            ->create([
                'created_by' => $user->id,
                'content' => 'Uneditted content',
            ]);

        $url = route('badaso.comment.edit', ['id' => $comment->id]);
        $response = AuthHelper::asUser($this, $user)->json('PUT', $url, [
            'content' => 'Editted content',
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $comment->id,
            'content' => 'Editted content',
        ]);
    }

    public function testDeleteCommentWithoutLoginExpectResponse401()
    {
        $url = route('badaso.comment.delete', ['id' => 1]);
        $response = $this->json('DELETE', $url);
        $response->assertStatus(401);
    }

    public function testDeleteCommentGivenCommentDoesNotExistExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $url = route('badaso.comment.delete', ['id' => 1]);
        $response = AuthHelper::asUser($this, $user)->json('DELETE', $url);
        $response->assertStatus(400);
    }

    public function testDeleteCommentGivenUserIsNotCreatorExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $comment = Comment::factory()->create();

        $url = route('badaso.comment.delete', ['id' => $comment->id]);
        $response = AuthHelper::asUser($this, $user)->json('DELETE', $url);

        $response->assertStatus(400);
    }

    public function testDeleteCommentOfOtherPeopleGivenUserIsNotTeacherExpectResponse401()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $student = User::factory()->create();
        $student->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::STUDENT])
            ->create();

        $announcement = Announcement::factory()
            ->for($course)
            ->create();

        $comment = Comment::factory()
            ->for($announcement)
            ->create([
                'created_by' => $student->id,
                'content' => 'To NOT be deleted',
            ]);

        $url = route('badaso.comment.delete', ['id' => $comment->id]);
        $response = AuthHelper::asUser($this, $user)->json('DELETE', $url);

        $response->assertStatus(401);
    }

    public function testDeleteOwnCommentGivenValidDataExpectDeleted()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::STUDENT])
            ->create();

        $announcement = Announcement::factory()
            ->for($course)
            ->create();

        $comment = Comment::factory()
            ->for($announcement)
            ->create([
                'created_by' => $user->id,
                'content' => 'To be deleted',
            ]);

        $this->assertEquals(1, Comment::count());

        $url = route('badaso.comment.delete', ['id' => $comment->id]);
        AuthHelper::asUser($this, $user)->json('DELETE', $url);

        $this->assertEquals(0, Comment::count());
    }

    public function testDeleteCommentOfOtherPeopleGivenValidDataExpectDeleted()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $student = User::factory()->create();
        $student->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $announcement = Announcement::factory()
            ->for($course)
            ->create();

        $comment = Comment::factory()
            ->for($announcement)
            ->create([
                'created_by' => $student->id,
                'content' => 'To be deleted',
            ]);

        $this->assertEquals(1, Comment::count());

        $url = route('badaso.comment.delete', ['id' => $comment->id]);
        AuthHelper::asUser($this, $user)->json('DELETE', $url);

        $this->assertEquals(0, Comment::count());
    }

    public function testDeleteCommentGivenValidDataExpectResponse200()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $announcement = Announcement::factory()
            ->for($course)
            ->create();

        $comment = Comment::factory()
            ->for($announcement)
            ->create([
                'created_by' => $user->id,
                'content' => 'To be deleted',
            ]);

        $url = route('badaso.comment.delete', ['id' => $comment->id]);
        $response = AuthHelper::asUser($this, $user)->json('DELETE', $url);

        $response->assertStatus(200);
    }
}
