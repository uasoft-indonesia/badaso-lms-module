<?php

namespace Uasoft\Badaso\Module\LMSModule\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Uasoft\Badaso\Controllers\Controller;
use Uasoft\Badaso\Helpers\ApiResponse;
use Uasoft\Badaso\Module\LMSModule\Models\User;
use Illuminate\Support\Facades\Hash;


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

            $user = User::factory()->create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'username' => $request->input('username'),
                'password' => Hash::make($request->input('password')),
            ]);

            DB::commit();

            return ApiResponse::success($user);
        } catch (Exception $e) {
            DB::rollBack();

            if($e instanceof ValidationException) {
                return ApiResponse::failed($e);
            }

            return ApiResponse::failed('Failed to register, please try again');
        }
    }
}
