<?php

namespace Uasoft\Badaso\Module\LMSModule\Helpers;

class Route 
{
    public static function getController($key)
    {
        $controllers = config('badaso-lms-module.controllers');

        if (! isset($controllers[$key])) {
            return 'Uasoft\\Badaso\\Module\\LMSModule\\Controllers\\'.$key;
        }
 
        return $controllers[$key];
    }
}
