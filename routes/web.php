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

    $router->post('login/', 'AuthController@authenticate');
    $router->post('register/', 'AuthController@register');
    $router->get('users/', 'UsersController@users');
    $router->post('getConnect/', 'UsersController@connectUser');
    $router->get('schedule/', 'UserController@getSchedule');
    $router->get('openschedule/', 'UsersContoller@openSchedule');
    $router->post('addschedule/', 'UsersContoller@addSchedule');
    $router->delete('deleteschedule/', 'UsersContoller@deleteSchedule');
    $router->put('updateschedule/', 'UsersContoller@updateSchedule');
});
