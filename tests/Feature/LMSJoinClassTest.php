<?php

namespace Uasoft\Badaso\Module\LMSModule\Tests\Feature;

use Uasoft\Badaso\Module\LMSModule\Models\Course;
use Uasoft\Badaso\Module\LMSModule\Models\CourseUser;
use Uasoft\Badaso\Module\LMSModule\Models\User;
use Tests\TestCase;
use Uasoft\Badaso\Module\LMSModule\Tests\Helpers\AuthHelper;

class LMSJoinClassTest extends TestCase
{

  public function testJoinClassWithoutLoginExpectResponseStatus401()
  {
    $url = route('badaso.course.join');
    $response = $this->json('POST', $url);
    $response->assertStatus(401);
  }

  public function testJoinClassAsAuthorizedUserWithUnknownClassCodeExpectResponseStatus500()
  {
    $user = User::factory()->create();
    $user->rawPassword = 'password';

    $url = route('badaso.course.join');

    $response = AuthHelper::asUser($this, $user)->json('POST', $url, [
      'code' => 'xxx'
    ]);
    $response->assertStatus(500);
  }

  public function testJoinClassAsAuthorizedUserWithNoClassCodeAsInputExpectResponseStatus400()
  {
    $user = User::factory()->create();
    $user->rawPassword = 'password';

    $url = route('badaso.course.join');

    $response = AuthHelper::asUser($this, $user)->json('POST', $url, []);
    $response->assertStatus(400);
  }

  public function testJoinClassAsAuthorizedUserWithValidClassCodeExpectResponseStatus200()
  {
    $userTeacher = User::factory()->create();
    $userTeacher->rawPassword = 'password';

    $userStudent = User::factory()->create();
    $userStudent->rawPassword = 'password';

    $courseUrl = route('badaso.course.add');
    AuthHelper::asUser($this, $userTeacher)->json('POST', $courseUrl, [
      'name' => 'Test Course',
      'subject' => 'Test Subject',
      'room' => 'Test room',
    ]);

    $course = Course::first();

    $joinUrl = route('badaso.course.join');
    $response = AuthHelper::asUser($this, $userStudent)->json('POST', $joinUrl, [
      'code' => $course->join_code,
    ]);

    $response->assertStatus(200);
  }

  public function testJoinClassAsAuthorizeUserWithValidClassCodeShouldAddStudent()
  {
    $userTeacher = User::factory()->create();
    $userTeacher->rawPassword = 'password';

    $userStudent = User::factory()->create();
    $userStudent->rawPassword = 'password';

    $course_url = route('badaso.course.add');
    AuthHelper::asUser($this, $userTeacher)->json('POST', $course_url, [
      'name' => 'Test Course',
      'subject' => 'Test Subject',
      'room' => 'Test room',
    ]);

    $course = Course::first();
    $courseUserBeforeCount = CourseUser::where(
      'course_id', '=', $course->id, 'and')
      ->where('role', '=', 'student')
      ->count();

    $joinUrl = route('badaso.course.join');
    AuthHelper::asUser($this, $userStudent)->json('POST', $joinUrl, [
      'code' => $course->join_code,
    ]);

    $courseUserAfterCount = CourseUser::where(
      'course_id', '=', $course->id, 'and')
      ->where('role', '=', 'student')
      ->count();

    $this->assertEquals(0, $courseUserBeforeCount);
    $this->assertEquals(1, $courseUserAfterCount);
  }
}
