<?php

namespace Uasoft\Badaso\Module\LMSModule\Tests\Feature;

use Uasoft\Badaso\Module\LMSModule\Models\Course;
use Uasoft\Badaso\Module\LMSModule\Models\CourseUser;
use Uasoft\Badaso\Module\LMSModule\Models\User;
use Uasoft\Badaso\Module\LMSModule\Helpers\Route;
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
      $user_teacher = User::factory()->create();
      $user_teacher->rawPassword = 'password';

      $user_student = User::factory()->create();
      $user_student->rawPassword = 'password';

      $course_url = route('badaso.course.add');
      AuthHelper::asUser($this, $user_teacher)->json('POST', $course_url, [
          'name' => 'Test Course',
          'subject' => 'Test Subject',
          'room' => 'Test room',
      ]);

      $course = Course::first();

      $join_url = route('badaso.course.join');
      $response = AuthHelper::asUser($this, $user_student)->json('POST', $join_url, [
          'code' => $course->join_code,
      ]);

      $response->assertStatus(200);

  }
}
