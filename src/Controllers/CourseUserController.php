<?php

namespace Uasoft\Badaso\Module\LMSModule\Controllers;

use Exception;
use Illuminate\Validation\ValidationException;
use Uasoft\Badaso\Controllers\Controller;
use Uasoft\Badaso\Helpers\ApiResponse;
use Uasoft\Badaso\Module\LMSModule\Models\Course;
use Uasoft\Badaso\Module\LMSModule\Models\CourseUser;
use Uasoft\Badaso\Module\LMSModule\Models\User;

class CourseUserController extends Controller
{
    public function view()
    {
        try {
            $user = auth()->user();

            $courseUserIds = CourseUser::where(
                'user_id', '=', $user->id
            )->pluck('course_id')->toArray();
            $courses = [];

            foreach ($courseUserIds as $courseUserId) {
                $course = Course::find($courseUserId);
                array_push($courses, $course);
            }

            foreach ($courses as $course) {
                $teacher = User::find(
                    $course->createdBy
                )->pluck('name')->toArray();
                $course->created_by = $teacher[0];
            }

            return ApiResponse::success($courses);
        } catch (Exception $e) {
            if ($e instanceof ValidationException) {
                return ApiResponse::failed($e);
            }
        }
    }
}
