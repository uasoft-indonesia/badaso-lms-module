<?php

namespace Uasoft\Badaso\Module\LMSModule\Tests\Feature;

use Tests\TestCase;
use Uasoft\Badaso\Module\LMSModule\Enums\CourseUserRole;
use Uasoft\Badaso\Module\LMSModule\Models\Course;
use Uasoft\Badaso\Module\LMSModule\Models\LessonMaterial;
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

    public function testReadLessonMaterialsWithoutLoginExpectResponse401()
    {
        $url = route('badaso.lesson_material.read', ['id' => 1]);
        $response = $this->json('GET', $url);
        $response->assertStatus(401);
    }

    public function testReadLessonMaterialsGivenUnenrolledCourseExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $lessonMaterial = LessonMaterial::factory()->create();

        $url = route('badaso.lesson_material.read', ['id' => $lessonMaterial->id]);
        $response = AuthHelper::asUser($this, $user)->json('GET', $url);

        $response->assertStatus(400);
    }
}
