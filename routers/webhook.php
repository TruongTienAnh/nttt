<?php
$app->router('/api/web-hook', 'GET', ['App\Controllers\HaravanWebhookController', 'callback']);

// $app->group(['prefix' => '/webhook'], function () use ($app) {
//     $app->router('/haravan', 'GET', function () use ($app) {
//         $params = [];
//         foreach (explode('&', $_SERVER['QUERY_STRING']) as $pair) {
//             [$key, $val] = explode('=', $pair, 2) + ['', ''];
//             $params[urldecode($key)] = urldecode($val);
//         }

//         $mode      = $params['hub.mode']         ?? '';
//         $token     = $params['hub.verify_token'] ?? '';
//         $challenge = $params['hub.challenge']    ?? '';

//         $envToken = $_ENV['HARAVAN_VERIFY_TOKEN'] ?? getenv('HARAVAN_VERIFY_TOKEN');

//         if ($mode === 'subscribe' && $token === $envToken) {
//             http_response_code(200);
//             echo $challenge;
//         } else {
//             http_response_code(401);
//             echo json_encode(['error' => 'Unauthorized']);
//         }
//         exit;
//     });

//     $app->router('/haravan',       'POST', ['App\Controllers\HaravanWebhookController', 'handle']);
//     $app->router('/haravan/debug', 'POST', ['App\Controllers\HaravanWebhookController', 'handleDebug']); 
// });


$app->group(['prefix' => '/webhook'], function () use ($app) {

    // Trong file routers/webhook.php

    $app->router('/haravan', 'GET', function () {
        // Haravan gửi verify_token để kiểm tra server của bạn
        $verifyToken = $_GET['hub_verify_token'] ?? $_GET['hub.verify_token'] ?? '';
        $challenge = $_GET['hub_challenge'] ?? $_GET['hub.challenge'] ?? '';
    
        if ($verifyToken === 'haravan_webhook_123') {
            header('Content-Type: text/plain');
            echo $challenge; // Bắt buộc phải echo đúng giá trị này
            exit;
        }
    
        http_response_code(403);
        echo "Verification failed";
        exit;
    });
    
    // Trong file routers/webhook.php

    $app->router('/haravan', 'POST', ['App\Controllers\HaravanWebhookController', 'handle']);

});

$app->group(['prefix' => '/app', 'middleware' => 'auth'], function () use ($app) {
    $app->router('/haravan/subscribe', 'POST',   ['App\Controllers\HaravanWebhookController', 'subscribe']);
    $app->router('/haravan/subscribe', 'GET',    ['App\Controllers\HaravanWebhookController', 'getSubscribed']);
    $app->router('/haravan/subscribe', 'DELETE', ['App\Controllers\HaravanWebhookController', 'unsubscribe']);
    $app->router('/haravan/logs',      'GET',    ['App\Controllers\HaravanWebhookController', 'logs']);
});