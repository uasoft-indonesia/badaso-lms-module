<?php

namespace Uasoft\Badaso\Module\LMSModule\Facades;

use Illuminate\Support\Facades\Facade;

class LMSModule extends Facade
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
