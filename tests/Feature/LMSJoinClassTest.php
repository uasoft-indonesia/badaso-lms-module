<?php

namespace Uasoft\Badaso\Module\LMSModule\Tests\Feature;

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




}
