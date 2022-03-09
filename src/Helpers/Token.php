<?php

class TokenHelper {
    public static function createNewToken($token, $user, $remember = false)
    {
        $obj = new stdClass();
        $obj->accessToken = $token;
        $obj->tokenType = 'bearer';
        $obj->user = $user;

        return $obj;
    }
}
