<?php

namespace Uasoft\Badaso\Module\LMS\Facades;

use Illuminate\Support\Facades\Facade;

class BadasoLMSModule extends Facade 
{
    /**
     * Get the registered name of the component
     * 
     * @return string
     */

     protected static function getFacadeAccessor()
     {
         return 'badaso-lms-module';
     }
}
