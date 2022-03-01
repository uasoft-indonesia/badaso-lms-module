<?php

namespace Uasoft\Badaso\Module\LMS\Tests\Feature;
use Tests\TestCase;
use Uasoft\Badaso\Helpers\CallHelperTest;

class ViewAndShareClassCodeTest extends TestCase {
    public function testClassCodeExist() {
        // init user login as normal user
        CallHelperTest::handleUserAdminAuthorize($this);
    }
}