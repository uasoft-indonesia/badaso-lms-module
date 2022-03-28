<?php

use Illuminate\Support\Facades\Route;
use Uasoft\Badaso\Middleware\ApiRequest;
use Uasoft\Badaso\Middleware\BadasoAuthenticate;
use Uasoft\Badaso\Module\LMSModule\Helpers\Route as HelpersRoute;

$api_route_prefix = config('Badaso.api_route_prefix', 'badaso-api');

Route::group(['prefix' => $api_route_prefix, 'as' => 'badaso.', 'middleware' => [ApiRequest::class]], function () {
    Route::group(['prefix' => 'module/lms/v1'], function () {
        Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
            Route::post('/login', HelpersRoute::getController('AuthController@login'))
                ->name('login');
            Route::post('/register', HelpersRoute::getController('AuthController@register'))
                ->name('register');
        });

        Route::group(['prefix' => 'course', 'as' => 'course.'], function () {
            Route::post('/', HelpersRoute::getController('CourseController@add'))
                ->middleware(BadasoAuthenticate::class)
                ->name('add');

            Route::post('/join', HelpersRoute::getController('CourseController@join'))
                ->middleware(BadasoAuthenticate::class)
                ->name('join');

            Route::get('/{id}/people', HelpersRoute::getController('CourseController@people'))
                ->middleware(BadasoAuthenticate::class)
                ->name('people');
        });

        Route::group(['prefix' => 'courseuser', 'as' => 'courseuser.'], function () {
            Route::get('/view', HelpersRoute::getController('CourseUserController@view'))
                ->middleware(BadasoAuthenticate::class)
                ->name('view');
        });

        Route::group(['prefix' => 'announcement', 'as' => 'announcement.'], function () {
            Route::post('/', HelpersRoute::getController('AnnouncementController@add'))
                ->middleware(BadasoAuthenticate::class)
                ->name('add');
            Route::get('/', HelpersRoute::getController('AnnouncementController@browse'))
                ->middleware(BadasoAuthenticate::class)
                ->name('browse');
            Route::put('/{id}', HelpersRoute::getController('AnnouncementController@edit'))
                ->middleware(BadasoAuthenticate::class)
                ->name('edit');
        });
    });
});
