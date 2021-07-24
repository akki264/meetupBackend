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
    return $router->app->version();
});


$router->group(['prefix' => 'api'], function () use ($router) {

    //Authentication APIs
    $router->post('login/', 'AuthController@authenticate');
    $router->post('register/', 'AuthController@register');

    //User related APIs
    $router->get('users/', 'UsersController@users');
    $router->post('getConnect/', 'UsersController@connectUser');


    //Scheduling APIs
    $router->get('schedule/', 'UsersController@getSchedule');
    $router->get('openschedule/', 'UsersController@openSchedule');
    $router->post('addschedule/', 'UsersController@addSchedule');
    $router->delete('deleteschedule/{id}', 'UsersController@deleteSchedule');
    $router->put('updateschedule/{id}', 'UsersController@updateSchedule');
});
