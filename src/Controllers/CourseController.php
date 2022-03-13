<?php

namespace Uasoft\Badaso\Module\LMSModule\Controllers;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Nette\Utils\Random;
use Uasoft\Badaso\Controllers\Controller;
use Uasoft\Badaso\Helpers\ApiResponse;
use Uasoft\Badaso\Module\LMSModule\Enums\CourseUserRole;
use Uasoft\Badaso\Module\LMSModule\Models\Course;
use Uasoft\Badaso\Module\LMSModule\Models\CourseUser;
use Uasoft\Badaso\Module\LMSModule\Models\User;

class CourseController extends Controller
{
    public function add(Request $request)
    {
        DB::beginTransaction();

        try {
            $user = auth()->user();

            $request->validate([
                'name' => 'required|string|max:255',
                'subject' => 'required|string|max:255',
                'room' => 'required|string|max:255',
            ]);

            $course = Course::create([
                'name' => $request->input('name'),
                'subject' => $request->input('subject'),
                'room' => $request->input('room'),
                'join_code' => Random::generate(6, '0-9A-Z'),
                'created_by' => $user->id,
            ]);

            CourseUser::create([
                'course_id' => $course->id,
                'user_id' => $user->id,
                'role' => CourseUserRole::TEACHER,
            ]);

            DB::commit();

            return ApiResponse::success($course);
        } catch (Exception $e) {
            DB::rollBack();

            if ($e instanceof ValidationException) {
                return ApiResponse::failed($e);
            }

            return ApiResponse::failed('Failed to create course, please try again');
        }
    }

    public function join(Request $request)
    {
        DB::beginTransaction();

        try {
            $user = auth()->user();

            $request->validate([
                'code' => 'required|string|max:255',
            ]);

            $course = Course::where(
                'join_code',
                '=',
                $request->input('code')
            )->firstOrFail();

            $courseUser = CourseUser::create([
                'course_id' => $course->id,
                'user_id' => $user->id,
                'role' => CourseUserRole::STUDENT,
            ]);

            DB::commit();

            return ApiResponse::success($courseUser);
        } catch (Exception $e) {
            DB::rollBack();

            if ($e instanceof ValidationException) {
                return ApiResponse::failed($e);
            } elseif ($e instanceof ModelNotFoundException) {
                abort(404, 'Class not found');
            } elseif ($e instanceof QueryException) {
                return ApiResponse::failed('You have been registered in this class already');
            }

            return ApiResponse::failed('Failed to join class, please try again');
        }
    }

    public function people(Request $request, $id)
    {
        try {
            $userIds = CourseUser::where(
                'course_id',
                '=',
                $id
            )->pluck('user_id')->toArray();

            $people = [];

            foreach ($userIds as $uid) {
                $role = CourseUser::where([
                    ['course_id', '=', $id],
                    ['user_id', '=', $uid],
                ])->pluck('role')->toArray();

                $name = User::find(
                    $uid
                )->name;

                $person = ['name' => $name, 'role' => $role[0]];
                array_push($people, $person);
            }

            return ApiResponse::success($people);
        } catch (Exception $e) {
            if ($e instanceof ValidationException) {
                return ApiResponse::failed($e);
            }
        }
    }
}
