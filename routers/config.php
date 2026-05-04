<?php

$app->group(['prefix' => '', 'middleware' => 'auth'], function () use ($app) {

    // Quản lý Chi nhánh
    $app->router('/config/brands',                 'GET',  ['App\Controllers\ConfigController', 'Brands']);
    $app->router('/config/brands/create',          'GET',  ['App\Controllers\ConfigController', 'BrandCreate']);
    $app->router('/config/brands/store',           'POST', ['App\Controllers\ConfigController', 'BrandStore']);
    $app->router('/config/brands/{active}/edit',   'GET',  ['App\Controllers\ConfigController', 'BrandEdit']);
    $app->router('/config/brands/{active}/update', 'POST', ['App\Controllers\ConfigController', 'BrandUpdate']);
    $app->router('/config/brands/{active}/delete', 'POST', ['App\Controllers\ConfigController', 'BrandDelete']);
    $app->router('/config/brands/{active}/toggle', 'POST', ['App\Controllers\ConfigController', 'BrandToggle']);

    // Quản lý Danh mục chi phí
    $app->router('/config/expense-categories', 'GET', ['App\Controllers\ConfigController', 'ExpenseCategories']);
    $app->router('/config/expense-categories-post', 'GET', ['App\Controllers\ConfigController', 'ExpenseCategoryPost']);
    $app->router('/config/expense-categories-post', 'POST', ['App\Controllers\ConfigController', 'SaveExpenseCategory']);
    $app->router('/config/expense-categories-delete', 'POST', ['App\Controllers\ConfigController', 'DeleteExpenseCategory']);
    $app->router('/config/expense-categories-toggle', 'POST', ['App\Controllers\ConfigController', 'ToggleExpenseCategoryStatus']);
    $app->router('/config/expense-categories-bulk-delete', 'POST', ['App\Controllers\ConfigController', 'BulkDeleteExpenseCategories']);

    // MODULE CẢNH BÁO ĐỘNG (ALERT RULES)
    $app->router('/config/alerts',               'GET',  ['App\Controllers\AlertController', 'Index']);
    $app->router('/config/alerts/create',        'GET',  ['App\Controllers\AlertController', 'Create']);
    $app->router('/config/alerts/store',         'POST', ['App\Controllers\AlertController', 'Store']);
    $app->router('/config/alerts/{id}/edit',     'GET',  ['App\Controllers\AlertController', 'Edit']);
    $app->router('/config/alerts/{id}/update',   'POST', ['App\Controllers\AlertController', 'Update']);
    $app->router('/config/alerts/{id}/delete',   'POST', ['App\Controllers\AlertController', 'Delete']);
    $app->router('/config/alerts/{id}/toggle',   'POST', ['App\Controllers\AlertController', 'Toggle']);

});