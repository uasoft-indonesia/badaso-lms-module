<?php

namespace Uasoft\Badaso\Module\LMS\Tests\Feature;

use Tests\TestCase;
use Uasoft\Badaso\Module\LMS\Helpers\CallLMSHelperTest;

class LMSJoinClassTest extends TestCase
{
    public function testStartInit()
    {
        CallLMSHelperTest::handleUserAuthorize($this);
    }
}
