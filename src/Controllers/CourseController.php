<?php

namespace Uasoft\Badaso\Module\LMSModule\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Nette\Utils\Random;
use Uasoft\Badaso\Controllers\Controller;
use Uasoft\Badaso\Helpers\ApiResponse;
use Uasoft\Badaso\Module\LMSModule\Enums\CourseUserRole;
use Uasoft\Badaso\Module\LMSModule\Models\Course;
use Uasoft\Badaso\Module\LMSModule\Models\CourseUser;

class CourseController extends Controller
{
    public function add(Request $request)
    {
        DB::beginTransaction();

        try {
            $user = auth()->user();

            $request->validate([
                'name' => 'required|string|max:255',
                'subject' => 'required|string|max:255',
                'room' => 'required|string|max:255',
            ]);

            $course = Course::create([
                'name' => $request->input('name'),
                'subject' => $request->input('subject'),
                'room' => $request->input('room'),
                'join_code' => Random::generate(6, '0-9A-Z'),
                'created_by' => $user->id,
            ]);

            CourseUser::create([
                'course_id' => $course->id,
                'user_id' => $user->id,
                'role' => CourseUserRole::TEACHER,
            ]);

            DB::commit();

            return ApiResponse::success($course);
        } catch (Exception $e) {
            DB::rollBack();

            if ($e instanceof ValidationException) {
                return ApiResponse::failed($e);
            }

            return ApiResponse::failed('Failed to create course, please try again');
        }
    }
}
