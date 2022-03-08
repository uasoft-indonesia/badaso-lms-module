<?php

namespace Uasoft\Badaso\Module\LMSModule\Controllers;

use Illuminate\Http\Request;
use Uasoft\Badaso\Controllers\BadasoAuthController;
use Uasoft\Badaso\Controllers\Controller;

class AuthController extends Controller 
{
    public function login(Request $request)
    {
        $badasoLogin = new BadasoAuthController();
        $badasoLogin->login($request);
    }
}
