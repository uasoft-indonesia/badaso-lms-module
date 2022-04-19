<?php

namespace Uasoft\Badaso\Module\LMSModule\Tests\Feature;

use Tests\TestCase;
use Uasoft\Badaso\Module\LMSModule\Enums\CourseUserRole;
use Uasoft\Badaso\Module\LMSModule\Models\Course;
use Uasoft\Badaso\Module\LMSModule\Models\LessonMaterial;
use Uasoft\Badaso\Module\LMSModule\Models\MaterialComment;
use Uasoft\Badaso\Module\LMSModule\Models\Topic;
use Uasoft\Badaso\Module\LMSModule\Models\User;
use Uasoft\Badaso\Module\LMSModule\Tests\Helpers\AuthHelper;

class MaterialCommentApiTest extends TestCase
{
    public function testCreateMaterialCommentWithoutLoginExpectResponse401()
    {
        $url = route('badaso.material_comment.add');
        $response = $this->json('POST', $url);
        $response->assertStatus(401);
    }

    public function testCreateMaterialCommentInNonExistingLessonMaterialExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $url = route('badaso.material_comment.add');
        $response = AuthHelper::asUser($this, $user)->json('POST', $url, [
            'material_id' => 1,
            'content' => 'Test content',
        ]);

        $response->assertStatus(400);
    }

    public function testCreateMaterialCommentInExistingLessonMaterialWithNoContentExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $url = route('badaso.material_comment.add');
        $response = AuthHelper::asUser($this, $user)->json('POST', $url, [
            'material_id' => 1,
        ]);

        $response->assertStatus(400);
    }

    public function testCreateMaterialCommentInExistingLessonMaterialWithValidDataExpectInsertedToDatabase()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $topic = Topic::factory()
            ->for($course)
            ->create();

        $lessonMaterial = LessonMaterial::factory()
            ->for($course)
            ->for($topic)
            ->create();

        $url = route('badaso.material_comment.add');
        AuthHelper::asUser($this, $user)->json('POST', $url, [
            'material_id' => $lessonMaterial->id,
            'content' => 'Test content',
        ]);

        $this->assertEquals(1, MaterialComment::count());
        $this->assertDatabaseHas(
            app(MaterialComment::class)->getTable(),
            [
                'material_id' => $lessonMaterial->id,
                'content' => 'Test content',
                'created_by' => $user->id,
            ]
        );
    }

    public function testCreateMaterialCommentInExistingLessonMaterialWithValidDataExpectReturnCreatedComment()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $topic = Topic::factory()
            ->for($course)
            ->create();

        $lessonMaterial = LessonMaterial::factory()
            ->for($course)
            ->for($topic)
            ->create();

        $url = route('badaso.material_comment.add');
        $response = AuthHelper::asUser($this, $user)->json('POST', $url, [
            'material_id' => $lessonMaterial->id,
            'content' => 'This is my comment',
        ]);

        $commentData = $response->json('data');

        $response->assertStatus(200);
        $this->assertArrayHasKey('id', $commentData);
        $this->assertEquals($commentData['materialId'], $lessonMaterial->id);
        $this->assertEquals($commentData['content'], 'This is my comment');
        $this->assertEquals($commentData['createdBy'], $user->id);
    }

    public function testReadLessonMaterialAlsoReturnItsComments()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $topic = Topic::factory()
            ->for($course)
            ->create();

        $lessonMaterial = LessonMaterial::factory()
            ->for($course)
            ->for($topic)
            ->create();

        $comment = MaterialComment::factory()
            ->for($lessonMaterial)
            ->create();

        $url = route('badaso.lesson_material.read', ['id' => $lessonMaterial->id]);
        $response = AuthHelper::asUser($this, $user)->json('GET', $url);

        $response->assertStatus(200);
        $response->assertJson(['data' =>[
            'id' => $lessonMaterial->id,
            'topic' => [
                'id' => $topic->id,
            ],
            'comments' => [
                [
                    'id' => $comment->id,
                    'materialId' => $lessonMaterial->id,
                    'content' => $comment->content,
                ],
            ],
        ]]);
    }

    public function testEditMaterialCommentWithoutLoginExpectResponse401()
    {
        $url = route('badaso.material_comment.edit', ['id' => 1]);
        $response = $this->json('PUT', $url);
        $response->assertStatus(401);
    }

    public function testEditMaterialCommentGivenUserIsNotCreatorExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $lessonMaterial = LessonMaterial::factory()
            ->for($course)
            ->create();

        $materialComment = MaterialComment::factory()
            ->for($lessonMaterial)
            ->create();

        $url = route('badaso.material_comment.edit', ['id' => $materialComment->id]);
        $response = AuthHelper::asUser($this, $user)->json('PUT', $url);

        $response->assertStatus(400);
    }

    public function testEditMaterialCommentGivenCreatorHasUnenrolledTheCourseExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $materialComment = MaterialComment::factory()
            ->create([
                'created_by' => $user->id,
            ]);

        $url = route('badaso.material_comment.edit', ['id' => $materialComment->id]);
        $response = AuthHelper::asUser($this, $user)->json('PUT', $url);

        $response->assertStatus(400);
    }

    public function testEditMaterialCommentGivenValidDataExpectUpdated()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $topic = Topic::factory()
            ->for($course)
            ->create();

        $lessonMaterial = LessonMaterial::factory()
            ->for($course)
            ->for($topic)
            ->create();

        $materialComment = MaterialComment::factory()
            ->for($lessonMaterial)
            ->create([
                'created_by' => $user->id,
                'content' => 'old content'
            ]);

        $url = route('badaso.material_comment.edit', ['id' => $materialComment->id]);
        AuthHelper::asUser($this, $user)->json('PUT', $url, [
            'content' => 'new content',
        ]);

        $this->assertDatabaseHas(
            app(MaterialComment::class)->getTable(),
            [
                'id' => $materialComment->id,
                'content' => 'new content',
                'created_by' => $user->id,
            ]
        );
    }

    public function testEditMaterialCommentGivenValidDataExpectReturnUpdatedMaterialComment()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $topic = Topic::factory()
            ->for($course)
            ->create();

        $lessonMaterial = LessonMaterial::factory()
            ->for($course)
            ->for($topic)
            ->create();

        $materialComment = MaterialComment::factory()
            ->for($lessonMaterial)
            ->create([
                'created_by' => $user->id,
            ]);

        $url = route('badaso.material_comment.edit', ['id' => $materialComment->id]);
        $response = AuthHelper::asUser($this, $user)->json('PUT', $url, [
            'content' => 'new content',
        ]);

        $commentData = $response->json('data');

        $response->assertStatus(200);
        $this->assertArrayHasKey('id', $commentData);
        $this->assertEquals($commentData['materialId'], $lessonMaterial->id);
        $this->assertEquals($commentData['content'], 'new content');
        $this->assertEquals($commentData['createdBy'], $user->id);
    }

    public function testDeleteMaterialCommentWithoutLoginExpectResponse401()
    {
        $url = route('badaso.material_comment.delete', ['id' => 1]);
        $response = $this->json('DELETE', $url);
        $response->assertStatus(401);
    }

    public function testDeleteMaterialCommentGivenUserIsNotCreatorExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $comment = MaterialComment::factory()->create();

        $url = route('badaso.material_comment.delete', ['id' => $comment->id]);
        $response = AuthHelper::asUser($this, $user)->json('DELETE', $url);

        $response->assertStatus(400);
    }

    public function testDeleteMaterialCommentGivenCreatorHasUnenrolledTheCourseExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $materialComment = MaterialComment::factory()->create([
            'created_by' => $user->id,
        ]);

        $url = route('badaso.material_comment.delete', ['id' => $materialComment->id]);
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

        $topic = Topic::factory()
            ->for($course)
            ->create();

        $lessonMaterial = LessonMaterial::factory()
            ->for($course)
            ->for($topic)
            ->create();

        $materialComment = MaterialComment::factory()
            ->for($lessonMaterial)
            ->create([
                'created_by' => $student->id,
            ]);

        $url = route('badaso.material_comment.delete', ['id' => $materialComment->id]);
        $response = AuthHelper::asUser($this, $user)->json('DELETE', $url);

        $response->assertStatus(401);
    }

    public function testDeleteOwnMaterialCommentGivenValidDataExpectDeleted()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::STUDENT])
            ->create();

        $topic = Topic::factory()
            ->for($course)
            ->create();

        $lessonMaterial = LessonMaterial::factory()
            ->for($course)
            ->for($topic)
            ->create();

        $materialComment = MaterialComment::factory()
            ->for($lessonMaterial)
            ->create([
                'created_by' => $user->id,
            ]);

        $this->assertEquals(1, MaterialComment::count());

        $url = route('badaso.material_comment.delete', ['id' => $materialComment->id]);
        $response = AuthHelper::asUser($this, $user)->json('DELETE', $url);

        $this->assertEquals(0, MaterialComment::count());
    }

    public function testDeleteMaterialCommentOfOtherPeopleGivenValidDataExpectDeleted()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $student = User::factory()->create();
        $student->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $topic = Topic::factory()
            ->for($course)
            ->create();

        $lessonMaterial = LessonMaterial::factory()
            ->for($course)
            ->for($topic)
            ->create();

        $materialComment = MaterialComment::factory()
            ->for($lessonMaterial)
            ->create([
                'created_by' => $student->id,
            ]);

        $this->assertEquals(1, MaterialComment::count());

        $url = route('badaso.material_comment.delete', ['id' => $materialComment->id]);
        $response = AuthHelper::asUser($this, $user)->json('DELETE', $url);

        $this->assertEquals(0, MaterialComment::count());
    }

    public function testDeleteMaterialCommentGivenValidDataExpectResponse200()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::STUDENT])
            ->create();

        $topic = Topic::factory()
            ->for($course)
            ->create();

        $lessonMaterial = LessonMaterial::factory()
            ->for($course)
            ->for($topic)
            ->create();

        $materialComment = MaterialComment::factory()
            ->for($lessonMaterial)
            ->create([
                'created_by' => $user->id,
            ]);

        $url = route('badaso.material_comment.delete', ['id' => $materialComment->id]);
        $response = AuthHelper::asUser($this, $user)->json('DELETE', $url);

        $response->assertStatus(200);
    }
}
