<?php

$app->group(['prefix' => '', 'middleware' => 'auth'], function () use ($app) {

    // Quản lý Nhóm quyền
    $app->router('/user/permissions',                 'GET',  ['App\Controllers\UserController', 'PermissionIndex']);
    $app->router('/user/permissions/create',          'GET',  ['App\Controllers\UserController', 'PermissionCreate']);
    $app->router('/user/permissions/store',           'POST', ['App\Controllers\UserController', 'PermissionStore']);
    $app->router('/user/permissions/{active}/edit',   'GET',  ['App\Controllers\UserController', 'PermissionEdit']);
    $app->router('/user/permissions/{active}/update', 'POST', ['App\Controllers\UserController', 'PermissionUpdate']);
    $app->router('/user/permissions/{active}/delete', 'POST', ['App\Controllers\UserController', 'PermissionDelete']);
    $app->router('/user/permissions/{active}/toggle', 'POST', ['App\Controllers\UserController', 'PermissionToggle']);

    // Quản lý Tài khoản
    $app->router('/user/accounts',                 'GET',  ['App\Controllers\UserController', 'AccountIndex']);
    $app->router('/user/accounts/create',          'GET',  ['App\Controllers\UserController', 'AccountCreate']);
    $app->router('/user/accounts/store',           'POST', ['App\Controllers\UserController', 'AccountStore']);
    $app->router('/user/accounts/{active}/edit',   'GET',  ['App\Controllers\UserController', 'AccountEdit']);
    $app->router('/user/accounts/{active}/update', 'POST', ['App\Controllers\UserController', 'AccountUpdate']);
    $app->router('/user/accounts/{active}/delete', 'POST', ['App\Controllers\UserController', 'AccountDelete']);
    $app->router('/user/accounts/{active}/toggle', 'POST', ['App\Controllers\UserController', 'AccountToggle']);

});