<?php

	$app->group(['prefix' => '', 'middleware' => 'auth'], function () use($app) {
        
        $app->router('', 'GET', ['App\Controllers\HomeController', 'Index']);
        
    });