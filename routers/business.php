<?php

$app->group(['prefix' => '', 'middleware' => 'auth'], function () use ($app) {

    // MODULE CHI PHÍ (EXPENSES)
    $app->router('/business/expenses',               'GET',  ['App\Controllers\ExpenseBusinessController', 'Index']);
    $app->router('/business/expenses/store',         'POST', ['App\Controllers\ExpenseBusinessController', 'Store']);
    $app->router('/business/expenses/{id}/edit',     'GET',  ['App\Controllers\ExpenseBusinessController', 'Edit']);
    $app->router('/business/expenses/{id}/update',   'POST', ['App\Controllers\ExpenseBusinessController', 'Update']);
    $app->router('/business/expenses/{id}/delete',   'POST', ['App\Controllers\ExpenseBusinessController', 'Delete']);

    // module khách hàng (customers)
    $app->router('/business/customers',               'GET',  ['App\Controllers\CustomerBusinessController', 'Index']);
    $app->router('/business/customers/{id}/show',         'GET', ['App\Controllers\CustomerBusinessController', 'Show']);

    // module hóa đơn (invoices)
    $app->router('/business/invoices',               'GET',  ['App\Controllers\InvoiceBusinessController', 'Index']);
    $app->router('/business/invoices/{id}/show',         'GET', ['App\Controllers\InvoiceBusinessController', 'Show']);

});