<?php

namespace Uasoft\Badaso\Module\LMSModule\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Uasoft\Badaso\Module\LMSModule\Factories\TopicFactory;

class Topic extends Model
{
    use HasFactory;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('badaso.database.prefix').'topics');
    }

    protected $fillable = [
        'course_id',
        'title',
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

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lessonMaterials()
    {
        return $this->hasMany(LessonMaterial::class, 'topic_id')
            ->orderBy('created_at', 'desc');
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class, 'topic_id')
            ->orderBy('created_at', 'desc');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'topic_id')
            ->orderBy('created_at', 'desc');
    }

    protected static function newFactory()
    {
        return TopicFactory::new();
    }
}
