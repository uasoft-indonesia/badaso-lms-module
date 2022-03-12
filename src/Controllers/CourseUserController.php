<?php

namespace Uasoft\Badaso\Module\LMSModule\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Uasoft\Badaso\Controllers\Controller;
use Uasoft\Badaso\Helpers\ApiResponse;

use Uasoft\Badaso\Module\LMSModule\Models\User;
use Uasoft\Badaso\Module\LMSModule\Models\Course;
use Uasoft\Badaso\Module\LMSModule\Models\CourseUser;

class CourseUserController extends Controller
{
    public function view()
    {
        try {
            $user = auth()->user();

            $courseUsers = CourseUser::where(
                'user_id', '=', $user->id
            )->pluck('course_id')->toArray();

            $courses = [];

            foreach ($courseUsers as $cid) 
            {
                $course = Course::find($cid);
                array_push($courses, $course);
            }

            foreach ($courses as $crs)
            {
                $teacher = User::find(
                    $crs->createdBy
                )->pluck('name')->toArray();
                
                $crs->created_by = $teacher[0];
            }
            
            // return $courses;
            return ApiResponse::success($courses);
        } catch (Exception $e) {
            if ($e instanceof ValidationException){
                
                return ApiResponse::failed($e);
            }
        }
    }
}
