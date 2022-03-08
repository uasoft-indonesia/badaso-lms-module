<?php

namespace Uasoft\Badaso\Module\LMSModule\Controllers;

use Illuminate\Http\Request;
use Uasoft\Badaso\Controllers\Controller;
use Uasoft\Badaso\Module\LMSModule\Models\Course;
class CourseController extends Controller
{
    public function add(Request $request)
    {
        $course = Course::create([
            'name' => $request->input('name'),
            'subject' => $request->input('subject'),
            'room' => $request->input('room'),
            'join_code' => Random::generate(6, '0-9A-Z'),
            'created_by' => $user->id,
        ]);
        return ApiResponse::success([
            'id' => 1,
            'name' => $request->input('name'),
            'subject' => $request->input('subject'),
            'room' => $request->input('room'),
        ]);
    }
}
