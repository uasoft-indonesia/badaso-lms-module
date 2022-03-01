<?php

use Illuminate\Support\Facades\Route;
use Uasoft\Badaso\Middleware\ApiRequest;
use Uasoft\Badaso\Module\LMSModule\Helpers\Route as HelpersRoute;

$api_route_prefix = config('Badaso.api_route_prefix', 'badaso-api');

Route::group(['prefix' => $api_route_prefix, 'as' => 'badaso.', 'middleware' => [ApiRequest::class]], function() {
    Route::group(['prefix' => 'module/lms/v1'], function() {
        Route::group(['prefix' => 'user'], function() {
            Route::get('/home', HelpersRoute::getController('UserController@home'));
        });
    });
});