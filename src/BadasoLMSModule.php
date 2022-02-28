<?php

namespace Uasoft\Badaso\Module\LMS;

class BadasoLMSModule 
{
    protected $protected_tables = [
        'lms_users',
    ];

    public function getProtectedTables(): array
    {
        return $this->protected_tables;
    }
}

