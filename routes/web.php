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

// $router->get('/', function () use ($router) {
//     return $router->app->version();
// });

$router->get('ping', function () {
    return ['pong' => true];
});

// $router->get('random', 'BarberController@createRandom');

$router->group(['prefix' => 'auth'], function () use ($router) {
    $router->post('login', 'AuthController@login');
    $router->post('logout', 'AuthController@logout');
    $router->post('refresh', 'AuthController@refresh');
});

$router->group(['prefix' => 'user'], function () use ($router) {
    $router->post('favorite', 'UserController@toggleFavorite');
    $router->get('favorites', 'UserController@getFavorites');
    $router->get('appointments', 'UserController@getAppointments');
    $router->post('avatar', 'UserController@updateAvatar');
    $router->post('/', 'UserController@create');
    $router->get('/', 'UserController@read');
    $router->get('{id}', 'UserController@read');
    $router->put('/', 'UserController@update');
});

$router->group(['prefix' => 'barber'], function () use ($router) {
    $router->get('{id}', 'BarberController@one');
    $router->post('{id}/appointment', 'BarberController@setAppointment');
});

$router->get('barbers', 'BarberController@list');

$router->get('search', 'SearchController@search');
