<?php

namespace App\Jobs;

class ProcessHaravanWebhookJob
{
    protected $topic;
    protected $payload;
    protected $orgId;

    // Khi khởi tạo Job, ta nạp data vào để nó "đóng băng" (Serialize)
    public function __construct($topic, $payload, $orgId)
    {
        $this->topic   = $topic;
        $this->payload = $payload;
        $this->orgId   = $orgId;
    }

    // Class Queue của bạn sẽ tự động gọi hàm này khi "rã đông" Job
    public function handle()
    {
        try {
            switch ($this->topic) {
                case 'orders/create':
                    $this->handleOrderCreate($this->payload, $this->orgId);
                    break;
                case 'orders/updated':
                case 'orders/paid':
                    $this->handleOrderUpdate($this->payload, $this->orgId);
                    break;
                case 'orders/cancelled':
                    $this->handleOrderCancelled($this->payload, $this->orgId);
                    break;
                case 'customers/create':
                case 'customers/update':
                    $this->handleCustomerUpsert($this->payload, $this->orgId);
                    break;
                case 'products/update':
                    $this->handleProductUpdate($this->payload, $this->orgId);
                    break;
                case 'products/delete':
                    $this->handleProductDelete($this->payload, $this->orgId);
                    break;
                case 'app/uninstalled':
                    $this->handleAppUninstalled($this->orgId);
                    break;
            }
            $this->writeLog($this->topic, $this->orgId, 'success');
        } catch (\Throwable $e) {
            $this->writeLog($this->topic, $this->orgId, 'error', 'Job Error: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // DÁN TOÀN BỘ CÁC HÀM PRIVATE TỪ CONTROLLER SANG ĐÂY
    // (handleOrderCreate, handleOrderUpdate, getOrCreateService, upsertCustomer, mapStatus...)
    // Lưu ý: Nhớ copy cả hàm mapPayment, mapStatus, uuid(), writeLog() nhé!
    // =========================================================================
    
    // ==========================================================
    // PRIVATE — Xử lý từng event
    // ==========================================================

    private function handleOrderCreate(array $data, string $orgId): void
    {
        // Lấy branch
        $branchRow = app()->db->get('branches', ['id'], [
            'organization_id' => $orgId,
            'is_active'       => 1
        ]);
        if (!$branchRow) return;
        $branchId = $branchRow['id'];

        // Upsert customer
        $customerId = null;
        if (!empty($data['customer'])) {
            $customerId = $this->upsertCustomer($data['customer'], $orgId, $branchId);
        }

        // Tìm staff
        $staffId = null;
        if (!empty($data['staff_email'])) {
            $staffRow = app()->db->get('users', ['id'], [
                'email'           => $data['staff_email'],
                'organization_id' => $orgId,
            ]);
            $staffId = $staffRow['id'] ?? null;
        }

        // Tránh duplicate
        $exists = app()->db->get('invoices', ['id'], [
            'invoice_no'      => (string)$data['id'],
            'organization_id' => $orgId,
        ]);
        if ($exists) return;

        // Insert invoice
        $invoiceId = $this->uuid();
        app()->db->insert('invoices', [
            'id'              => $invoiceId,
            'organization_id' => $orgId,
            'branch_id'       => $branchId,
            'customer_id'     => $customerId,
            'staff_id'        => $staffId,
            'invoice_no'      => (string)$data['id'],
            'subtotal'        => (float)($data['subtotal_price']  ?? 0),
            'discount'        => (float)($data['total_discounts'] ?? 0),
            'total'           => (float)($data['total_price']     ?? 0),
            'payment_method'  => $this->mapPayment($data['payment_gateway'] ?? ''),
            'status'          => $this->mapStatus($data['financial_status'] ?? ''),
            'invoice_date'    => $data['created_at'] ?? date('Y-m-d H:i:s'),
            'created_at'      => date('Y-m-d H:i:s'),
            'source'          => $data['source_name'] ?? 'web',
            'fulfillment_status' => $data['fulfillment_status'] ?? 'restocking',
            'external_id'        => $data['id'],           // Ví dụ: 1791323925
        ]);

        // Insert line items
        foreach ($data['line_items'] ?? [] as $item) {
            $sku = $item['sku'] ?? '';
            $name = $item['name'] ?? 'Sản phẩm không tên';
        
            // 1. Tìm xem service đã tồn tại chưa
            $svcRow = app()->db->get('services', ['id'], [
                'OR'              => ['sku' => $sku, 'name' => $name],
                'organization_id' => $orgId,
            ]);
        
            // 2. CHƯA CÓ THÌ TẠO MỚI NGAY
            if (!$svcRow) {
                $newServiceId = $this->uuid();
                app()->db->insert('services', [
                    'id'              => $newServiceId,
                    'organization_id' => $orgId,
                    'name'            => $name,
                    'sku'             => $sku,
                    'price'           => (float)($item['price'] ?? 0),
                    'is_active'       => 1,
                    'created_at'      => date('Y-m-d H:i:s'),
                ]);
                $serviceId = $newServiceId;
            } else {
                $serviceId = $svcRow['id'];
            }
        
            // 3. Insert vào invoice_items (Lúc này chắc chắn có serviceId)
            $qty   = (int)($item['quantity'] ?? 1);
            $price = (float)($item['price']  ?? 0);
        
            app()->db->insert('invoice_items', [
                'id'         => $this->uuid(),
                'invoice_id' => $invoiceId,
                'service_id' => $serviceId,
                'name'       => $name,
                'qty'        => $qty,
                'unit_price' => $price,
                'total'      => $qty * $price,
            ]);
        }
    }
    
    private function handleProductDelete(array $data, string $orgId): void
    {
        // Haravan gửi payload: {"id": 123456789}
        $haravanProductId = $data['id'] ?? null;
    
        if ($haravanProductId) {
            app()->db->update('services', [
                'is_active' => 0,
                'updated_at' => date('Y-m-d H:i:s')
            ], [
                'haravan_service_id' => $haravanProductId,
                'organization_id'    => $orgId
            ]);
        }
    }
    
    private function handleOrderCancelled(array $data, string $orgId): void
    {
        app()->db->update('invoices', [
            'status'     => 'cancelled',
            'updated_at' => date('Y-m-d H:i:s')
        ], [
            'invoice_no'      => (string)$data['id'],
            'organization_id' => $orgId
        ]);
    }

    private function handleOrderUpdate(array $data, string $orgId): void
    {
        // 1. Tìm ID hóa đơn nội bộ trước
        $invoice = app()->db->get('invoices', ['id'], [
            'invoice_no'      => (string)$data['id'],
            'organization_id' => $orgId,
        ]);
    
        if (!$invoice) {
            // Nếu không thấy hóa đơn (trường hợp Webhook Create bị lỗi trước đó)
            // thì có thể gọi luôn hàm Create ở đây để tạo mới
            $this->handleOrderCreate($data, $orgId);
            return;
        }
    
        $invoiceId = $invoice['id'];
    
        // 2. Cập nhật "phần đầu" hóa đơn
        app()->db->update('invoices', [
            'status'             => $this->mapStatus($data['financial_status'] ?? ''),
            'fulfillment_status' => $data['fulfillment_status'] ?? 'restocking', // Thêm cột này nếu đã chạy SQL nâng cấp
            'total'              => (float)($data['total_price']      ?? 0),
            'subtotal'           => (float)($data['subtotal_price']   ?? 0),
            'discount'           => (float)($data['total_discounts']  ?? 0),
            'payment_method'     => $this->mapPayment($data['payment_gateway'] ?? ''),
            'updated_at'         => date('Y-m-d H:i:s'),
        ], ['id' => $invoiceId]);
    
        // 3. Cập nhật "phần ruột" (Sản phẩm)
        // Xóa sạch các món cũ để nạp món mới (Tránh tính toán cộng trừ nhức đầu)
        app()->db->delete('invoice_items', ['invoice_id' => $invoiceId]);
    
        foreach ($data['line_items'] ?? [] as $item) {
            $sku = $item['sku'] ?? '';
            $name = $item['name'] ?? '';
    
            // Tự động tạo service nếu bảng services đang rỗng hoặc không tìm thấy SKU
            $serviceId = $this->getOrCreateService($item, $orgId);
    
            $qty   = (int)($item['quantity'] ?? 1);
            $price = (float)($item['price']  ?? 0);
    
            app()->db->insert('invoice_items', [
                'id'         => $this->uuid(),
                'invoice_id' => $invoiceId,
                'service_id' => $serviceId,
                'name'       => $name,
                'qty'        => $qty,
                'unit_price' => $price,
                'total'      => $qty * $price,
            ]);
        }
    }
    
    // Hàm bổ trợ để xử lý việc bảng services bị rỗng
    private function getOrCreateService(array $item, string $orgId): string 
    {
        $sku = $item['sku'] ?? '';
        $name = $item['name'] ?? 'Sản phẩm không tên';
        $haravanProductId = $item['product_id'] ?? null; // ID sản phẩm từ Haravan
    
        // 1. Tìm kiếm ưu tiên theo Haravan Product ID, sau đó mới đến SKU
        $svcRow = null;
        if ($haravanProductId) {
            $svcRow = app()->db->get('services', ['id'], [
                'haravan_service_id' => $haravanProductId,
                'organization_id'    => $orgId
            ]);
        }
    
        if (!$svcRow && !empty($sku)) {
            $svcRow = app()->db->get('services', ['id'], [
                'sku'             => $sku,
                'organization_id' => $orgId
            ]);
        }
    
        // 2. Nếu tìm thấy, trả về ID ngay
        if ($svcRow) return $svcRow['id'];
    
        // 3. Nếu CHƯA CÓ (bảng rỗng), tạo mới và nạp vào DB
        $newId = $this->uuid();
        app()->db->insert('services', [
            'id'                 => $newId,
            'organization_id'    => $orgId,
            'haravan_service_id' => $haravanProductId,
            'name'               => $name,
            'sku'                => $sku,
            'price'              => (float)($item['price'] ?? 0),
            'is_active'          => 1,
            'created_at'         => date('Y-m-d H:i:s'),
        ]);
    
        return $newId;
    }

    private function handleCustomerUpsert(array $data, string $orgId): void
    {
        $branchRow = app()->db->get('branches', ['id'], [
            'organization_id' => $orgId,
            'is_active'       => 1,
        ]);
        if ($branchRow) {
            $this->upsertCustomer($data, $orgId, $branchRow['id']);
        }
    }

    private function upsertCustomer(array $c, string $orgId, string $branchId): ?string
    {
        $haravanCustId = $c['id'] ?? null;
        $phone = !empty($c['phone']) ? $c['phone'] : null;
        $email = !empty($c['email']) ? $c['email'] : null;
        
        // Loại bỏ email mặc định của Haravan/Sàn để tránh gộp nhầm khách
        if ($email === 'guest@haravan.com') {
            $email = null;
        }
    
        // 1. CHIẾN THUẬT TÌM KIẾM 3 LỚP
        $existing = null;
    
        // Lớp 1: Tìm theo ID Haravan (Chính xác 100%)
        if ($haravanCustId) {
            $existing = app()->db->get('customers', ['id'], [
                'organization_id' => $orgId,
                'haravan_customer_id' => $haravanCustId
            ]);
        }
    
        // Lớp 2: Nếu không thấy, tìm theo Số điện thoại (Chính xác 90%)
        if (!$existing && $phone) {
            $existing = app()->db->get('customers', ['id'], [
                'organization_id' => $orgId,
                'phone' => $phone
            ]);
        }
    
        // Lớp 3: Nếu vẫn không thấy, tìm theo Email (Nếu email hợp lệ)
        if (!$existing && $email) {
            $existing = app()->db->get('customers', ['id'], [
                'organization_id' => $orgId,
                'email' => $email
            ]);
        }
    
        $fullName = trim(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? ''));
        if (empty($fullName)) $fullName = 'Khách hàng Haravan';
    
        // 2. XỬ LÝ UPDATE HOẶC INSERT
        if ($existing) {
            app()->db->update('customers', [
                'haravan_customer_id' => $haravanCustId, // Cập nhật ID để lần sau tìm nhanh hơn
                'full_name'  => $fullName,
                'email'      => $email, // Có thể null
                'phone'      => $phone, // Có thể null
                'updated_at' => date('Y-m-d H:i:s'),
            ], ['id' => $existing['id']]);
    
            return $existing['id'];
        }
    
        // Tạo mới nếu hoàn toàn không tìm thấy
        $id = $this->uuid();
        app()->db->insert('customers', [
            'id'                  => $id,
            'organization_id'     => $orgId,
            'branch_id'           => $branchId,
            'haravan_customer_id' => $haravanCustId,
            'full_name'           => $fullName,
            'phone'               => $phone,
            'email'               => $email,
            'source'              => 'haravan',
            'created_at'          => date('Y-m-d H:i:s'),
        ]);
    
        return $id;
    }

    private function handleProductUpdate(array $data, string $orgId): void
    {
        foreach ($data['variants'] ?? [] as $variant) {
            $svcRow = app()->db->get('services', ['id'], [
                'sku'             => $variant['sku'] ?? '',
                'organization_id' => $orgId,
            ]);
            if ($svcRow) {
                app()->db->update('services', [
                    'name'  => $data['title'] ?? '',
                    'price' => (float)($variant['price'] ?? 0),
                ], ['id' => $svcRow['id']]);
            }
        }
    }

    private function handleAppUninstalled(string $orgId): void
    {
        app()->db->update('integrations', ['is_active' => 0], [
            'organization_id' => $orgId,
            'type'            => 'pos_haravan',
        ]);
    }

    // ==========================================================
    // PRIVATE — OAuth helpers
    // ==========================================================

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

    private function saveIntegration(string $orgId, string $accessToken, array $tokenData): void
    {
        // Tìm org theo slug
        $orgRow = app()->db->get('organizations', ['id'], ['slug' => $orgId]);

        if (!$orgRow) {
            $newOrgId = $this->uuid();
            app()->db->insert('organizations', [
                'id'         => $newOrgId,
                'name'       => 'Shop ' . $orgId,
                'slug'       => $orgId,
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $organizationId = $newOrgId;
        } else {
            $organizationId = $orgRow['id'];
        }

        $credentials = json_encode([
            'access_token'   => $accessToken,
            'refresh_token'  => $tokenData['refresh_token'] ?? '',
            'expires_in'     => $tokenData['expires_in']    ?? 0,
            'token_type'     => $tokenData['token_type']    ?? 'Bearer',
            'haravan_org_id' => $orgId,
            'connected_at'   => date('Y-m-d H:i:s'),
        ]);

        $existing = app()->db->get('integrations', ['id'], [
            'organization_id' => $organizationId,
            'type'            => 'pos_haravan',
        ]);

        if ($existing) {
            app()->db->update('integrations', [
                'credentials'    => $credentials,
                'is_active'      => 1,
                'last_synced_at' => date('Y-m-d H:i:s'),
            ], ['id' => $existing['id']]);
        } else {
            app()->db->insert('integrations', [
                'id'              => $this->uuid(),
                'organization_id' => $organizationId,
                'type'            => 'pos_haravan',
                'credentials'     => $credentials,
                'is_active'       => 1,
                'created_at'      => date('Y-m-d H:i:s'),
            ]);
        }
    }

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

    // ==========================================================
    // PRIVATE — Misc helpers
    // ==========================================================

    // Đổi haravan org_id (slug) → internal UUID trong bảng organizations
    private function resolveOrgId(string $haravanOrgId): ?string
    {
        $row = app()->db->get('organizations', ['id'], [
            'OR'        => ['haravan_org_id' => $haravanOrgId, 'slug' => $haravanOrgId],
            'is_active' => 1,
        ]);
        return $row['id'] ?? null;
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

    private function verifyHmac(string $rawBody): bool
    {
        $header     = $_SERVER['HTTP_X_HARAVAN_HMACSHA256'] ?? '';
        $calculated = base64_encode(hash_hmac('sha256', $rawBody, $_ENV['HARAVAN_APP_SECRET'], true));
        return hash_equals($calculated, $header);
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