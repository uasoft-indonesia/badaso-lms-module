<?php

namespace Uasoft\Badaso\Module\LMSModule\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Uasoft\Badaso\Module\LMSModule\Factories\AnnouncementFactory;

class Announcement extends Model
{
    use HasFactory;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('badaso.database.prefix').'announcements');
    }

    protected $fillable = [
        'course_id',
        'content',
        'created_by',
    ];

    protected $hidden = [
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

    public function comments()
    {
        return $this->hasMany(Comment::class, 'announcement_id')
            ->orderBy('created_at', 'asc');
    }

    protected static function newFactory()
    {
        return AnnouncementFactory::new();
    }
}
