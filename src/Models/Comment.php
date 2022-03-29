<?php

namespace Uasoft\Badaso\Module\LMSModule\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Uasoft\Badaso\Module\LMSModule\Factories\CommentFactory;

class Comment extends Model
{
    use HasFactory;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('badaso.database.prefix').'comments');
    }

    protected $fillable = [
        'announcement_id',
        'content',
        'created_by',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function announcement()
    {
        return $this->belongsTo(Announcement::class, 'announcement_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function newFactory()
    {
        return CommentFactory::new();
    }
}
