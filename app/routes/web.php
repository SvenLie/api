<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    $routeCollection = Illuminate\Support\Facades\Route::getRoutes();
    foreach ($routeCollection as $value) {
        if ($value['uri'] == '/') {
            continue;
        }
        echo $value['uri'] . "<br />";
    }
});

$router->group(['prefix' => 'htw-dresden'], function () use ($router) {
    $router->group(['prefix' => 'pillnitz'], function () use ($router) {

        $router->get('/timetable/{course}', [
            'as' => 'timetable', 'uses' => 'HtwDresden\Pillnitz\TimetableController@index'
        ]);

        $router->get('/courses', [
            'as' => 'courses', 'uses' => 'HtwDresden\Pillnitz\CourseController@index'
        ]);

        $router->get('/weeks', [
            'as' => 'weeks', 'uses' => 'HtwDresden\Pillnitz\WeeksController@index'
        ]);

    });
});
