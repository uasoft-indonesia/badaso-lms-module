<?php

namespace Uasoft\Badaso\Module\LMS\Tests\Feature;

use Tests\TestCase;
use Uasoft\Badaso\Module\LMSModule\Helpers\Route;

class LMSJoinClassTest extends TestCase
{

    public static string $VALID_CLASS_CODE;
    public static string $INVALID_CLASS_CODE;

    public function testClassIsExist()
    {
        $response = Route::getController('ClassController@...');
        $response->assertSuccessful();

        $this->assertNotNull($response);
    }

    public function testClassCodeValid()
    {
        global $VALID_CLASS_CODE;
        $class = Route::getController('ClassController@...');
        $class->assertSuccessful();

        $this->assertEquals($VALID_CLASS_CODE, $class['code']);
    }

    public function testClassCodeInvalid()
    {
        global $INVALID_CLASS_CODE;
        $class = Route::getController('ClassController@...');
        $class->assertSuccessful();

        $this->assertNotEquals($INVALID_CLASS_CODE, $class['code']);
    }


}
