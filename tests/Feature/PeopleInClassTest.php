<?php

namespace Uasoft\Badaso\Module\LMS\Tests\Feature;

use Tests\TestCase;
use Uasoft\Badaso\Helpers\CallHelperTest;

class PeopleInClassTest extends TestCase
{
    public function testStartInit()
    {
        // init user login as normal user
        CallHelperTest::handleUserAdminAuthorize($this);
    }
}
