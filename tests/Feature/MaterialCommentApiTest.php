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

        $lessonMaterial = LessonMaterial::factory()->create();

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

        $lessonMaterial = LessonMaterial::factory()->create();

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

    public function testReadLessonMaterialAlsoReturnCommentsOfEachLessonMaterialCorrectly()
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
}
