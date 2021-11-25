<?php

namespace Uasoft\Badaso\Module\Lms\Facades;

use Illuminate\Support\Facades\Facade;

class BadasoLmsModule extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'badaso-lms-module';
    }
}
