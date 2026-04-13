<?php

$app->group(['prefix' => '', 'middleware' => 'auth'], function () use ($app) {

    $app->router('/config/brands',                    'GET',  ['App\Controllers\ConfigController', 'Brands']);
    $app->router('/config/brands/store',              'POST', ['App\Controllers\ConfigController', 'BrandStore']);
    $app->router('/config/brands/{id}/edit',          'GET',  ['App\Controllers\ConfigController', 'BrandEdit']);
    $app->router('/config/brands/{id}/update',        'POST', ['App\Controllers\ConfigController', 'BrandUpdate']);
    $app->router('/config/brands/{id}/delete',        'POST', ['App\Controllers\ConfigController', 'BrandDelete']);
    $app->router('/config/brands/{id}/restore',       'POST', ['App\Controllers\ConfigController', 'BrandRestore']);
    $app->router('/config/brands/{id}/toggle-status', 'POST', ['App\Controllers\ConfigController', 'BrandToggleStatus']);

});