<?php

namespace Uasoft\Badaso\Module\LMSModule\Controllers;

use Illuminate\Http\Request;
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
        $user = auth()->user();
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
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'room' => 'required|string|max:255',
        ]);
        return ApiResponse::success([
            'id' => 1,
            'name' => $request->input('name'),
            'subject' => $request->input('subject'),
            'room' => $request->input('room'),
        ]);
    }
}
