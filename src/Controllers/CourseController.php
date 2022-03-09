<?php

namespace Uasoft\Badaso\Module\LMSModule\Controllers;

use Doctrine\DBAL\Exception\ConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Exception;
use Facade\FlareClient\Http\Exceptions\NotFound;
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
use UniSharp\LaravelFilemanager\Exceptions\DuplicateFileNameException;

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
                'join_code', '=', $request->input('code')
            )->firstOrFail();

            $courseUser = CourseUser::create([
                'course_id' => $course->id,
                'user_id' => $user->id,
                'role' => CourseUserRole::STUDENT,
            ]);

            DB::commit();

            return ApiResponse::success($courseUser);
        } catch (Exception $e){
            DB::rollBack();

            if ($e instanceof ValidationException){
                return ApiResponse::failed($e);
            } else if ($e instanceof ModelNotFoundException){
                return ApiResponse::failed('Class code is invalid');
            } else if ($e instanceof QueryException){
                return ApiResponse::failed('You have been registered in this class already');
            }
            return ApiResponse::failed('Failed to join class, please try again');
        }
    }
}
