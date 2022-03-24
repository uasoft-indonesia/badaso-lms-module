<?php

namespace Uasoft\Badaso\Module\LMSModule\Tests\Feature;

use Tests\TestCase;

class AnnonuncementApiTest extends TestCase
{
    public function testCreateAnnouncementWithoutLoginExpectResponse401()
    {
        $url = route('badaso.announcement.add');
        $response = $this->json('POST', $url);
        $response->assertStatus(401);
    }
}
