<?php

namespace Uasoft\Badaso\Module\LMSModule\Tests\Helpers;

use Exception;
use Tests\TestCase;

class AuthHelper
{
    protected static $loginUrl = '/badaso-api/module/lms/v1/auth/login';

    protected static $cache = [];

    public static function asUser(TestCase $testCase, $user)
    {
        if (!isset($user->rawPassword)) {
            throw new Exception(
                'Please set user raw password first, e.g. `$user->rawPassword = \'password\';`'
            );
        }

        $cachedToken = self::getCachedUserToken($user);
        if (!empty($cachedToken)) {
            return $cachedToken;
        }

        $credentials = [
            'email' => $user->email,
            'password' => $user->rawPassword,
        ];

        $loginResponse = $testCase->json('POST', self::$loginUrl, $credentials);

        $token = $loginResponse->json('accessToken');

        self::setCachedUserToken($user, $token);

        return $testCase->withHeader('Authorization', 'Bearer ' . $token);
    }

    public static function getCacheKey($user)
    {
        return $user->email . ' : ' . $user->id;
    }

    public static function getCachedUserToken($user)
    {
        $cacheKey = static::getCacheKey($user);
        return self::$cache[$cacheKey] ?? NULL;
    }

    public static function setCachedUserToken($user, $token)
    {
        $cacheKey = static::getCacheKey($user);
        self::$cache[$cacheKey] = $token;
    }
}
