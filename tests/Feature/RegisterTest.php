<?php

namespace Uasoft\Badaso\Module\LMS\Tests\Feature;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;
use Uasoft\Badaso\Helpers\CallHelperTest;
use Uasoft\Badaso\Module\LMSModule\Models\User;

class RegisterTest extends TestCase
{
    public function testStartInit()
    {
        CallHelperTest::handleUserAdminAuthorize($this);
    }

    public function testAddUserDatabase()
    {
        $name = Str::random(10);
        $create_user = [
            'name' => $name,
            'username' => $name,
            'email' => $name.'@gmail.com',
            'password' => Hash::make($name),
        ];
        $user = User::create($create_user);
        $this->assertEquals($create_user['name'], $user->name);
        $this->assertEquals($create_user['username'], $user->username);
        $this->assertEquals($create_user['email'], $user->email);
        $this->assertEquals($create_user['password'], $user->password);
    }

    public function testAddUser()
    {
        $name = Str::random(10);
        $password = Hash::make($name);
        $create_user = [
            'name' => $name,
            'username' => $name,
            'email' => $name.'@gmail.com',
            'password' => $password,
            'password_confirmation' => $password,
        ];

        $response = $this->json('POST', route('badaso.auth.register'), $create_user);
        $response->assertStatus(200);
    }

    public function testAddUserWithNotEnoughParameter()
    {
        $name = Str::random(10);
        $password = Hash::make($name);
        $create_user = [
            'name' => $name,
            'username' => $name,
            'email' => $name.'@gmail.com',
            'password' => $password,
        ];

        $response = $this->json('POST', route('badaso.auth.register'), $create_user);
        $response->assertStatus(400);
    }

    public function testAddUserWithExistingUsername()
    {
        $name = Str::random(10);
        $password = Hash::make($name);
        $create_user = [
            'name' => $name,
            'username' => $name,
            'email' => $name.'@gmail.com',
            'password' => $password,
        ];

        User::factory()->create([
            'name' => 'test',
            'username' => $name,
            'email' => 'test@gmail.com',
            'password' => $password,
        ]);

        $response = $this->json('POST', route('badaso.auth.register'), $create_user);
        $response->assertStatus(400);
    }

    public function testAddUserWithExistingEmail()
    {
        $name = Str::random(10);
        $password = Hash::make($name);
        $create_user = [
            'name' => $name,
            'username' => $name,
            'email' => $name.'@gmail.com',
            'password' => $password,
        ];

        User::factory()->create([
            'name' => 'test',
            'username' => 'test',
            'email' => $name.'@gmail.com',
            'password' => $password,
        ]);

        $response = $this->json('POST', route('badaso.auth.register'), $create_user);
        $response->assertStatus(400);
    }
}
