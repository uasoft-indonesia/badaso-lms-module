<?php

namespace Uasoft\Badaso\Module\LMSModule\Helpers;
use stdClass;
class Token
{
    public static function createNewToken($token, $user)
    {
        $obj = new stdClass();
        $obj->accessToken = $token;
        $obj->tokenType = 'bearer';
        $obj->user = $user;

        return $obj;
    }
}
