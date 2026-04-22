<?php
    $app->group(['prefix' => '/app', 'middleware' => 'auth'], function () use($app) {

        $app->router('/account', 'GET', ['App\Controllers\AccountController', 'Account']);

        $app->router('/profile', 'GET', ['App\Controllers\AccountController', 'Profiles']);

        $app->router('/affiliate', 'GET', ['App\Controllers\AccountController', 'Affiliate']);

        $app->router('/payments', 'GET', ['App\Controllers\AccountController', 'Payments']);

        $app->router('/account/change-infomation', 'POST', ['App\Controllers\AccountController', 'UpdateInformation']);

        $app->router('/account/change-password', 'POST', ['App\Controllers\AccountController', 'ChangePassword']);

        $app->router('/test', 'GET', function () use ($app) {
            // Test 1: Check tiền
            $balance = callApi('/account/balance');
            print_r($balance); 
            // Kết quả Mock: ['credits' => 9800...]

            // Test 2: Scan bài viết
            $result = callApi('/scan', 'POST', [
                'title' => 'Test GPT Content', // Title này sẽ kích hoạt Mock trả về AI 99%
                'content' => 'Nội dung bài viết cần kiểm tra...',
                'aiModelVersion' => 'asd'
            ]);

            // Xử lý kết quả (Code này sẽ chạy đúng cho cả Mock và Thật)
            if (isset($result['results']['ai']['classification']['AI'])) {
                $aiScore = print_r($result['results']);
                // echo "Điểm AI phát hiện được là: " . ($aiScore * 100) . "%";
            }
        });

    });