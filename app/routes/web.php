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
        echo $value['uri'] . " (". $value['method'] . ")<br />";
    }
});

$router->group(['prefix' => 'htw-dresden'], function () use ($router) {
    $router->group(['prefix' => 'pillnitz'], function () use ($router) {

        $router->get('/timetable-ical/{course}', [
            'as' => 'timetable-ical', 'uses' => 'HtwDresden\Pillnitz\TimetableController@ical'
        ]);

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

$router->group(['prefix' => 'date-time'], function() use ($router) {
    $router->get('/current-time', [
        'as' => 'current-time', 'uses' => 'DateTime\TimeController@getCurrentTimeInGermany'
    ]);
});

$router->group(['prefix' => 'automation'], function() use ($router) {
    $router->get('/presence', [
        'as' => 'presence', 'uses' => 'Automation\PresenceController@getCurrentPresenceStatus'
    ]);

    $router->post('/presence', [
        'as' => 'presence', 'uses' => 'Automation\PresenceController@setCurrentPresenceStatus'
    ]);
});
