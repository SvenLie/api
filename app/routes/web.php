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
        echo $value['uri'] . " (" . $value['method'] . ")<br />";
    }
});

$router->group(['prefix' => 'auth'], function () use ($router) {
    $router->post('login', 'AuthController@login');
    $router->post('logout', 'AuthController@logout');
    $router->post('register', 'AuthController@register');
});

$router->group(['prefix' => 'htw-dresden'], function () use ($router) {
    $router->get('/modules', [
        'as' => 'htw-modules', 'uses' =>  'HtwDresden\Timetable\TimetableController@modules'
    ]);

    $router->get('/lectures', [
       'as' => 'htw-lectures', 'uses' =>  'HtwDresden\Timetable\TimetableController@lectures'
    ]);

    $router->get('/timetable', [
        'as' => 'htw-timetable', 'uses' =>  'HtwDresden\Timetable\TimetableController@timetable'
    ]);

    $router->get('/timetable-ical', [
        'as' => 'htw-timetable-ical', 'uses' =>  'HtwDresden\Timetable\TimetableController@timetableICAL'
    ]);

    $router->group(['prefix' => 'pillnitz'], function () use ($router) {

        $router->get('/timetable-ical/{course}', [
            'as' => 'timetable-ical', 'uses' => 'HtwDresden\Pillnitz\Legacy\TimetableController@ical'
        ]);

        $router->get('/timetable/{course}', [
            'as' => 'timetable', 'uses' => 'HtwDresden\Pillnitz\Legacy\TimetableController@index'
        ]);

        $router->get('/courses', [
            'as' => 'courses', 'uses' => 'HtwDresden\Pillnitz\Legacy\CourseController@index'
        ]);

        $router->get('/weeks', [
            'as' => 'weeks', 'uses' => 'HtwDresden\Pillnitz\Legacy\WeeksController@index'
        ]);

        /*$router->group(['prefix' => 'v2'], function () use ($router) {
            $router->get('/timetable/{course}', [
                'as' => 'timetable', 'uses' => 'HtwDresden\Pillnitz\V2\TimetableController@index'
            ]);
        });*/

    });
});

$router->group(['prefix' => 'date-time'], function () use ($router) {
    $router->get('/current-time', [
        'as' => 'current-time', 'uses' => 'DateTime\TimeController@getCurrentTimeInGermany'
    ]);
});

$router->group(['prefix' => 'automation'], function () use ($router) {
    $router->get('/presence', [
        'as' => 'presence', 'uses' => 'Automation\PresenceController@getCurrentPresenceStatus'
    ]);

    $router->post('/presence', [
        'as' => 'presence', 'uses' => 'Automation\PresenceController@setCurrentPresenceStatus'
    ]);
});
