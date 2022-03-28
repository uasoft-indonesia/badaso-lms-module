<?php

namespace Uasoft\Badaso\Module\LMSModule\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Uasoft\Badaso\Controllers\Controller;
use Uasoft\Badaso\Helpers\ApiResponse;
use Uasoft\Badaso\Module\LMSModule\Helpers\CourseUserHelper;
use Uasoft\Badaso\Module\LMSModule\Models\Topic;

class TopicController extends Controller
{
    public function add(Request $request)
    {
        try {
            $user = auth()->user();
            $request->validate([
                'course_id' => 'required|integer',
                'title' => 'required|string|max:65535',
            ]);

            if (! CourseUserHelper::isUserInCourse($user->id, $request->input('course_id'))) {
                throw ValidationException::withMessages([
                    'course_id' => 'Course not found',
                ]);
            }

            $topic = Topic::create([
                'course_id' => $request->input('course_id'),
                'title' => $request->input('title'),
                'created_by' => $user->id,
            ]);

            return ApiResponse::Success($topic->toArray());
        } catch (Exception $e) {
            return ApiResponse::failed($e);
        }
    }

    public function browse(Request $request)
    {
        try {
            $request->validate([
                'course_id' => 'required|integer',
            ]);

            $user = auth()->user();
            if (! CourseUserHelper::isUserInCourse($user->id, $request->query('course_id'))) {
                throw ValidationException::withMessages([
                    'course_id' => 'Course not found',
                ]);
            }

            $topic = Topic::where('course_id', $request->query('course_id'))
                ->orderBy('created_at', 'desc')
                ->get();

            return ApiResponse::success($topic->toArray());
        } catch (Exception $e) {
            return ApiResponse::failed($e);
        }
    }
}
