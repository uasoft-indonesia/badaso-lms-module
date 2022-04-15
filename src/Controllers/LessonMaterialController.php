<?php

namespace Uasoft\Badaso\Module\LMSModule\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Uasoft\Badaso\Controllers\Controller;
use Uasoft\Badaso\Helpers\ApiResponse;
use Uasoft\Badaso\Module\LMSModule\Enums\CourseUserRole;
use Uasoft\Badaso\Module\LMSModule\Helpers\CourseUserHelper;
use Uasoft\Badaso\Module\LMSModule\Models\LessonMaterial;

class LessonMaterialController extends Controller
{
    public function add(Request $request)
    {
        try {
            $user = auth()->user();
            $request->validate([
                'course_id' => 'required|integer',
                'title' => 'required|string|max:255',
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

            $lessonMaterial = LessonMaterial::create([
                'course_id' => $request->input('course_id'),
                'topic_id' => $request->input('topic_id'),
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'file_url' => $request->input('file_url'),
                'link_url' => $request->input('link_url'),
                'created_by' => $user->id,
            ]);

            return ApiResponse::success($lessonMaterial->toArray());
        } catch (Exception $e) {
            return ApiResponse::failed($e);
        }
    }

    public function read($id)
    {
        try {
            $user = auth()->user();

            $lessonMaterial = LessonMaterial::with([
                'createdBy:id,name',
                'topic:id,title',
            ])->find($id);

            if (! CourseUserHelper::isUserInCourse(
                $user->id,
                $lessonMaterial?->course_id,
            )) {
                throw ValidationException::withMessages([
                    'id' => 'Lesson material not found',
                ]);
            }

            return ApiResponse::success(
                $lessonMaterial
                    ->makeHidden(['topic_id'])
                    ->toArray()
            );
        } catch (Exception $e) {
            return ApiResponse::failed($e);
        }
    }

    public function edit(Request $request, $id)
    {
        try {
            $request->validate([
                'title' => 'string|max:255',
                'content' => 'string|max:65535',
                'file_url' => 'string|max:65535',
                'link_url' => 'string|max:65535',
            ]);

            $user = auth()->user();

            $lessonMaterial = LessonMaterial::find($id);
            if ($lessonMaterial?->created_by != $user->id) {
                throw ValidationException::withMessages([
                    'id' => 'Lesson material not found',
                ]);
            }

            if (! CourseUserHelper::isUserInCourse(
                $user->id,
                $lessonMaterial->course_id,
                CourseUserRole::TEACHER,
            )) {
                throw ValidationException::withMessages([
                    'id' => 'Must enroll the course to edit the lesson material',
                ]);
            }

            $lessonMaterial->fill($request->only([
                'title',
                'content',
                'file_url',
                'link_url',
            ]))->save();

        } catch (Exception $e) {
            return ApiResponse::failed($e);
        }
    }
}
