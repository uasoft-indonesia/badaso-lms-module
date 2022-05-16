<?php

namespace Uasoft\Badaso\Module\LMSModule\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Uasoft\Badaso\Module\LMSModule\Factories\QuizFactory;

class Quiz extends Model
{
    use HasFactory;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('badaso.database.prefix').'quizzes');
    }

    protected $fillable = [
        'course_id',
        'topic_id',
        'name',
        'description',
        'start_time',
        'end_time',
        'duration',
        'point',
        'link_url',
        'created_by',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class, 'topic_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, app(CourseUser::class)->getTable())
            ->using(CourseUser::class)
            ->withPivot('role');
    }

    protected static function newFactory()
    {
        return QuizFactory::new();
    }
}
