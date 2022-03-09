<?php

class TokenHelper {
    public static function createNewToken($token, $user)
    {
        $obj = new stdClass();
        $obj->accessToken = $token;
        $obj->tokenType = 'bearer';
        $obj->user = $user;

        return $obj;
    }
}
