<?php

namespace Uasoft\Badaso\Module\LMSModule\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Uasoft\Badaso\Models\User as ModelsUser;
use Uasoft\Badaso\Module\LMSModule\Factories\UserFactory;

class User extends ModelsUser
{
    use HasFactory;

    public function courses()
    {
        return $this->belongsToMany(Course::class, app(CourseUser::class)->getTable())
            ->using(CourseUser::class)
            ->withPivot('role');
    }

    protected static function newFactory()
    {
        return UserFactory::new();
    }
}
