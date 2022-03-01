<?php

namespace Uasoft\Badaso\Module\LMSModule\Tests\Feature;

use Tests\TestCase;

class UserLoginTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testUserLogin()
    {
        $response = $this->post('/', [
            'fullName' => 'Nofaldi Atmam',
            'userame' => 'nofamex',
            'email' => 'nsiren@gmail.com',
            'password' => 'password', 
        ]);

        $response->assertStatus(200);
    }
}
