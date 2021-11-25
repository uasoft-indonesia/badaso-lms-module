<?php

use Illuminate\Support\Str;
use Uasoft\Badaso\Facades\Badaso;
use Uasoft\Badaso\Middleware\ApiRequest;

$api_route_prefix = \config('badaso.api_route_prefix');
Route::group(['prefix' => $api_route_prefix, 'namespace' => 'Uasoft\Badaso\Module\Lms\Controllers', 'as' => 'badaso.', 'middleware' => [ApiRequest::class]], function () {
    Route::group(['prefix' => 'v1'], function () {
        Route::group(['prefix' => 'category'], function () {
            Route::get('/', 'CategoriesController@index');
            Route::post('/add', 'CategoriesController@add');
            Route::get('/read', 'CategoriesController@read');
            Route::put('/edit', 'CategoriesController@edit');
            Route::get('/delete', 'CategoriesController@delete');
        });
    });
});
