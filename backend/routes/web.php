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

$router->group(['prefix' => 'api'], function () use ($router) {
    # Auth Section
    $router->post('auth/login', 'AuthController@login');
    $router->post('auth/register', 'AuthController@register');
    $router->get('auth/confirm/{token}', 'AuthController@confirm');
    $router->post('auth/email/forgot', 'AuthController@forgot');
    $router->post('auth/email/reset/{token}', 'AuthController@reset');
    # Account Section
    $router->get('account/detail', 'AccountController@detail');
    $router->post('account/update', 'AccountController@update');
    $router->post('account/password', 'AccountController@password');
    $router->post('account/upload', 'AccountController@upload');
    $router->post('account/token', 'AccountController@refresh');
    $router->get('account/activity', 'AccountController@activity');
    $router->post('account/logout', 'AccountController@logout');
    # Notification Section
    $router->get('notification/list', 'NotificationController@list');
    $router->get('notification/read/{id}', 'NotificationController@read');
    $router->delete('notification/remove/{id}', 'NotificationController@delete');
    # Article Section
    $router->get('article/list', 'ArticleController@list');
    $router->post('article/create',  'ArticleController@create');
    $router->get('article/read/{slug}', 'ArticleController@read');
    $router->put('article/update/{id}','ArticleController@update');
    $router->delete('article/remove/{id}', 'ArticleController@delete');
    $router->get('article/user', 'ArticleController@user');
    $router->get('article/words', 'ArticleController@words');
    # Comment Section
    $router->get('comment/list/{id}', 'CommentController@list');
    $router->post('comment/create/{id}', 'CommentController@create');
    $router->delete('comment/remove/{id}', 'CommentController@delete');
});
