<?php

namespace Uasoft\Badaso\Module\LMSModule\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use stdClass;
use TokenHelper;
use Tymon\JWTAuth\Exceptions\JWTException;
use Uasoft\Badaso\Controllers\Controller;
use Uasoft\Badaso\Helpers\ApiResponse;

class AuthController extends Controller 
{
    public function login(Request $request)
    {
        try {
            $remember = $request->get('remember', false);
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
                $data =  TokenHelper::createNewToken($token, Auth::user(), $remember);
                return ApiResponse::success($data);
            }

            return ApiResponse::failed("authentication failed");
        } catch (JWTException $e) {
            return ApiResponse::failed($e);
        } catch (Exception $e) {
            return ApiResponse::failed($e);
        }
    }
}
