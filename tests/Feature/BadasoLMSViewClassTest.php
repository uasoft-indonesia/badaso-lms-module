<?php

namespace Uasoft\Badaso\Module\LMS\Tests\Feature;

class BadasoLMSViewClassTest extends TestCase
{
    public static int $EXISTING_CLASS_ID = 1;
    public static int $NON_EXISTING_CLASS_ID = 100;
    public static string $CLASS_CODE = 'CLASS_CODE';
    public static string $WRONG_CLASS_CODE = 'WRONG_CLASS_CODE';

    public function testStartInit()
    {
        // init user login as normal user
        CallLMSHelperTest::handelUserAuthorize($this);
    }
}