<?php

namespace Uasoft\Badaso\Module\LMSModule\Models;

use Illuminate\Database\Eloquent\Model;

class LMSUsers extends Model
{
    public function __construct(array $attributes = [])
    {
        $prefix = config('Badaso.database.prefix');
        $this->table = $prefix.'lms_users';
        parent::__construct($attributes);
    }

    protected $fillable = [
        'id',
        'full_name',
        'username',
        'email',
        'password',
        'created_at',
    ];
}