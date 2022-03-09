<?php

namespace Uasoft\Badaso\Module\LMSModule\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Uasoft\Badaso\Module\LMSModule\Enums\CourseUserRole;

class CourseUser extends Pivot
{
    public $incrementing = false;

    protected $casts = [
        'role' => CourseUserRole::class,
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('badaso.database.prefix').'course_user');
    }
}
