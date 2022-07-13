<?php

namespace Uasoft\Badaso\Module\LMSModule\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Uasoft\Badaso\Controllers\Controller;
use Uasoft\Badaso\Helpers\ApiResponse;
use Uasoft\Badaso\Module\LMSModule\Helpers\CourseUserHelper;
use Uasoft\Badaso\Module\LMSModule\Models\CourseUser;
use Uasoft\Badaso\Module\LMSModule\Models\LessonMaterial;
use Uasoft\Badaso\Module\LMSModule\Models\MaterialComment;

class MaterialCommentController extends Controller
{
    public function add(Request $request)
    {
        try {
            $user = auth()->user();
            $request->validate([
                'material_id' => 'required|integer',
                'content' => 'required|string|max:65535',
            ]);

            $lessonMaterial = LessonMaterial::where('id', $request->input('material_id'))
                ->first();

            if (! $lessonMaterial) {
                throw ValidationException::withMessages([
                    'material_id' => 'Lesson Material not found',
                ]);
            }

            if (! CourseUserHelper::isUserInCourse(
                $user->id,
                $lessonMaterial?->course_id,
            )) {
                throw ValidationException::withMessages([
                    'id' => 'Must enroll the course to edit the announcement',
                ]);
            }

            $materialComment = MaterialComment::create([
                'material_id' => $request->input('material_id'),
                'content' => $request->input('content'),
                'created_by' => $user->id,
            ]);

            return ApiResponse::success($materialComment->toArray());
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

            $materialComment = MaterialComment::with([
                'lessonMaterial:id,course_id',
            ])->find($id);

            if (! CourseUserHelper::isUserInCourse($user->id, $materialComment->lessonMaterial->course_id)) {
                throw ValidationException::withMessages([
                    'id' => 'Must enroll the course to edit the comment',
                ]);
            }

            $materialComment->fill($request->only([
                'content',
            ]))->save();

            return ApiResponse::success($materialComment->toArray());
        } catch (Exception $e) {
            return ApiResponse::failed($e);
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            $user = auth()->user();

            $materialComment = MaterialComment::with([
                'lessonMaterial:id,course_id',
            ])->find($id);

            if (! CourseUserHelper::isUserInCourse($user->id, $materialComment->lessonMaterial->course_id)) {
                throw ValidationException::withMessages([
                    'id' => 'Must enroll the course to edit the comment',
                ]);
            }

            $courseuser = CourseUser::where('user_id', '=', $user->id)
                ->where('course_id', '=', $materialComment->lessonMaterial->course_id)
                ->first();

            if ($courseuser->role == 'teacher') {
                $materialComment->delete();
            } else {
                if ($materialComment->created_by == $user->id) {
                    $materialComment->delete();
                } else {
                    return ApiResponse::unauthorized();
                }
            }

            return ApiResponse::success();
        } catch (Exception $e) {
            return ApiResponse::failed($e);
        }
    }
}
