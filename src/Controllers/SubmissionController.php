<?php

namespace Uasoft\Badaso\Module\LMSModule\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Uasoft\Badaso\Controllers\Controller;
use Uasoft\Badaso\Helpers\ApiResponse;
use Uasoft\Badaso\Module\LMSModule\Helpers\CourseUserHelper;
use Uasoft\Badaso\Module\LMSModule\Models\Assignment;
use Uasoft\Badaso\Module\LMSModule\Models\Submission;

class SubmissionController extends Controller
{
    public function add(Request $request)
    {
        try {
            $user = auth()->user();

            $request->validate([
                'assignment_id' => 'required|integer',
                'file_url' => 'nullable|string|max:65535',
                'link_url' => 'nullable|string|max:65535',
            ]);

            $assignment = Assignment::find($request->input('assignment_id'));

            // Check if assignment exist
            if (! $assignment) {
                throw ValidationException::withMessages([
                    'id' => 'assignment not found',
                ]);
            }

            // Check if user is in the course
            if (! CourseUserHelper::isUserInCourse(
                $user->id,
                $assignment->course_id,
            )) {
                throw ValidationException::withMessages([
                    'id' => 'Must enroll the course to submit assignment',
                ]);
            }

            $existingSubmission = Submission::where([
                ['assignment_id', '=', $request->input('assignment_id')],
                ['user_id', '=', $user->id],
            ])->first();

            // Check if there is existing submission
            if ($existingSubmission) {
                throw ValidationException::withMessages([
                    'duplicate' => 'There is already a submission for this assignment',
                ]);
            }

            $due_date = strtotime($assignment->due_date);
            $current_date = strtotime(date('Y-m-d H:i:sP'));

            // Check if submission pass due date
            if ($current_date > $due_date) {
                throw ValidationException::withMessages([
                    'date' => 'You have passed submission due date',
                ]);
            }

            $submission = Submission::create([
                'assignment_id' => $request->input('assignment_id'),
                'user_id' => $user->id,
                'file_url' => $request->input('file_url'),
                'link_url' => $request->input('link_url'),
            ]);

            return ApiRespOnse::success($submission->toArray());
        } catch (Exception $e) {
            return ApiResponse::failed($e);
        }
    }

    public function read($id)
    {
        $user = auth()->user();

        $response = [
            'status' => 'submitted',
            'file_url' => null,
            'link_url' => null,
        ];

        $submission = Submission::with([
            'assignment:id,course_id',
        ])->where([
            ['assignment_id', '=', $id],
            ['user_id', '=', $user->id],
        ])->first();

        if (! $submission) {
            $response['status'] = 'no submission';
        } else {
            if (! CourseUserHelper::isUserInCourse(
                $user->id,
                $submission->assignment->course_id,
            )) {
                throw ValidationException::withMessages([
                    'id' => 'Must enroll the course to submit assignment',
                ]);
            }

            $response['file_url'] = $submission->file_url;
            $response['link_url'] = $submission->link_url;
        }

        return ApiRespOnse::success($response);
    }

    public function edit(Request $request, $id)
    {
        try {
            $user = auth()->user();

            $request->validate([
                'file_url' => 'nullable|string|max:65535',
                'link_url' => 'nullable|string|max:65535',
            ]);

            $submission = Submission::with([
                'assignment:id,course_id,due_date',
            ])->where([
                ['id', '=', $id],
                ['user_id', '=', $user->id],
            ])->first();

            if (! $submission) {
                throw ValidationException::withMessages([
                    'id' => 'Submission not found',
                ]);
            }

            if (! CourseUserHelper::isUserInCourse(
                $user->id,
                $submission->assignment->course_id,
            )) {
                throw ValidationException::withMessages([
                    'id' => 'Must enroll the course to submit assignment',
                ]);
            }

            $due_date = strtotime($submission->assignment->due_date);
            $current_date = strtotime(date('Y-m-d H:i:sP'));

            // Check if submission pass due date
            if ($current_date > $due_date) {
                throw ValidationException::withMessages([
                    'date' => 'You have passed submission due date',
                ]);
            }

            $submission->fill($request->only([
                'file_url',
                'link_url',
            ]))->save();

            return ApiRespOnse::success($submission->toArray());
        } catch (Exception $e) {
            return ApiResponse::failed($e);
        }
    }
}
