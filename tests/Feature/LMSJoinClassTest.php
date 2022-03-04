<?php

namespace Uasoft\Badaso\Module\LMSModule\Tests\Feature;

use Uasoft\Badaso\Module\LMSModule\Helpers\Route;
use Uasoft\Badaso\Module\LMSModule\Tests\TestCase;

class LMSJoinClassTest extends TestCase
{

    public static string $VALID_CLASS_CODE = '12345';
    public static string $INVALID_CLASS_CODE;
    public static int $TEST_COURSE_ID = 1;

    public function testJoinClassWithoutLoginExpectResponseStatus401()
    {
        global $VALID_CLASS_CODE;
        global $TEST_COURSE_ID;

        $course = $this->getJson("/course/{$TEST_COURSE_ID}");

        $response = $this->postJson('/courses/join', [
            'id' => $course['id'],
            'code' => $VALID_CLASS_CODE,
        ]);

        $response->assertStatus(401);
    }


}
