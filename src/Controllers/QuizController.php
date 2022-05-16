<?php

namespace Uasoft\Badaso\Module\LMSModule\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Uasoft\Badaso\Controllers\Controller;
use Uasoft\Badaso\Helpers\ApiResponse;
use Uasoft\Badaso\Module\LMSModule\Enums\CourseUserRole;
use Uasoft\Badaso\Module\LMSModule\Helpers\CourseUserHelper;
use Uasoft\Badaso\Module\LMSModule\Models\Quiz;

class QuizController extends Controller
{
    public function add(Request $request)
    {
        try {
            $user = auth()->user();
            $request->validate([
                'course_id' => 'required|integer',
                'topic_id' => 'nullable|integer|exists:Uasoft\Badaso\Module\LMSModule\Models\Topic,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:65535',
                'start_time' => 'nullable|date_format:Y-m-d H:i:sP',
                'end_time' => 'nullable|date_format:Y-m-d H:i:sP',
                'duration' => 'nullable|integer',
            ]);

            if (! CourseUserHelper::isUserInCourse(
                $user->id,
                $request->input('course_id'),
                CourseUserRole::TEACHER,
            )) {
                throw ValidationException::withMessages([
                    'course_id' => 'Course not found',
                ]);
            }

            $quiz = Quiz::create([
                'course_id' => $request->input('course_id'),
                'topic_id' => $request->input('topic_id'),
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'start_time' => $request->input('start_time'),
                'end_time' => $request->input('end_time'),
                'duration' => $request->input('duration'),
                'created_by' => $user->id,
            ]);

            return ApiResponse::success($quiz->toArray());
        } catch (Exception $e) {
            return ApiResponse::failed($e);
        }
    }
}