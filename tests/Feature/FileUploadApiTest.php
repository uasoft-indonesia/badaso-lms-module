<?php

namespace Uasoft\Badaso\Module\LMSModule\Tests\Feature;

use Illuminate\Http\Testing\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Uasoft\Badaso\Module\LMSModule\Models\User;
use Uasoft\Badaso\Module\LMSModule\Tests\Helpers\AuthHelper;

class FileUplaodApiTest extends TestCase
{
    public function testUploadFileWithoutLogin()
    {
        $url = route('badaso.file.upload');
        $response = $this->json('POST', $url);
        $response->assertStatus(401);
    }

    public function testUploadFileWithoutFileGiven()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $url = route('badaso.file.upload');
        $response = AuthHelper::asUser($this, $user)->json('POST', $url);

        $response->assertStatus(400);
    }

    public function testUploadFileSuccesfully()
    {   
        $user = User::factory()->create();
        $user->rawPassword = 'password';
        Storage::fake('public');

        $file = File::image('logo.pdf', 400, 100);

        $url = route('badaso.file.upload');
        $response = AuthHelper::asUser($this, $user)->json('POST', $url, [
            'file' => $file
        ]);

        $response->assertStatus(200);
    }
}
