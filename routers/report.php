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
    $app->router('/reports/finance/net-profit',  'GET', ['App\Controllers\FinancialReportController', 'NetProfit']);
    $app->router('/reports/finance/break-even',  'GET', ['App\Controllers\FinancialReportController', 'BreakEven']);
    $app->router('/reports/finance/forecast',    'GET', ['App\Controllers\FinancialReportController', 'Forecast']);
    $app->router('/reports/finance/location-pnl','GET', ['App\Controllers\FinancialReportController', 'LocationPnL']);
    $app->router('/reports/finance/roi',         'GET', ['App\Controllers\FinancialReportController', 'RoiPayback']); // Báo cáo ROI

    // ==========================================
    // 3. NHÓM HỆ THỐNG CẢNH BÁO (3 CẢNH BÁO)
    // ==========================================
    $app->router('/alerts/cost-risk', 'GET', ['App\Controllers\AlertController', 'CostRisk']);
    $app->router('/alerts/loss-risk', 'GET', ['App\Controllers\AlertController', 'LossRisk']);
    $app->router('/alerts/red-alert', 'GET', ['App\Controllers\AlertController', 'RedAlert']);


});
