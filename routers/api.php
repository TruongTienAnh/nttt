<?php

$app->group(['prefix' => '', 'middleware' => 'auth'], function () use ($app) {

    // API CỦA HỆ THỐNG CẢNH BÁO
    $app->router('/api/alerts/scan',      'POST', ['App\Controllers\AlertController', 'RunScanner']);
    $app->router('/api/alerts/unread',    'GET',  ['App\Controllers\AlertController', 'GetUnreadAlerts']);
    $app->router('/api/alerts/read/{id}', 'POST', ['App\Controllers\AlertController', 'MarkAlertRead']);

});