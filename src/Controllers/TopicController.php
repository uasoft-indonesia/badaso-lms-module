<?php

namespace Uasoft\Badaso\Module\LMSModule\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Uasoft\Badaso\Controllers\Controller;
use Uasoft\Badaso\Helpers\ApiResponse;
use Uasoft\Badaso\Module\LMSModule\Helpers\CourseUserHelper;
use Uasoft\Badaso\Module\LMSModule\Models\Assignment;
use Uasoft\Badaso\Module\LMSModule\Models\LessonMaterial;
use Uasoft\Badaso\Module\LMSModule\Models\Quiz;
use Uasoft\Badaso\Module\LMSModule\Models\Topic;

class TopicController extends Controller
{
    public function add(Request $request)
    {
        try {
            $user = auth()->user();
            $request->validate([
                'course_id' => 'required|integer',
                'title' => 'required|string|max:255',
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

            $courseId = $request->query('course_id');

            $topic = Topic::with(
                'lessonMaterials:id,title,created_at,topic_id',
                'quizzes:id,name,created_at,topic_id',
                'assignments:id,title,created_at,topic_id',
            )
                ->where('course_id', $courseId)
                ->orderBy('created_at', 'desc')
                ->get();
            $topicArray = $topic->toArray();

            $lessonMaterialsWithNoTopic = LessonMaterial::whereNull('topic_id')
                ->where('course_id', $courseId)
                ->select('id', 'title', 'created_at', 'topic_id')
                ->orderBy('created_at', 'desc')
                ->get();

            $quizzesWithNoTopic = Quiz::whereNull('topic_id')
                ->where('course_id', $courseId)
                ->select('id', 'name', 'created_at', 'topic_id')
                ->orderBy('created_at', 'desc')
                ->get();

            $assignmentsWithNoTopic = Assignment::whereNull('topic_id')
                ->where('course_id', $courseId)
                ->select('id', 'title', 'created_at', 'topic_id')
                ->orderBy('created_at', 'desc')
                ->get();
            
            $nullTopic = [
                'id' => null,
                'title' => null,
                'courseId' => (int) $courseId,
                'lessonMaterials' => $lessonMaterialsWithNoTopic?->toArray(),
                'quizzes' => $quizzesWithNoTopic?->toArray(),
                'assignments' => $assignmentsWithNoTopic?->toArray(),
            ];
            array_unshift($topicArray, $nullTopic);

            return ApiResponse::success($topicArray);
        } catch (Exception $e) {
            return ApiResponse::failed($e);
        }
    }

    public function edit(Request $request, $id)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
            ]);

            $user = auth()->user();
            $topic = Topic::where('id', $id)
                ->where('created_by', $user->id)
                ->first();

            if (! $topic) {
                throw ValidationException::withMessages([
                    'id' => 'Topic not found',
                ]);
            }

            if (! CourseUserHelper::isUserInCourse($user->id, $topic->course_id)) {
                throw ValidationException::withMessages([
                    'id' => 'Must enroll the course to edit the topic',
                ]);
            }

            $topic->title = $request->input('title');
            $topic->save();

            return ApiResponse::success($topic->toArray());
        } catch (Exception $e) {
            return ApiResponse::failed($e);
        }
    }

    public function delete($id)
    {
        try {
            $user = auth()->user();
            $topic = Topic::where('id', $id)
                ->where('created_by', $user->id)
                ->first();

            if (! $topic) {
                throw ValidationException::withMessages([
                    'id' => 'Topic not found',
                ]);
            }

            if (! CourseUserHelper::isUserInCourse($user->id, $topic->course_id)) {
                throw ValidationException::withMessages([
                    'id' => 'Must enroll the course to delete the topic',
                ]);
            }

            $topic->delete();

            return ApiResponse::success($topic->toArray());
        } catch (Exception $e) {
            return ApiResponse::failed($e);
        }
    }
}
