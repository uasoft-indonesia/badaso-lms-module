<?php

namespace Uasoft\Badaso\Module\LMSModule\Helpers;

class Route 
{
    public static function getController($key)
    {
         // get config 'controllers' from config/badaso-post.php
         $controllers = config('badaso-lms.controllers');

         // if the key is not found, return $key
         if (! isset($controllers[$key])) {
             return 'Uasoft\\Badaso\\Module\\LMS\\Controllers\\'.$key;
         }
 
         // return the value of the key
         return $controllers[$key];
    }
}