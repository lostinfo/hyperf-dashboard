<?php

declare(strict_types=1);

/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

use Hyperf\HttpServer\Router\Router;

// Supper Admin
Router::addGroup('/api/admin', function () {

    Router::get('/permissions', 'App\Controller\Admin\PermissionController@index');
    Router::post('/permissions', 'App\Controller\Admin\PermissionController@store');

    Router::get('/admins', 'App\Controller\Admin\AdminController@index');
    Router::post('/admins', 'App\Controller\Admin\AdminController@store');
    Router::get('/admins/{id}', 'App\Controller\Admin\AdminController@info');
    Router::delete('/admins/{id}', 'App\Controller\Admin\AdminController@destroy');

    Router::get('/roles', 'App\Controller\Admin\RoleController@index');
    Router::post('/roles', 'App\Controller\Admin\RoleController@store');
    Router::get('/roles/{id}', 'App\Controller\Admin\RoleController@info');
    Router::delete('/roles/{id}', 'App\Controller\Admin\RoleController@destroy');


}, ['middleware' => [\App\Middleware\AdminAuthMiddleware::class, \App\Middleware\CheckAdminIsSupperMiddleware::class]]);


// Admin Auth $$ Permission
Router::addGroup('/api/admin', function () {

    Router::get('/users', 'App\Controller\Admin\UserController@index');
    Router::get('/users/{id}', 'App\Controller\Admin\UserController@info');

    Router::get('/articles', 'App\Controller\Admin\ArticleController@index');
    Router::post('/articles', 'App\Controller\Admin\ArticleController@store');
    Router::get('/articles/{id}', 'App\Controller\Admin\ArticleController@info');
    Router::delete('/articles/{id}', 'App\Controller\Admin\ArticleController@destroy');

    // Export
    Router::addGroup('/export', function () {

        Router::get('/articles', 'App\Controller\Admin\ArticleController@export');

    });

}, ['middleware' => [\App\Middleware\AdminAuthMiddleware::class, \App\Middleware\AdminPermissionMiddleware::class]]);


// Admin Auth
Router::addGroup('/api/admin', function () {

    // Upload
    Router::post('/files/article', 'App\Controller\Admin\FileController@article');

    // Options
    Router::addGroup('/option', function () {

        Router::get('/roles', 'App\Controller\Admin\RoleController@options');
        Router::get('/roles/menu', 'App\Controller\Admin\RoleController@menuOptions');
        Router::get('/permissions', 'App\Controller\Admin\PermissionController@options');

    });

}, ['middleware' => [\App\Middleware\AdminAuthMiddleware::class]]);


// Admin
Router::addGroup('/api/admin', function () {

    Router::post('/login', 'App\Controller\Admin\AuthController@login');
    Router::post('/check', 'App\Controller\Admin\AuthController@check');

});


// Home
Router::addGroup('/api/home', function () {

    Router::get('/articles', 'App\Controller\Home\ArticleController@index');
    Router::get('/articles/{id}', 'App\Controller\Home\ArticleController@info');

});

// Notify
Router::addGroup('/api/notify', function () {


});
