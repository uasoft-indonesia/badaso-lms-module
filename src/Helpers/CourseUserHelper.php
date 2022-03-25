<?php

namespace Uasoft\Badaso\Module\LMSModule\Helpers;

use Uasoft\Badaso\Module\LMSModule\Models\CourseUser;

class CourseUserHelper
{
    public static function isUserInCourse($userId, $courseId, $role = null)
    {
        $courseUser = CourseUser::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if (!$courseUser) {
            return false;
        }

        if ($role !== null) {
            return $courseUser->role == $role;
        }

        return true;
    }
}
