<?php

/** @var Router $router */

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

use Laravel\Lumen\Routing\Router;

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->post('/login', 'AuthController@login');
$router->post('/register', 'AuthController@register');

$router->group(['middleware' => 'auth'], function () use ($router) {
    $router->post('/logout', 'AuthController@logout');

    $router->get('/book', 'BookController@index');
    $router->get('/book/{id}', 'BookController@show');
    $router->post('/book', 'BookController@create');
    $router->delete('/book/{id}', 'BookController@delete');
    $router->post('/book/{id}', 'BookController@update');
});
