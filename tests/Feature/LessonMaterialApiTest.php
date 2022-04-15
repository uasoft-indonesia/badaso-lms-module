<?php

namespace Uasoft\Badaso\Module\LMSModule\Tests\Feature;

use Tests\TestCase;
use Uasoft\Badaso\Module\LMSModule\Enums\CourseUserRole;
use Uasoft\Badaso\Module\LMSModule\Models\Course;
use Uasoft\Badaso\Module\LMSModule\Models\LessonMaterial;
use Uasoft\Badaso\Module\LMSModule\Models\Topic;
use Uasoft\Badaso\Module\LMSModule\Models\User;
use Uasoft\Badaso\Module\LMSModule\Tests\Helpers\AuthHelper;

class LessonMaterialApiTest extends TestCase
{
    public function testCreateLessonMaterialWithoutLoginExpectResponse401()
    {
        $url = route('badaso.lesson_material.add');
        $response = $this->json('POST', $url);
        $response->assertStatus(401);
    }

    public function testCreateLessonMaterialInAnUnerolledCourseExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $url = route('badaso.lesson_material.add');
        $response = AuthHelper::asUser($this, $user)->json('POST', $url, [
            'course_id' => 1,
            'title' => 'test title',
        ]);

        $response->assertStatus(400);
    }

    public function testCreateLessonMaterialAsStudentExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::STUDENT])
            ->create();

        $url = route('badaso.lesson_material.add');
        $response = AuthHelper::asUser($this, $user)->json('POST', $url, [
            'course_id' => $course->id,
            'title' => 'test title',
        ]);

        $response->assertStatus(400);
    }

    public function testCreateLessonMaterialAsTeacherWithNoTitleExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $url = route('badaso.lesson_material.add');
        $response = AuthHelper::asUser($this, $user)->json('POST', $url, [
            'course_id' => $course->id,
        ]);

        $response->assertStatus(400);
    }

    public function testCreateLessonMaterialAsTeacherWithValidDataExpectInsertedToDatabase()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $url = route('badaso.lesson_material.add');
        AuthHelper::asUser($this, $user)->json('POST', $url, [
            'course_id' => $course->id,
            'title' => 'test title',
        ]);

        $this->assertEquals(1, LessonMaterial::count());
        $this->assertDatabaseHas(
            app(LessonMaterial::class)->getTable(),
            [
                'course_id' => $course->id,
                'title' => 'test title',
                'created_by' => $user->id,
            ]
        );
    }

    public function testCreateLessonMaterialAsTeacherWithValidDataExpectReturnCreatedLessonMaterial()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $url = route('badaso.lesson_material.add');
        $response = AuthHelper::asUser($this, $user)->json('POST', $url, [
            'course_id' => $course->id,
            'title' => 'test title',
        ]);

        $lesssonMaterialData = $response->json('data');

        $response->assertStatus(200);
        $this->assertArrayHasKey('id', $lesssonMaterialData);
        $this->assertEquals($lesssonMaterialData['courseId'], $course->id);
        $this->assertEquals($lesssonMaterialData['title'], 'test title');
        $this->assertEquals($lesssonMaterialData['createdBy'], $user->id);
    }

    public function testReadLessonMaterialWithoutLoginExpectResponse401()
    {
        $url = route('badaso.lesson_material.read', ['id' => 1]);
        $response = $this->json('GET', $url);
        $response->assertStatus(401);
    }

    public function testReadLessonMaterialGivenUnenrolledCourseExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $lessonMaterial = LessonMaterial::factory()->create();

        $url = route('badaso.lesson_material.read', ['id' => $lessonMaterial->id]);
        $response = AuthHelper::asUser($this, $user)->json('GET', $url);

        $response->assertStatus(400);
    }

    public function testReadLessonMaterialGivenValidDataExpectRespondsWithLessonMaterial()
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

        $url = route('badaso.lesson_material.read', ['id' => $lessonMaterial->id]);
        $response = AuthHelper::asUser($this, $user)->json('GET', $url);

        $lessonMaterialData = $response->json('data');

        $response->assertStatus(200);
        $this->assertArrayHasKey('id', $lessonMaterialData);
        $this->assertEquals($lessonMaterialData['courseId'], $course->id);
        $this->assertEquals($lessonMaterialData['title'], $lessonMaterial->title);
        $this->assertEquals($lessonMaterialData['createdBy']['name'], $lessonMaterial->createdBy->name);
        $this->assertEquals($lessonMaterialData['topic']['title'], $lessonMaterial->topic->title);
    }

    public function testEditLessonMaterialWithoutLoginExpectResponse401()
    {
        $url = route('badaso.lesson_material.edit', ['id' => 1]);
        $response = $this->json('PUT', $url);
        $response->assertStatus(401);
    }

    public function testEditLessonMaterialGivenUserIsNotCreatorExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $lessonMaterial = LessonMaterial::factory()
            ->for($course)
            ->create();

        $url = route('badaso.lesson_material.edit', ['id' => $lessonMaterial->id]);
        $response = AuthHelper::asUser($this, $user)->json('PUT', $url);

        $response->assertStatus(400);
    }

    public function testEditLessonMaterialGivenCreatorHasUnenrolledTheCourseExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $lessonMaterial = LessonMaterial::factory()->create([
            'created_by' => $user->id,
        ]);

        $url = route('badaso.lesson_material.edit', ['id' => $lessonMaterial->id]);
        $response = AuthHelper::asUser($this, $user)->json('PUT', $url);

        $response->assertStatus(400);
    }

    public function testEditLessonMaterialGivenValidDataExpectUpdated()
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
            ->create([
                'created_by' => $user->id,
            ]);

        $url = route('badaso.lesson_material.edit', ['id' => $lessonMaterial->id]);
        AuthHelper::asUser($this, $user)->json('PUT', $url, [
            'title' => 'new title',
            'content' => 'new content',
            'file_url' => 'http://new-file-url.com',
            'link_url' => 'http://new-link-url.com',
        ]);

        $this->assertDatabaseHas(
            app(LessonMaterial::class)->getTable(),
            [
                'id' => $lessonMaterial->id,
                'title' => 'new title',
                'content' => 'new content',
                'file_url' => 'http://new-file-url.com',
                'link_url' => 'http://new-link-url.com',
            ]
        );
    }

    public function testEditLessonMaterialGivenValidDataExpectReturnUpdatedLessonMaterial()
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
            ->create([
                'created_by' => $user->id,
            ]);

        $url = route('badaso.lesson_material.edit', ['id' => $lessonMaterial->id]);
        $response = AuthHelper::asUser($this, $user)->json('PUT', $url, [
            'title' => 'new title',
            'content' => 'new content',
            'file_url' => 'http://new-file-url.com',
            'link_url' => 'http://new-link-url.com',
        ]);

        $lessonMaterialData = $response->json('data');

        $response->assertStatus(200);
        $this->assertArrayHasKey('id', $lessonMaterialData);
        $this->assertEquals($lessonMaterialData['title'], 'new title');
        $this->assertEquals($lessonMaterialData['content'], 'new content');
        $this->assertEquals($lessonMaterialData['fileUrl'], 'http://new-file-url.com');
        $this->assertEquals($lessonMaterialData['linkUrl'], 'http://new-link-url.com');
    }

    public function testDeleteLessonMaterialWithoutLoginExpectResponse401()
    {
        $url = route('badaso.lesson_material.delete', ['id' => 1]);
        $response = $this->json('DELETE', $url);
        $response->assertStatus(401);
    }

    public function testDeleteLessonMaterialGivenUserIsNotCreatorExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $course = Course::factory()
            ->hasAttached($user, ['role' => CourseUserRole::TEACHER])
            ->create();

        $lessonMaterial = LessonMaterial::factory()
            ->for($course)
            ->create();

        $url = route('badaso.lesson_material.delete', ['id' => $lessonMaterial->id]);
        $response = AuthHelper::asUser($this, $user)->json('DELETE', $url);

        $response->assertStatus(400);
    }
}
