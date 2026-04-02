<?php
    $app->router('/login', 'GET', ['App\Controllers\AuthController', 'index']);
    $app->router('/login', 'POST', ['App\Controllers\AuthController', 'Login']);
    $app->router('/register', 'POST', ['App\Controllers\AuthController', 'Register']);
    $app->router('/logout', 'GET', ['App\Controllers\AuthController', 'Logout']);