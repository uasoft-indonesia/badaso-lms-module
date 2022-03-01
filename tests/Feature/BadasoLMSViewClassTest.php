<?php

namespace Uasoft\Badaso\Module\LMS\Tests\Feature;

class BadasoLMSViewClassTest extends TestCase
{
    public static int $EXISTING_CLASS_ID = 1;
    public static int $NON_EXISTING_CLASS_ID = 100;
    public static int $USER_ID = 232;

    public function testStartInit()
    {
        // init user login as normal user
        CallLMSHelperTest::handelUserAuthorize($this);
    }
    
    public function testClassIsExist()
    {
        global $EXISTING_CLASS_ID;
        $response = CallLMSHelperTest::withAuthorizeBearer($this)->json('GET', CallLMSHelperTest::getUrlApiV1Prefix("/class/{$EXISTING_CLASS_ID}"));
        $this->assertNotEmpty($response);
    }

    public function testClassIsNotExist()
    {
        global $NON_EXISTING_CLASS_ID;
        $response = CallLMSHelperTest::withAuthorizeBearer($this)->json('GET', CallLMSHelperTest::getUrlApiV1Prefix("/class/{$NON_EXISTING_CLASS_ID}"));
        $this->assertEmpty($response);
    }

    public function testUserBelongInClass() 
    {
        global $EXISTING_CLASS_ID, $USER_ID;
        $response = CallLMSHelperTest::withAuthorizeBearer($this)->json('GET', CallLMSHelperTest::getUrlApiV1Prefix("/user-class/{$EXISTING_CLASS_ID}"));
        $this->assertTrue($response['user_id'] == $USER_ID);
    }

    public function testUserHasClasses()
    {
        global $USER_ID;
        $response = CallLMSHelperTest::withAuthorizeBearer($this)->json('GET', CallLMSHelperTest::getUrlApiV1Prefix("/user-class/{$USER_ID}"));
        $this->assertNotEmpty($response);
    }

    public function testUserHasNoClass()
    {
        global $USER_ID;
        $response = CallLMSHelperTest::withAuthorizeBearer($this)->json('GET', CallLMSHelperTest::getUrlApiV1Prefix("/user-class/{$USER_ID}"));
        $this->assertEmpty($response);
    }
}