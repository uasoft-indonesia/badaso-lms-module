<?php

namespace Uasoft\Badaso\Module\LMSModule\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Uasoft\Badaso\Controllers\Controller;
use Uasoft\Badaso\Helpers\ApiResponse;
use Uasoft\Badaso\Module\LMSModule\Helpers\Token;
use Uasoft\Badaso\Module\LMSModule\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email',
                'username' => 'required|string|max:255',
                'password' => 'required|string|max:255',
                'password_confirmation' => 'required|string|max:255',
            ]);

            if ($request->input('password') != $request->input('password_confirmation')) {
                return ApiResponse::failed('Password does not match, please try again');
            }

            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'username' => $request->input('username'),
                'password' => Hash::make($request->input('password')),
            ]);

            DB::commit();

            return ApiResponse::success($user);
        } catch (Exception $e) {
            DB::rollBack();

            if ($e instanceof ValidationException) {
                return ApiResponse::failed($e);
            }

            return ApiResponse::failed('Failed to register, please try again');
        }
    }

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
