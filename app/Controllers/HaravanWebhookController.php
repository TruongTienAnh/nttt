<?php

namespace App\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Integration;

class HaravanWebhookController
{

    public function callback(): void
    {
        $code  = $_GET['code']  ?? '';
        $error = $_GET['error'] ?? '';
 
        // User từ chối cấp quyền
        if ($error || !$code) {
            echo json_encode(['error' => 'User denied or missing code']);
            exit;
        }
 
        // Đổi code lấy access_token
        $tokenData = $this->exchangeToken($code);
 
        if (empty($tokenData['access_token'])) {
            echo json_encode(['error' => 'Cannot get access_token', 'data' => $tokenData]);
            exit;
        }
 
        $accessToken = $tokenData['access_token'];
        $orgId       = $tokenData['org_id'] ?? ($tokenData['myharavan_id'] ?? '');
 
        // Lưu access_token vào bảng integrations
        $this->saveIntegration($orgId, $accessToken, $tokenData);
 
        // Đăng ký webhook ngay sau khi có token
        $this->subscribeWebhook($accessToken);
 
        // Redirect về trang cài đặt hoặc thông báo thành công
        header('Location: /app/settings?haravan=connected');
        exit;
    }
 
    // ──────────────────────────────────────────────────────────
    // Đổi authorization code lấy access_token
    // ──────────────────────────────────────────────────────────
    private function exchangeToken(string $code): array
    {
        $ch = curl_init('https://accounts.haravan.com/connect/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'grant_type'    => 'authorization_code',
                'code'          => $code,
                'redirect_uri'  => 'https://ntt.eclo.io/api/web-hook',
                'client_id'     => $_ENV['HARAVAN_CLIENT_ID'],
                'client_secret' => $_ENV['HARAVAN_APP_SECRET'],
            ]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        ]);
 
        $response = curl_exec($ch);
        curl_close($ch);
 
        return json_decode($response, true) ?? [];
    }
 
    // ──────────────────────────────────────────────────────────
    // Lưu token vào bảng integrations
    // ──────────────────────────────────────────────────────────
    private function saveIntegration(string $orgId, string $accessToken, array $tokenData): void
    {
        $db = getDB();
 
        // Tìm organization theo haravan org_id
        $org = $db->prepare('SELECT id FROM organizations WHERE slug = ? LIMIT 1');
        $org->execute([$orgId]);
        $orgRow = $org->fetch();
 
        if (!$orgRow) {
            // Tự tạo organization mới nếu chưa có
            $newOrgId = $this->uuid();
            $db->prepare('
                INSERT INTO organizations (id, name, slug, is_active, created_at)
                VALUES (?, ?, ?, 1, NOW())
            ')->execute([$newOrgId, 'Shop ' . $orgId, $orgId]);
            $organizationId = $newOrgId;
        } else {
            $organizationId = $orgRow['id'];
        }
 
        // Lưu hoặc cập nhật integration
        $existing = $db->prepare('
            SELECT id FROM integrations
            WHERE organization_id = ? AND type = ?
        ');
        $existing->execute([$organizationId, 'pos_haravan']);
        $row = $existing->fetch();
 
        $credentials = json_encode([
            'access_token'  => $accessToken,
            'refresh_token' => $tokenData['refresh_token'] ?? '',
            'expires_in'    => $tokenData['expires_in'] ?? 0,
            'token_type'    => $tokenData['token_type'] ?? 'Bearer',
            'haravan_org_id'=> $orgId,
            'connected_at'  => date('Y-m-d H:i:s'),
        ]);
 
        if ($row) {
            $db->prepare('
                UPDATE integrations
                SET credentials = ?, is_active = 1, last_synced_at = NOW()
                WHERE id = ?
            ')->execute([$credentials, $row['id']]);
        } else {
            $db->prepare('
                INSERT INTO integrations (id, organization_id, type, credentials, is_active, created_at)
                VALUES (?, ?, ?, ?, 1, NOW())
            ')->execute([$this->uuid(), $organizationId, 'pos_haravan', $credentials]);
        }
    }
 
    // ──────────────────────────────────────────────────────────
    // Subscribe webhook ngay sau khi lấy được token
    // ──────────────────────────────────────────────────────────
    private function subscribeWebhook(string $accessToken): void
    {
        $ch = curl_init('https://webhook.haravan.com/api/subscribe');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode([]),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken,
            ],
        ]);
        curl_exec($ch);
        curl_close($ch);
    }
 
    public function handle(): void
    {
        $rawBody = file_get_contents('php://input');

        // 1. Xác thực chữ ký HMAC
        // if (!$this->verifyHmac($rawBody)) {
        //     http_response_code(401);
        //     echo json_encode(['error' => 'Invalid signature']);
        //     exit;
        // }

        $payload = json_decode($rawBody, true);
        if (!$payload) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON']);
            exit;
        }

        // 2. Trả 200 ngay lập tức — tránh Haravan timeout 5s
        http_response_code(200);
        echo json_encode(['status' => 'received']);

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request(); // PHP-FPM: đóng response, tiếp tục xử lý
        }

        // 3. Xử lý data sau khi đã trả response
        $orgId = (string)($payload['org_id'] ?? '');
        $topic = $payload['topic'] ?? '';

        try {
            switch ($topic) {
                case 'orders/create':
                    $this->handleOrderCreate($payload, $orgId);
                    break;

                case 'orders/updated':
                case 'orders/paid':
                    $this->handleOrderUpdate($payload, $orgId);
                    break;

                case 'customers/create':
                case 'customers/update':
                    $this->handleCustomerUpsert($payload, $orgId);
                    break;

                case 'products/update':
                    $this->handleProductUpdate($payload, $orgId);
                    break;

                case 'app/uninstalled':
                    $this->handleAppUninstalled($orgId);
                    break;
            }

            $this->writeLog($topic, $orgId, 'success');

        } catch (\Throwable $e) {
            $this->writeLog($topic, $orgId, 'error', $e->getMessage());
        }

        exit;
    }

    // ──────────────────────────────────────────────────────────
    // POST /app/haravan/subscribe
    // Đăng ký nhận webhook (gọi 1 lần sau khi có access_token)
    // ──────────────────────────────────────────────────────────
    public function subscribe(): void
    {
        $accessToken = $_POST['access_token'] ?? '';
        if (!$accessToken) {
            echo json_encode(['error' => 'access_token required']);
            exit;
        }

        $result = $this->callHaravanApi('POST', 'https://webhook.haravan.com/api/subscribe', $accessToken);
        echo json_encode($result);
        exit;
    }

    // ──────────────────────────────────────────────────────────
    // GET /app/haravan/subscribe
    // Xem danh sách webhook đang active
    // ──────────────────────────────────────────────────────────
    public function getSubscribed(): void
    {
        $accessToken = $_GET['access_token'] ?? '';
        $result = $this->callHaravanApi('GET', 'https://webhook.haravan.com/api/subscribe', $accessToken);
        echo json_encode($result);
        exit;
    }

    // ──────────────────────────────────────────────────────────
    // DELETE /app/haravan/subscribe
    // Hủy đăng ký webhook
    // ──────────────────────────────────────────────────────────
    public function unsubscribe(): void
    {
        $accessToken = $_POST['access_token'] ?? '';
        $result = $this->callHaravanApi('DELETE', 'https://webhook.haravan.com/api/subscribe', $accessToken);
        echo json_encode($result);
        exit;
    }

    // ──────────────────────────────────────────────────────────
    // GET /app/haravan/logs
    // Xem 50 dòng log gần nhất (debug)
    // ──────────────────────────────────────────────────────────
    public function logs(): void
    {
        $logFile = __DIR__ . '/../../logs/haravan_webhook.log';
        if (!file_exists($logFile)) {
            echo json_encode(['logs' => []]);
            exit;
        }

        $lines = array_slice(file($logFile), -50);
        echo json_encode(['logs' => array_reverse($lines)]);
        exit;
    }


    // ==========================================================
    // PRIVATE — xử lý từng event
    // ==========================================================

    private function handleOrderCreate(array $data, string $orgId): void
    {
        $db = getDB();

        // Lấy branch theo org_id
        $branch = $db->prepare('SELECT id FROM branches WHERE organization_id = ? AND is_active = 1 LIMIT 1');
        $branch->execute([$orgId]);
        $branchRow = $branch->fetch();
        if (!$branchRow) return;

        $branchId = $branchRow['id'];

        // Upsert customer nếu có
        $customerId = null;
        if (!empty($data['customer'])) {
            $customerId = $this->upsertCustomer($data['customer'], $orgId, $branchId);
        }

        // Tìm staff nếu có
        $staffId = null;
        if (!empty($data['staff_email'])) {
            $staff = $db->prepare('SELECT id FROM users WHERE email = ? AND organization_id = ?');
            $staff->execute([$data['staff_email'], $orgId]);
            $staffRow = $staff->fetch();
            $staffId = $staffRow['id'] ?? null;
        }

        // Tránh duplicate (Haravan retry)
        $check = $db->prepare('SELECT id FROM invoices WHERE invoice_no = ? AND organization_id = ?');
        $check->execute([(string)$data['id'], $orgId]);
        if ($check->fetch()) return;

        $invoiceId = $this->uuid();

        $db->prepare('
            INSERT INTO invoices
                (id, organization_id, branch_id, customer_id, staff_id,
                 invoice_no, subtotal, discount, total,
                 payment_method, status, invoice_date, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ')->execute([
            $invoiceId, $orgId, $branchId, $customerId, $staffId,
            (string)$data['id'],
            (float)($data['subtotal_price']   ?? 0),
            (float)($data['total_discounts']  ?? 0),
            (float)($data['total_price']      ?? 0),
            $this->mapPayment($data['payment_gateway'] ?? ''),
            $this->mapStatus($data['financial_status'] ?? ''),
            $data['created_at'] ?? date('Y-m-d H:i:s'),
        ]);

        // Insert line items
        foreach ($data['line_items'] ?? [] as $item) {
            $svc = $db->prepare('SELECT id FROM services WHERE (sku = ? OR name = ?) AND organization_id = ? LIMIT 1');
            $svc->execute([$item['sku'] ?? '', $item['name'] ?? '', $orgId]);
            $svcRow = $svc->fetch();

            $qty   = (int)($item['quantity'] ?? 1);
            $price = (float)($item['price'] ?? 0);

            $db->prepare('
                INSERT INTO invoice_items (id, invoice_id, service_id, name, qty, unit_price, total)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ')->execute([
                $this->uuid(), $invoiceId,
                $svcRow['id'] ?? null,
                $item['name'] ?? '',
                $qty, $price, $qty * $price,
            ]);
        }
    }

    private function handleOrderUpdate(array $data, string $orgId): void
    {
        $db = getDB();
        $db->prepare('
            UPDATE invoices
            SET status = ?, total = ?, discount = ?, payment_method = ?
            WHERE invoice_no = ? AND organization_id = ?
        ')->execute([
            $this->mapStatus($data['financial_status'] ?? ''),
            (float)($data['total_price']     ?? 0),
            (float)($data['total_discounts'] ?? 0),
            $this->mapPayment($data['payment_gateway'] ?? ''),
            (string)$data['id'],
            $orgId,
        ]);
    }

    private function handleCustomerUpsert(array $data, string $orgId): void
    {
        $branchId = $this->getDefaultBranch($orgId);
        if ($branchId) $this->upsertCustomer($data, $orgId, $branchId);
    }

    private function upsertCustomer(array $c, string $orgId, string $branchId): ?string
    {
        $db = getDB();

        $existing = $db->prepare('
            SELECT id FROM customers
            WHERE organization_id = ? AND (phone = ? OR email = ?)
            LIMIT 1
        ');
        $existing->execute([$orgId, $c['phone'] ?? '', $c['email'] ?? '']);
        $row = $existing->fetch();

        $fullName = trim(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? ''));

        if ($row) {
            $db->prepare('
                UPDATE customers SET full_name = ?, email = ?, phone = ?, updated_at = NOW()
                WHERE id = ?
            ')->execute([$fullName, $c['email'] ?? null, $c['phone'] ?? null, $row['id']]);
            return $row['id'];
        }

        $id = $this->uuid();
        $db->prepare('
            INSERT INTO customers (id, organization_id, branch_id, full_name, phone, email, source, tier, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ')->execute([$id, $orgId, $branchId, $fullName, $c['phone'] ?? null, $c['email'] ?? null, 'haravan', 'new']);

        return $id;
    }

    private function handleProductUpdate(array $data, string $orgId): void
    {
        $db = getDB();
        foreach ($data['variants'] ?? [] as $variant) {
            $svc = $db->prepare('SELECT id FROM services WHERE sku = ? AND organization_id = ?');
            $svc->execute([$variant['sku'] ?? '', $orgId]);
            $row = $svc->fetch();
            if ($row) {
                $db->prepare('UPDATE services SET name = ?, price = ? WHERE id = ?')
                   ->execute([$data['title'] ?? '', (float)($variant['price'] ?? 0), $row['id']]);
            }
        }
    }

    private function handleAppUninstalled(string $orgId): void
    {
        getDB()->prepare('UPDATE integrations SET is_active = 0 WHERE organization_id = ? AND type = ?')
               ->execute([$orgId, 'pos_haravan']);
    }


    // ==========================================================
    // PRIVATE — helpers
    // ==========================================================

    private function verifyHmac(string $rawBody): bool
    {
        $header     = $_SERVER['HTTP_X_HARAVAN_HMACSHA256'] ?? '';
        $calculated = base64_encode(hash_hmac('sha256', $rawBody,$_ENV['HARAVAN_APP_SECRET'], true));
        return hash_equals($calculated, $header);
    }

    private function callHaravanApi(string $method, string $url, string $token): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_POSTFIELDS     => $method === 'POST' ? json_encode([]) : null,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
            ],
        ]);
        $response = curl_exec($ch);
        $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['http_code' => $code, 'data' => json_decode($response, true)];
    }

    private function getDefaultBranch(string $orgId): ?string
    {
        $stmt = getDB()->prepare('SELECT id FROM branches WHERE organization_id = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$orgId]);
        return $stmt->fetch()['id'] ?? null;
    }

    private function mapPayment(string $gateway): string
    {
        return match(true) {
            str_contains($gateway, 'momo')  => 'momo',
            str_contains($gateway, 'vnpay') => 'vnpay',
            str_contains($gateway, 'zalo')  => 'zalopay',
            str_contains($gateway, 'bank')  => 'transfer',
            default                         => 'cash',
        };
    }

    private function mapStatus(string $status): string
    {
        return match($status) {
            'paid'     => 'paid',
            'refunded' => 'refunded',
            'voided'   => 'cancelled',
            default    => 'draft',
        };
    }

    private function uuid(): string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    private function writeLog(string $topic, string $orgId, string $status, string $error = ''): void
    {
        $dir = __DIR__ . '/../../logs';
        if (!is_dir($dir)) mkdir($dir, 0775, true);

        $line = date('Y-m-d H:i:s') . " | {$status} | org:{$orgId} | {$topic}";
        if ($error) $line .= " | {$error}";

        file_put_contents($dir . '/haravan_webhook.log', $line . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}