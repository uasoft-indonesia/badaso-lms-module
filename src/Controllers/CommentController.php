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

class CommentController extends Controller
{
    public function add(Request $request)
    {
        try {
            $user = auth()->user();
            $request->validate([
                'announcement_id' => 'required|integer',
                'content' => 'required|string|max:65535',
            ]);

            $announcement = Announcement::where('id', $request->input('announcement_id'))
                ->first();

            if (! $announcement) {
                throw ValidationException::withMessages([
                    'announcement_id' => 'Announcement not found',
                ]);
            }

            $comment = Comment::create([
                'announcement_id' => $request->input('announcement_id'),
                'content' => $request->input('content'),
                'created_by' => $user->id,
            ]);

            return ApiResponse::success($comment->toArray());
        } catch (Exception $e) {
            return ApiResponse::failed($e);
        }
    }

    public function edit(Request $request, $id)
    {
        try {
            $user = auth()->user();
            $request->validate([
                'content' => 'required|string|max:65535',
            ]);

            $comment = Comment::where('id', $id)
                ->where('created_by', $user->id)
                ->first();
            
            if (! $comment) {
                throw ValidationException::withMessages([
                    'id' => 'Comment not found',
                ]);
            }

            $announcement = Announcement::where('id', $comment->announcement_id)
                ->first();

            if (! CourseUserHelper::isUserInCourse($user->id, $announcement->course_id)) {
                throw ValidationException::withMessages([
                    'id' => 'Must enroll the course to edit the comment',
                ]);
            }

            $comment->content = $request->input('content');
            $comment->save();

            return ApiResponse::success($comment->toArray());
        } catch (Exception $e) {
            return ApiResponse::failed($e);
        }
    }
}
