<?php

namespace Uasoft\Badaso\Module\LMSModule\Tests\Unit;

use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Uasoft\Badaso\Module\LMSModule\Models\LMSUsers;

class UserLoginTest extends TestCase
{

    public function testStarterInit()
    {
        $create_user = [
            'full_name' => 'Nofaldi Atmam',
            'username' => 'nofamex',
            'email' => 'nsiren@gmail.com',
            'password' => Hash::make('password'),
        ];
        
        return LMSUsers::creaate($create_user);
    }
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testUserExist()
    {

        $existing_user = LMSUsers::where('username', $user['username'])->first();

        $this->assertEquals($existing_user['id'], $user['id']);
    }
}
