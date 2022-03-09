<?php

namespace Uasoft\Badaso\Module\LMSModule\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Uasoft\Badaso\Module\LMSModule\Factories\CourseFactory;
use Uasoft\Badaso\Module\LMSModule\Models\User;

class Course extends Model
{
  use HasFactory;

  public function __construct(array $attributes = [])
  {
    parent::__construct($attributes);
    $this->setTable(config('badaso.database.prefix') . 'courses');
  }

  protected $fillable = [
    'name',
    'subject',
    'room',
    'join_code',
    'created_by',
  ];

  protected $hidden = [
    'created_at',
    'updated_at',
  ];

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
    return CourseFactory::new();
  }
}
