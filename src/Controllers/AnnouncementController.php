<?php

namespace Uasoft\Badaso\Module\LMSModule\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Uasoft\Badaso\Controllers\Controller;
use Uasoft\Badaso\Helpers\ApiResponse;
use Uasoft\Badaso\Module\LMSModule\Helpers\CourseUserHelper;
use Uasoft\Badaso\Module\LMSModule\Models\Announcement;
use Uasoft\Badaso\Module\LMSModule\Models\Comment;

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

            if (!CourseUserHelper::isUserInCourse($user->id, $request->input('course_id'))) {
                throw ValidationException::withMessages([
                    'course_id' => 'Course not found',
                ]);
            }

            $announcement = Announcement::create([
                'course_id' => $request->input('course_id'),
                'content' => $request->input('content'),
                'created_by' => $user->id,
            ]);

            return ApiResponse::success($announcement->toArray());
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
            if (!CourseUserHelper::isUserInCourse($user->id, $request->query('course_id'))) {
                throw ValidationException::withMessages([
                    'course_id' => 'Course not found',
                ]);
            }

            $announcements = Announcement::where('course_id', $request->query('course_id'))
                ->join('badaso_users', 'badaso_users.id', '=', 'badaso_announcements.created_by')
                ->orderBy('badaso_announcements.created_at', 'desc')
                ->select('badaso_announcements.*', 'badaso_users.name as author')
                ->get();

            foreach ($announcements as $announcement) {
                $comments = Comment::where('announcement_id', '=', $announcement->id)
                    ->orderBy('created_at', 'asc')
                    ->get()
                    ->toArray();
                $announcement->comments = $comments;
            }

            return ApiResponse::success($announcements->toArray());
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

            $user = auth()->user();
            $announcement = Announcement::where('id', $id)
                ->where('created_by', $user->id)
                ->first();

            if (!$announcement) {
                throw ValidationException::withMessages([
                    'id' => 'Announcement not found',
                ]);
            }

            if (!CourseUserHelper::isUserInCourse($user->id, $announcement->course_id)) {
                throw ValidationException::withMessages([
                    'id' => 'Must enroll the course to edit the announcement',
                ]);
            }

            $announcement->content = $request->input('content');
            $announcement->save();

            return ApiResponse::success($announcement->toArray());
        } catch (Exception $e) {
            return ApiResponse::failed($e);
        }
    }

    public function delete($id)
    {
        try {
            $user = auth()->user();

            $announcement = Announcement::find($id);
            if (! $announcement) {
                throw ValidationException::withMessages([
                    'id' => 'Announcement not found',
                ]);
            }

            $course = $announcement->course;
            if (! ($announcement->created_by == $user->id || $course->created_by == $user->id)) {
                throw ValidationException::withMessages([
                    'id' => 'Announcement not found',
                ]);
            }

            if (!CourseUserHelper::isUserInCourse($user->id, $announcement->course_id)) {
                throw ValidationException::withMessages([
                    'id' => 'Must enroll the course to edit the announcement',
                ]);
            }

            $announcement->delete();

            return ApiResponse::success($announcement->toArray());
        } catch (Exception $e) {
            return ApiResponse::failed($e);
        }
    }
}
