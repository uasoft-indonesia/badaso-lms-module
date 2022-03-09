<?php

class TokenHelper {
    public static function createNewToken($token, $user, $remember = false)
    {
        $obj = new stdClass();
        $obj->access_token = $token;
        $obj->token_type = 'bearer';
        $obj->user = $user;

        return $obj;
    }
}
