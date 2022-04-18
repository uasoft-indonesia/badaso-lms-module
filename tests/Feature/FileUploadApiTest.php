<?php

namespace Uasoft\Badaso\Module\LMSModule\Tests\Feature;

use Illuminate\Http\UploadedFile;
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

        $file = UploadedFile::fake()->create('file.pdf');

        $url = route('badaso.file.upload');
        $response = AuthHelper::asUser($this, $user)->json('POST', $url, [
            'file' => $file,
        ]);

        $response->assertStatus(200);
    }

    public function testDeleteFileWithoutLogin()
    {
        $url = route('badaso.file.delete', [
            'fileName' => 'new.pdf',
        ]);
        $response = $this->json('DELETE', $url);
        $response->assertStatus(401);
    }

    public function testDeleteFileWithoutFileNameGiven()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $url = route('badaso.file.delete', [
            'fileName' => 'new.pdf',
        ]);
        $response = AuthHelper::asUser($this, $user)->json('DELETE', $url);

        $response->assertStatus(500);
    }

    public function testDeleteFileSuccesfully()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        Storage::fake('public');
        UploadedFile::fake()->create('file.pdf')->storeAs('files', 'file.pdf');

        $url = route('badaso.file.delete', [
            'fileName' => 'file.pdf',
        ]); 
        $response = AuthHelper::asUser($this, $user)->json('DELETE', $url);

        $response->assertStatus(200);
    }
}
