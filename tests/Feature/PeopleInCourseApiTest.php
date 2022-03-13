<?php

namespace Uasoft\Badaso\Module\LMSModule\Tests\Feature;

use Tests\TestCase;
use Uasoft\Badaso\Module\LMSModule\Models\User;
use Uasoft\Badaso\Module\LMSModule\Tests\Helpers\AuthHelper;

class PeopleInCourseApiTest extends TestCase
{
    public function testPeopleInCourseWithoutLoginExpectResponse401()
    {
        $url = route('badaso.course.people', ['id' => 1]);
        $response = $this->json('GET', $url);
        $response->assertStatus(401);
    }

    public function testPeopleInCourseAsLoggedInUserWithValidDataExpectResponse200()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $url = route('badaso.course.people', ['id' => 1]);
        $response = AuthHelper::asUser($this, $user)->json('GET', $url);
        $response->assertStatus(200);
    }
}
