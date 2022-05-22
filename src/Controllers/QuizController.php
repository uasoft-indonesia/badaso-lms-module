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
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:65535',
                'start_time' => 'nullable|date_format:Y-m-d\TH:i:sp',
                'end_time' => 'nullable|date_format:Y-m-d\TH:i:sp',
                'duration' => 'nullable|integer',
                'point' => 'nullable|integer',
                'link_url' => 'required|string|max:65535',
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
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'start_time' => gmdate(
                    'Y-m-d H:i:s',
                    strtotime($request->input('start_time')),
                ),
                'end_time' => gmdate(
                    'Y-m-d H:i:s',
                    strtotime($request->input('end_time')),
                ),
                'duration' => $request->input('duration'),
                'point' => $request->input('point'),
                'link_url' => $request->input('link_url'),
                'created_by' => $user->id,
            ]);

            return ApiResponse::success($quiz->toArray());
        } catch (Exception $e) {
            return ApiResponse::failed($e);
        }
    }

    public function read($id)
    {
        try {
            $user = auth()->user();

            $quiz = Quiz::with([
                'createdBy:id,name',
                'topic:id,title',
            ])->find($id);

            if (! CourseUserHelper::isUserInCourse(
                $user->id,
                $quiz?->course_id,
            )) {
                throw ValidationException::withMessages([
                    'id' => 'Quiz not found',
                ]);
            }

            return ApiResponse::success(
                $quiz
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
            $user = auth()->user();
            $request->validate([
                'title' => 'string|max:255',
                'description' => 'nullable|string|max:65535',
                'start_time' => 'nullable|date_format:Y-m-d\TH:i:sp',
                'end_time' => 'nullable|date_format:Y-m-d\TH:i:sp',
                'duration' => 'nullable|integer',
                'point' => 'nullable|integer',
                'link_url' => 'nullable|string|max:65535',
            ]);

            $quiz = Quiz::find($id);
            if ($quiz?->created_by != $user->id) {
                throw ValidationException::withMessages([
                    'id' => 'Quiz not found',
                ]);
            }

            if (! CourseUserHelper::isUserInCourse(
                $user->id,
                $quiz->course_id,
                CourseUserRole::TEACHER,
            )) {
                throw ValidationException::withMessages([
                    'id' => 'Must enroll the course to edit the quiz',
                ]);
            }

            $quiz->fill($request->only([
                'title',
                'description',
                'start_time',
                'end_time',
                'duration',
                'point',
                'link_url',
            ]) + [
                'start_time' => gmdate(
                    'Y-m-d H:i:s',
                    strtotime($request->input('start_time')),
                ),
                'end_time' => gmdate(
                    'Y-m-d H:i:s',
                    strtotime($request->input('end_time')),
                ),
            ])->save();

            return ApiResponse::success($quiz->toArray());
        } catch (Exception $e) {
            return ApiResponse::failed($e);
        }
    }

    public function delete($id)
    {
        try {
            $user = auth()->user();

            $quiz = Quiz::find($id);
            if ($quiz?->created_by != $user->id) {
                throw ValidationException::withMessages([
                    'id' => 'Quiz not found',
                ]);
            }

            if (! CourseUserHelper::isUserInCourse(
                $user->id,
                $quiz->course_id,
                CourseUserRole::TEACHER,
            )) {
                throw ValidationException::withMessages([
                    'id' => 'Must enroll the course to edit the quiz',
                ]);
            }

            $quiz->delete();

            return ApiResponse::success();
        } catch (Exception $e) {
            return ApiResponse::failed($e);
        }
    }
}
