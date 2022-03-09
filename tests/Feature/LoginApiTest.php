<?php

namespace Uasoft\Badaso\Module\LMSModule\Tests\Feature;

use Tests\TestCase;
use Uasoft\Badaso\Module\LMSModule\Models\User;

class LoginApiTest extends TestCase
{
    public function testLoginRouteExist()
    {
        $url = route('badaso.auth.login');
        $response = $this->json('POST', $url);
        $response->assertStatus(400);
    }

    public function testLoginWithFailedValidation()
    {
        $url = route('badaso.auth.login');

        $response = $this->json('POST', $url, [
            'email' => 'test@email.com',
        ]);
        $response->assertStatus(400);
    }

    public function testLoginWithWrongCredential()
    {
        $user = User::factory()->create();
        $url = route('badaso.auth.login');
        $response = $this->json('POST', $url, [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $data = $response->json('data');
        $errorMessage = $response->json('message');

        $response->assertStatus(500);
        $this->assertNull($data);
        $this->assertEquals($errorMessage, 'authentication failed');
    }

    public function testLoginSuccesfully()
    {
        $user = User::factory()->create();
        $user->rawPassword = 'password';

        $url = route('badaso.auth.login');
        $response = $this->json('POST', $url, [
            'email' => $user->email,
            'password' => $user->rawPassword,
        ]);

        $data = $response->json('data');

        $response->assertStatus(200);
        $this->assertNotNull($data['accessToken']);
        $this->assertEquals($data['tokenType'], 'bearer');
        $this->assertEquals($data['user']['name'], $user->name);
    }
}
