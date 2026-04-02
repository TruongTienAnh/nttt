<?php

	$app->group(['prefix' => '', 'middleware' => 'auth'], function () use($app) {

	    $app->router('', 'GET', function () use ($app){
	        $user = $app->request->user;
	        return view('home/home', ['user' => $user]);
	    });
	    
	});