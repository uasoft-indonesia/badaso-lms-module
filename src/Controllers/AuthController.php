<?php

namespace Uasoft\Badaso\Module\LMSModule\Controllers;

use Illuminate\Http\Request;
use Uasoft\Badaso\Controllers\Controller;
use Uasoft\Badaso\Helpers\ApiResponse;
use Uasoft\Badaso\Module\LMSModule\Models\User;

class AuthController extends Controller 
{
    public function login(Request $request)
    {
        $credentials = [
            'username' => $request->username,
            'password' => $request->password,
        ];
        
        $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        $user = User::where('username', '=', $credentials['username'])->firstOrFail();

        return ApiResponse::success($user->toArray());
    }
}
