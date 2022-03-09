<?php

namespace Uasoft\Badaso\Module\LMSModule\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Uasoft\Badaso\Controllers\Controller;
use Uasoft\Badaso\Helpers\ApiResponse;
use Uasoft\Badaso\Module\LMSModule\Helpers\Token;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $credentials = [
                'email'    => $request->email,
                'password' => $request->password,
            ];
            $request->validate([
                'email' => ['required'],
                'password' => ['required'],
            ]);

            $token = Auth::attempt($credentials);

            if ($token) {
                $data = Token::createNewToken($token, Auth::user());
                
                return ApiResponse::success($data);
            }

            return ApiResponse::failed('authentication failed');
        } catch (JWTException $e) {
            return ApiResponse::failed($e);
        } catch (Exception $e) {
            return ApiResponse::failed($e);
        }
    }
}
