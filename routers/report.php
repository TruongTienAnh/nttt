<?php

$app->group(['prefix' => '', 'middleware' => 'auth'], function () use ($app) {

    // ==========================================
    // 1. NHÓM HÀNH VI VÀ KHÁCH HÀNG (3 BÁO CÁO)
    // ==========================================
    $app->router('/reports/customers/rfm',        'GET', ['App\Controllers\CustomerReportController', 'RfmReport']);
    $app->router('/reports/customers/churn',      'GET', ['App\Controllers\CustomerReportController', 'ChurnReport']);
    $app->router('/reports/customers/cross-sell', 'GET', ['App\Controllers\CustomerReportController', 'CrossSellReport']);

    // ==========================================
    // 2. NHÓM TÀI CHÍNH CHIẾN LƯỢC (5 BÁO CÁO)
    // ==========================================
    $app->router('/reports/finance/net-profit',  'GET', ['App\Controllers\FinancialReportController', 'NetProfitReport']);
    $app->router('/reports/finance/break-even',  'GET', ['App\Controllers\FinancialReportController', 'BreakEvenReport']);
    $app->router('/reports/finance/forecast',    'GET', ['App\Controllers\FinancialReportController', 'ForecastReport']);
    $app->router('/reports/finance/location-pnl','GET', ['App\Controllers\FinancialReportController', 'LocationPnlReport']);
    $app->router('/reports/finance/roi',         'GET', ['App\Controllers\FinancialReportController', 'RoiReport']);

    // BÁO CÁO TỰ CHỌN (DYNAMIC REPORTS)
    $app->router('/reports/dynamic',          'GET',  ['App\Controllers\CustomReportController', 'Index']);

});
