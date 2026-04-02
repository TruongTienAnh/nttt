<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
}
try {
    // 1. Gọi file bootstrap để lấy instance $app
    $app = require_once __DIR__ . '/../config/bootstrap.php';

    // 2. Chạy ứng dụng
    $app->run();

} catch (Throwable $e) {
    // Xử lý lỗi cấp cao nhất (nếu bootstrap hoặc run bị lỗi)
    if (isset($app) && method_exists($app, 'abort')) {
        $app->abort(500, $e->getMessage());
    } else {
        // Fallback nếu App chưa khởi tạo được
        http_response_code(500);
        echo "<h1>Critical Error</h1>" . $e->getMessage();
    }
}