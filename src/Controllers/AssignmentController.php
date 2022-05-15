<?php

namespace Uasoft\Badaso\Module\LMSModule\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Uasoft\Badaso\Controllers\Controller;
use Uasoft\Badaso\Helpers\ApiResponse;
use Uasoft\Badaso\Module\LMSModule\Enums\CourseUserRole;
use Uasoft\Badaso\Module\LMSModule\Helpers\CourseUserHelper;

class AssignmentController extends Controller
{
    public function add(Request $request)
    {
        try {
            $user = auth()->user();

            if (!CourseUserHelper::isUserInCourse(
                $user->id,
                $request->input('course_id'),
                CourseUserRole::TEACHER,
            )) {
                throw ValidationException::withMessages([
                    'course_id' => 'Course not found',
                ]);
            }
        } catch (Exception $e) {
            return ApiResponse::failed($e);
        }


    }
}
