<?php

namespace Uasoft\Badaso\Module\LMSModule\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Uasoft\Badaso\Controllers\Controller;
use Uasoft\Badaso\Helpers\ApiResponse;
use Uasoft\Badaso\Module\LMSModule\Helpers\CourseUserHelper;
use Uasoft\Badaso\Module\LMSModule\Models\Announcement;

class AnnouncementController extends Controller
{
    public function add(Request $request)
    {
        try {
            $user = auth()->user();
            $request->validate([
                'course_id' => 'required|integer',
                'content' => 'required|string|max:65535',
            ]);

            if (! CourseUserHelper::isUserInCourse($user->id, $request->input('course_id'))) {
                throw ValidationException::withMessages([
                    'course_id' => 'Course not found',
                ]);
            }

            $announcement = Announcement::create([
                'course_id' => $request->input('course_id'),
                'content' => $request->input('content'),
                'created_by' => $user->id,
            ]);

            return ApiResponse::success($announcement);
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

            $announcements = Announcement::where('course_id', $request->query('course_id'))
                ->orderBy('created_at', 'desc')
                ->get()
                ->toArray();

            return ApiResponse::success($announcements);
        } catch (Exception $e) {
            return ApiResponse::failed($e);
        }
    }

    public function edit(Request $request, $id)
    {
        try {
            $request->validate([
                'content' => 'required|string|max:65535',
            ]);

            if (! $announcement = Announcement::find($id)) {
                throw ValidationException::withMessages([
                    'id' => 'Announcement not found',
                ]);
            }
        } catch (Exception $e) {
            return ApiResponse::failed($e);
        }
    }
}
