<?php

namespace Uasoft\Badaso\Module\LMSModule\Tests\Feature;

use Uasoft\Badaso\Module\LMSModule\Helpers\Route;
use Uasoft\Badaso\Module\LMSModule\Tests\TestCase;

class LMSJoinClassTest extends TestCase
{

  public function testJoinClassWithoutLoginExpectResponseStatus401()
  {
    $url = route('badaso.course.join');
    $response = $this->json('POST', $url);
    $response->assertStatus(401);
  }


}
