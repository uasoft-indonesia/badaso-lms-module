<?php

namespace Uasoft\Badaso\Module\LMSModule\Tests\Feature;

use Uasoft\Badaso\Module\LMSModule\Helpers\Route;
use Tests\TestCase;

class LMSJoinClassTest extends TestCase
{

  public function testJoinClassWithoutLoginExpectResponseStatus401()
  {
    $url = route('badaso.course.join');
    $response = $this->json('POST', $url);
    $response->assertStatus(401);
  }

  public function testJoinClassAsAuthorizedUserWithUnknownClassCodeExpectResponseStatus404()
  {
      $url = route('badaso.course.join');
      $response = $this->json('POST', $url, [
          'code' => 'xxx'
      ]);
      $response->assertStatus(404);
  }


}
