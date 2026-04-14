<?php

namespace App\Controllers;

use App\Jobs\ProcessHaravanWebhookJob;

class HaravanWebhookController
{
    // ==========================================================
    // PUBLIC — OAuth callback
    // ==========================================================

    public function callback(): void
    {
        $code  = $_GET['code']  ?? '';
        $error = $_GET['error'] ?? '';
        $shopFromUrl = $_GET['shop'] ?? ''; // Lấy từ ?shop=namtoanthinh.myharavan.com
    
        if ($error || !$code) {
            echo json_encode(['error' => 'User denied or missing code']);
            exit;
        }
    
        // 1. Đổi code lấy Token
        $tokenData = $this->exchangeToken($code);
    
        if (empty($tokenData['access_token'])) {
            echo json_encode(['error' => 'Cannot get access_token', 'data' => $tokenData]);
            exit;
        }
    
        $accessToken = $tokenData['access_token'];
        $orgId = '';
        $orgName = '';
    
        // 2. Giải mã id_token để lấy orgsub (định danh shop) và orgname
        if (!empty($tokenData['id_token'])) {
            $parts = explode('.', $tokenData['id_token']);
            if (count($parts) === 3) {
                // Giải mã phần Payload của JWT
                $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])), true);
                
                // Dựa trên dữ liệu bạn check: orgsub là "namtoanthinh", orgname là "Gunshop.vn"
                $orgId   = $payload['orgsub'] ?? ($payload['org_id'] ?? '');
                $orgName = $payload['orgname'] ?? '';
            }
        }
    
        // 3. Fallback: Nếu trong token không có, lấy từ tham số shop trên URL
        if (empty($orgId) && !empty($shopFromUrl)) {
            $orgId = str_replace('.myharavan.com', '', $shopFromUrl);
        }
    
        if (empty($orgName)) {
            $orgName = 'Shop ' . $orgId;
        }
    
        // 4. Kiểm tra cuối cùng trước khi lưu
        if (empty($orgId)) {
            echo json_encode(['error' => 'Could not determine Shop Slug (orgsub)']);
            exit;
        }
    
        // 5. Lưu vào bảng organizations (cột slug) và integrations
        $this->saveIntegration($orgId, $accessToken, $tokenData);
    
        // 6. Đăng ký nhận dữ liệu đơn hàng
        $this->subscribeWebhook($accessToken);
    
        // 7. Hoàn tất và chuyển hướng
        header('Location: /app/settings?haravan=connected');
        exit;
    }

    // ==========================================================
    // PUBLIC — Webhook handler
    // ==========================================================

    public function handle(): void
    {
        $rawBody = file_get_contents('php://input');
        $payload = json_decode($rawBody, true);
        
    
        if (!$payload) {
            http_response_code(400);
            exit;
        }
    
        // 1. Lấy Header theo cách an toàn nhất
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $topic = $headers['X-Haravan-Topic'] ?? $headers['x-haravan-topic'] ?? $_SERVER['HTTP_X_HARAVAN_TOPIC'] ?? '';
        $haravanOrgId = $headers['X-Haravan-Org-Id'] ?? $headers['x-haravan-org-id'] ?? $_SERVER['HTTP_X_HARAVAN_ORG_ID'] ?? '';
    
        // 2. FALLBACK: Nếu Header bị Nginx nuốt, ta tự tìm Org ID trong Payload
        if (empty($haravanOrgId)) {
            // Haravan luôn để OrgID trong link ảnh sản phẩm hoặc các trường liên quan
            // Ví dụ: ...products/1000343028/...
            if (isset($payload['line_items'][0]['image']['src'])) {
                preg_match('/products\/(\d+)\//', $payload['line_items'][0]['image']['src'], $matches);
                $haravanOrgId = $matches[1] ?? '';
            }
        }
    
        // 3. TRẢ VỀ 200 NGAY (Để Haravan khỏi bắn lại liên tùng tục)
        http_response_code(200);
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
    
        // 4. KIỂM TRA SHOP TRONG DB
        $orgId = $this->resolveOrgId((string)$haravanOrgId);
    
        if (!$orgId) {
            // In log kèm theo ID tìm được để debug cho dễ
            $this->writeLog($topic, (string)$haravanOrgId, 'error', 'Organization not found in DB. Payload ID detected: ' . $haravanOrgId);
            exit;
        }
    
        // 5. XỬ LÝ LOGIC
        try {
            switch ($topic) {
                // --- ĐƠN HÀNG ---
                case 'orders/create':
                    $this->handleOrderCreate($payload, $orgId);
                    break;
                case 'orders/updated':
                case 'orders/paid':
                    $this->handleOrderUpdate($payload, $orgId);
                    break;
                case 'orders/cancelled':
                    $this->handleOrderCancelled($payload, $orgId);
                    break;
                case 'orders/fulfilled':
                    $this->handleOrderFulfilled($payload, $orgId);
                    break;
                case 'orders/delete':
                    $this->handleOrderDelete($payload, $orgId);
                    break;

                // --- KHÁCH HÀNG ---
                case 'customers/create':
                case 'customers/update':
                    $this->handleCustomerUpsert($payload, $orgId);
                    break;
                case 'customers/enable':
                case 'customers/disable':
                    $this->handleCustomerStatus($payload, $orgId, $topic);
                    break;
                case 'customers/delete':
                    $this->handleCustomerDelete($payload, $orgId);
                    break;

                // --- SẢN PHẨM & APP ---
                case 'products/update':
                    $this->handleProductUpdate($payload, $orgId);
                    break;
                case 'products/delete':
                    $this->handleProductDelete($payload, $orgId);
                    break;
                case 'app/uninstalled':
                    $this->handleAppUninstalled($orgId);
                    break;
            }
            $this->writeLog($topic, $orgId, 'success');
        } catch (\Throwable $e) {
            $this->writeLog($topic, $orgId, 'error', 'Logic error: ' . $e->getMessage());
        }
    
        exit;
    }

    // ==========================================================
    // PUBLIC — Debug (test only)
    // ==========================================================

    public function handleDebug(): void
    {
        $rawBody = file_get_contents('php://input');
        $payload = json_decode($rawBody, true);

        if (!$payload) {
            echo json_encode(['error' => 'Invalid JSON']);
            exit;
        }

        $haravanOrgId = (string)($payload['org_id'] ?? '');
        $log          = [];

        // Step 1: Check organization (slug → internal UUID)
        $orgRow = app()->db->get('organizations', ['id', 'name', 'slug'], [
            'OR' => ['id' => $haravanOrgId, 'slug' => $haravanOrgId]
        ]);
        $log['step1_organization']  = $orgRow ?: 'NOT FOUND';
        $log['step1_haravan_orgid'] = $haravanOrgId;

        if (!$orgRow) {
            $log['step1_error'] = 'Organization không tồn tại — cần tạo org với slug = ' . $haravanOrgId;
            echo json_encode(['debug' => $log], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        }

        $orgId = $orgRow['id']; // internal UUID

        // Step 2: Check branches
        $log['step2_internal_orgid'] = $orgId;
        $log['step2_all_branches']   = app()->db->select('branches', ['id', 'name', 'is_active'], [
            'organization_id' => $orgId
        ]) ?: 'NOT FOUND';

        $branchRow = app()->db->get('branches', ['id'], [
            'organization_id' => $orgId,
            'is_active'       => 1
        ]);
        $log['step2_active_branch'] = $branchRow ?: 'NOT FOUND — handleOrderCreate sẽ return sớm ở đây';

        if (!$branchRow) {
            echo json_encode(['debug' => $log], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        }
        $branchId = $branchRow['id'];

        // Step 3: Check duplicate invoice
        $existing = app()->db->get('invoices', ['id'], [
            'invoice_no'      => (string)($payload['id'] ?? ''),
            'organization_id' => $orgId,
        ]);
        $log['step3_duplicate_invoice'] = $existing ?: 'OK — không duplicate';

        if ($existing) {
            echo json_encode(['debug' => $log], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Step 4: Thử insert invoice
        $invoiceId = $this->uuid();
        try {
            app()->db->insert('invoices', [
                'id'              => $invoiceId,
                'organization_id' => $orgId,
                'source'             => $payload['source_name'] ?? 'web',
                'external_id'        => $payload['id'],           // Ví dụ: 1791323925
                'branch_id'       => $branchId,
                'customer_id'     => null,
                'staff_id'        => null,
                'fulfillment_status' => $payload['fulfillment_status'] ?? 'restocking',
                'invoice_no'      => (string)($payload['id'] ?? 'TEST'),
                'subtotal'        => (float)($payload['subtotal_price']  ?? 0),
                'discount'        => (float)($payload['total_discounts'] ?? 0),
                'total'           => (float)($payload['total_price']     ?? 0),
                'payment_method'  => $this->mapPayment($payload['payment_gateway'] ?? ''),
                'status'          => $this->mapStatus($payload['financial_status'] ?? ''),
                'invoice_date'    => $payload['created_at'] ?? date('Y-m-d H:i:s'),
                'created_at'      => date('Y-m-d H:i:s'),
            ]);
            $log['step4_invoice_insert'] = 'SUCCESS — id: ' . $invoiceId;
        } catch (\Throwable $e) {
            $log['step4_exception'] = $e->getMessage();
        }

        // Step 5: Verify
        $log['step5_verify_insert'] = app()->db->get('invoices', ['id', 'invoice_no', 'total'], [
            'id' => $invoiceId
        ]) ?: 'KHÔNG TÌM THẤY SAU KHI INSERT';

        echo json_encode(['debug' => $log], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ==========================================================
    // PUBLIC — Subscribe / Unsubscribe / Logs
    // ==========================================================

    public function subscribe(): void
    {
        $accessToken = $_POST['access_token'] ?? '';
        if (!$accessToken) {
            echo json_encode(['error' => 'access_token required']);
            exit;
        }
        echo json_encode($this->callHaravanApi('POST', 'https://webhook.haravan.com/api/subscribe', $accessToken));
        exit;
    }

    public function getSubscribed(): void
    {
        $accessToken = $_GET['access_token'] ?? '';
        echo json_encode($this->callHaravanApi('GET', 'https://webhook.haravan.com/api/subscribe', $accessToken));
        exit;
    }

    public function unsubscribe(): void
    {
        $accessToken = $_POST['access_token'] ?? '';
        echo json_encode($this->callHaravanApi('DELETE', 'https://webhook.haravan.com/api/subscribe', $accessToken));
        exit;
    }

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

        $invoiceId = $this->uuid();

        try {
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
                'invoice_date'    => (new \DateTime($data['created_at']))
                                    ->setTimezone(new \DateTimeZone('Asia/Ho_Chi_Minh'))
                                    ->format('Y-m-d H:i:s'),
                'created_at'      => date('Y-m-d H:i:s'),
                'source'          => $data['source_name'] ?? 'web',
                'fulfillment_status' => $data['fulfillment_status'] ?? 'restocking',
                'external_id'        => $data['id'],
            ]);
        
        } catch (\Throwable $e) {

            // 🔥 check duplicate bằng message (an toàn hơn code)
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        
                $invoice = app()->db->get('invoices', ['id'], [
                    'invoice_no'      => (string)$data['id'],
                    'organization_id' => $orgId,
                ]);
        
                $invoiceId = $invoice['id'] ?? null;
        
                if (!$invoiceId) return;
        
            } else {
                throw $e;
            }
        }
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
        $dir = __DIR__ . '/../../logs';
        file_put_contents(
            $dir . '/webhooktefast.log',
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
            . PHP_EOL
            . "==================== END ====================" 
            . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
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
                'tags'       => $c['tags'] ?? "",
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
            'tags'                => $c['tags'] ?? "",
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
    // CÁC HÀM BỔ SUNG CHO ĐƠN HÀNG
    // ==========================================================

    private function handleOrderFulfilled(array $data, string $orgId): void
    {
        // Khi đơn hàng được giao thành công, chỉ cần cập nhật trạng thái vận chuyển
        app()->db->update('invoices', [
            'fulfillment_status' => 'fulfilled',
            'updated_at'         => date('Y-m-d H:i:s')
        ], [
            'invoice_no'      => (string)($data['id'] ?? ''),
            'organization_id' => $orgId
        ]);
    }

    private function handleOrderDelete(array $data, string $orgId): void
    {
        $invoiceNo = (string)($data['id'] ?? '');
        if (!$invoiceNo) return;

        $invoice = app()->db->get('invoices', ['id'], [
            'invoice_no'      => $invoiceNo,
            'organization_id' => $orgId
        ]);

        if ($invoice) {
            // Xóa các món trong hóa đơn trước để tránh rác dữ liệu
            app()->db->delete('invoice_items', ['invoice_id' => $invoice['id']]);
            // Xóa hóa đơn chính
            app()->db->delete('invoices', ['id' => $invoice['id']]);
        }
    }

    // ==========================================================
    // CÁC HÀM BỔ SUNG CHO KHÁCH HÀNG
    // ==========================================================

    private function handleCustomerStatus(array $data, string $orgId, string $topic): void
    {
        $haravanCustId = $data['id'] ?? null;
        if (!$haravanCustId) return;

        // Bật/tắt trạng thái (Giả định bảng customers của bạn có cột is_active)
        $isActive = ($topic === 'customers/enable') ? 1 : 0;

        app()->db->update('customers', [
            'is_active'  => $isActive,
            'updated_at' => date('Y-m-d H:i:s')
        ], [
            'haravan_customer_id' => $haravanCustId,
            'organization_id'     => $orgId
        ]);
    }

    private function handleCustomerDelete(array $data, string $orgId): void
    {
        $haravanCustId = $data['id'] ?? null;
        if (!$haravanCustId) return;

        // Khuyên dùng "Xóa mềm" (Soft delete) thay vì xóa cứng (DELETE) 
        // để không làm hỏng dữ liệu các hóa đơn cũ mà khách này từng mua.
        app()->db->update('customers', [
            'is_active'  => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ], [
            'haravan_customer_id' => $haravanCustId,
            'organization_id'     => $orgId
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