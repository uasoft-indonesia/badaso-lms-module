<?php

namespace Uasoft\Badaso\Module\LMSModule\Tests\Feature;

use Tests\TestCase;
use Uasoft\Badaso\Module\LMSModule\Models\User;
use Uasoft\Badaso\Module\LMSModule\Tests\Helpers\AuthHelper;

class ViewApiTest extends TestCase
{
    public function testCreateCommentWithoutLoginExpectResponse401()
    {
        $url = route('badaso.announcement.comment');
        $response = $this->json('POST', $url);
        $response->assertStatus(401);
    }

    public function testCreateCommentInNonExistingAnnouncementExpectResponse400()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $url = $url = route('badaso.announcement.comment');
        $response = AuthHelper::asUser($this, $user)->json('POST', $url, [
            'announcement_id' => 1,
            'content' => 'Test content',
        ]);

        $response->assertStatus(400);
    }
}
