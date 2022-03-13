<?php

namespace Uasoft\Badaso\Module\LMSModule\Tests\Feature;

use Tests\TestCase;
use Uasoft\Badaso\Module\LMSModule\Models\User;
use Uasoft\Badaso\Module\LMSModule\Tests\Helpers\AuthHelper;

class ViewApiTest extends TestCase
{
    public function testViewCourseWithoutLoginExpectResponse401()
    {
        $url = route('badaso.courseuser.view');
        $response = $this->json('GET', $url);
        $response->assertStatus(401);
    }

    public function testViewCourseAsLoggedInUserWithValidDataExpectResponse200()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $url = route('badaso.courseuser.view');
        $response = AuthHelper::asUser($this, $user)->json('GET', $url);
        $response->assertStatus(200);
    }
}
