<?php

namespace Uasoft\Badaso\Module\LMSModule\Tests\Feature;

use Tests\TestCase;
use Uasoft\Badaso\Module\LMSModule\Models\Course;
use Uasoft\Badaso\Module\LMSModule\Models\User;

class BadasoCourseApiTest extends TestCase
{
    public function testCreateCourseWithoutLoginExpectResponse401()
    {
        $url = route('badaso.course.add');
        $response = $this->json('POST', $url);
        $response->assertStatus(401);
    }

}
