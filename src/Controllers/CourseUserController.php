<?php

namespace Uasoft\Badaso\Module\LMSModule\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Uasoft\Badaso\Controllers\Controller;
use Uasoft\Badaso\Helpers\ApiResponse;
use Uasoft\Badaso\Module\LMSModule\Models\CourseUser;

class CourseUserController extends Controller
{
    public function view(Request $request)
    {
        try {
            $user = auth()->user();

            $courseUsers = CourseUser::where(
                'user_id', '=', $user->id
            )->get();


            return ApiResponse::success($courseUsers);
        } catch (Exception $e) {
            if ($e instanceof ValidationException){
                
                return ApiResponse::failed($e);
            }
        }
    }
}
