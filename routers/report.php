<?php

use App\Controllers\CustomerReportController;

// customer report
$app->router('/reports/customers', 'GET', [CustomerReportController::class, 'index']);
