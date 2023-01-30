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


$router->group(['prefix' => 'pick-and-ban'], function () use ($router) {
    $router->group(['middleware' => ['auth', 'hasRolePickAndBan']], function () use ($router) {
        # GET ROUTES
        $router->get('/games', [
            'as' => 'games', 'uses' => 'PickAndBan\GameController@getOwnGames'
        ]);
        $router->get('/game-sets', [
            'as' => 'game-sets', 'uses' => 'PickAndBan\GameSetController@getOwnGameSets'
        ]);
        $router->get('/game-set-items', [
            'as' => 'game-set-items', 'uses' => 'PickAndBan\GameSetItemController@getAllGameSetItemsByGame'
        ]);
        $router->get('/rule-sets', [
            'as' => 'rule-sets', 'uses' => 'PickAndBan\RuleSetController@getOwnRuleSets'
        ]);

        # CREATE ROUTES
        $router->put('/game', 'PickAndBan\GameController@createGame');
        $router->put('/game-set', 'PickAndBan\GameSetController@createGameSet');
        $router->put('/game-set-item', 'PickAndBan\GameSetItemController@createGameSetItem');
        $router->put('/rule-set', 'PickAndBan\RuleSetController@createRuleSet');

        # DELETE ROUTES
        $router->delete('/game', 'PickAndBan\GameController@deleteGame');
        $router->delete('/game-set', 'PickAndBan\GameSetController@deleteGameSet');
        $router->delete('/game-set-item', 'PickAndBan\GameSetItemController@deleteGameSetItem');
        $router->delete('/rule-set', 'PickAndBan\RuleSetController@deleteRuleSet');

        # EDIT ROUTES
        $router->post('/game', 'PickAndBan\GameController@editGame');
        $router->post('/game-set', 'PickAndBan\GameSetController@editGameSet');
        $router->post('/game-set-item', 'PickAndBan\GameSetItemController@editGameSetItem');
        $router->post('/rule-set', 'PickAndBan\RuleSetController@editRuleSet');
    });
});

$router->group(['prefix' => 'recipes'], function () use ($router) {
    $router->group(['middleware' => ['auth', 'hasRoleRecipes']], function () use ($router) {
        # GET ROUTES
        $router->get('/', 'Recipes\RecipeController@getOwnRecipes');
        $router->get('/recipe', 'Recipes\RecipeController@getRecipe');

        # CREATE ROUTES
        $router->put('/recipe', 'Recipes\RecipeController@createRecipe');

        # DELETE ROUTES
        $router->delete('/recipe', 'Recipes\RecipeController@deleteRecipe');

        # EDIT ROUTES
        $router->post('/recipe', 'Recipes\RecipeController@editRecipe');
    });
});
