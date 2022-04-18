<?php

namespace Uasoft\Badaso\Module\LMSModule\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Uasoft\Badaso\Module\LMSModule\Factories\MaterialCommentFactory;

class MaterialComment extends Model
{
    use HasFactory;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('badaso.database.prefix').'comments');
    }

    protected $fillable = [
        'material_id',
        'content',
        'created_by',
    ];

    protected $hidden = [
        'updated_at',
    ];

    public function lessonMaterial()
    {
        return $this->belongsTo(LessonMaterial::class, 'material_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function newFactory()
    {
        return MaterialCommentFactory::new();
    }
}
