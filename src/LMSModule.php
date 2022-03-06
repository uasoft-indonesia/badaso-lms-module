<?php

namespace Uasoft\Badaso\Module\LMSModule;

class LMSModule 
{
    protected $protected_tables = [
        'lms_users',
    ];

    public function getProtectedTables(): array
    {
        return $this->protected_tables;
    }
}
