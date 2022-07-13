<?php

namespace Uasoft\Badaso\Module\LMSModule\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Uasoft\Badaso\Module\LMSModule\Factories\LessonMaterialFactory;

class LessonMaterial extends Model
{
    use HasFactory;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('badaso.database.prefix').'lesson_materials');
    }

    protected $fillable = [
        'course_id',
        'topic_id',
        'title',
        'content',
        'file_url',
        'link_url',
        'created_by',
    ];

    protected $hidden = [
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

    public function comments()
    {
        return $this->hasMany(MaterialComment::class, 'material_id')
            ->orderBy('created_at', 'asc');
    }

    protected static function newFactory()
    {
        return LessonMaterialFactory::new();
    }
}
