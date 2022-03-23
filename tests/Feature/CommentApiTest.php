<?php

namespace Uasoft\Badaso\Module\LMSModule\Tests\Feature;

use Tests\TestCase;
use Uasoft\Badaso\Module\LMSModule\Models\User;
use Uasoft\Badaso\Module\LMSModule\Tests\Helpers\AuthHelper;

class ViewApiTest extends TestCase
{
    public function testCreateCommentWithoutLoginExpectResponse401()
    {
        $url = route('badaso.course.addcomment');
        $response = $this->json('GET', $url);
        $response->assertStatus(401);
    }

    public function testCreateCommentAsLoggedInUserWithValidDataExpectResponse200()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        //create announcement w factory
        $announcement_id = 0;

        $url = route('badaso.course.addcomment');

        $response = AuthHelper::asUser($this, $user)->json('POST', $url, [
            'announcement_id' => $announcement_id,
            'content' => 'Test comment'
        ]);

        $response->assertStatus(200);
    }
}
