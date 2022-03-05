<?php

namespace Uasoft\Badaso\Module\LMS\Tests\Feature;

use Tests\TestCase;
use Uasoft\Badaso\Helpers\CallHelperTest;

class PeopleInClassTest extends TestCase
{
    public static int $USER_ID = 240;
    public static int $TEACHER_ID = 245;
    public static int $STUDENT_ID = 250;

    public function testStartInit()
    {
        // init user login as normal user
        CallHelperTest::handleUserAdminAuthorize($this);
    }

    public function testTeacherIsExist()
    {
        $response = CallHelperTest::withAuthorizeBearer($this)->json('GET', CallHelperTest::getUrlApiV1Prefix("/role/{$TEACHER_ID}"));
        $this->assertEmpty($response);
    }

    public function testStudentIsExist()
    {
        $response = CallHelperTest::withAuthorizeBearer($this)->json('GET', CallHelperTest::getUrlApiV1Prefix("/role/{$STUDENT_ID}"));
        $this->assertEmpty($response);
    }
}
