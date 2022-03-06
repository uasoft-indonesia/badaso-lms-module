<?php

use Illuminate\Support\Facades\Route;
use Uasoft\Badaso\Middleware\ApiRequest;
use Uasoft\Badaso\Module\LMSModule\Helpers\Route as HelpersRoute;

$api_route_prefix = config('Badaso.api_route_prefix', 'badaso-api');

// Route::group(['prefix' => $api_route_prefix, 'as' => 'badaso.', 'middleware' => [ApiRequest::class]], function() {
Route::group(['prefix' => $api_route_prefix, 'as' => 'badaso.'], function() {
    Route::group(['prefix' => 'module/lms/v1'], function() {
        Route::group(['prefix' => 'user'], function() {
            Route::get('/home', HelpersRoute::getController('UserController@home'));
        });

        Route::group(['prefix' => 'course', 'as' => 'course.'], function() {
            Route::get('/', HelpersRoute::getController('CourseController@index'));
            Route::post('/', HelpersRoute::getController('CourseController@store'))->name('store');
            Route::get('/{course}', HelpersRoute::getController('CourseController@show'));
        });
    });
});
