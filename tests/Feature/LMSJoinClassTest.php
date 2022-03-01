<?php

namespace Uasoft\Badaso\Module\LMS\Tests\Feature;

use Tests\TestCase;
use Uasoft\Badaso\Module\LMSModule\Helpers\Route;

class LMSJoinClassTest extends TestCase
{
    public function testClassIsExist()
    {
        $response = Route::getController('ClassController@...');
        $response->assertSuccessful();

        $this->assertNotNull($response);
    }
}
