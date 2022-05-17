<?php

namespace Uasoft\Badaso\Module\LMSModule\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Uasoft\Badaso\Module\LMSModule\Factories\SubmissionFactory;

class Submission extends Model
{
    use HasFactory;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('badaso.database.prefix').'submissions');
    }

    protected $fillable = [
        'assignment_id',
        'user_id',
        'file_url',
        'link_url',
    ];

    protected $hidden = [
        'updated_at',
    ];

    public function assignment()
    {
        return $this->belongsTo(Assignment::class, 'assignment_id');
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function newFactory()
    {
        return SubmissionFactory::new();
    }
}