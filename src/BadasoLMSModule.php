<?php

namespace Uasoft\Badaso\Module\LMS;

class BadasoLMSModule {
    protected $protected_tables = [];

    public function getProtectedTables(): array
    {
        return $this->protected_tables;
    }
}

