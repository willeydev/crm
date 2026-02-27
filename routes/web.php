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


// Auth
$router->group(['prefix' => 'auth'], function () use ($router) {
    $router->post('register', 'AuthController@register');
    $router->post('login',    'AuthController@login');

    $router->group(['middleware' => 'auth'], function () use ($router) {
        $router->post('logout', 'AuthController@logout');
        $router->get('me',      'AuthController@me');
    });
});

$router->group(['prefix' => 'customers', 'middleware' => 'auth'], function () use ($router) {
    $router->get('',        'CustomerController@index');
    $router->post('',       'CustomerController@store');
    $router->get('{id}',    'CustomerController@show');
    $router->put('{id}',    'CustomerController@update');
    $router->delete('{id}', 'CustomerController@destroy');
});
