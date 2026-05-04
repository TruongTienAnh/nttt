<?php
namespace App\Controllers;

class AlertController extends BaseController
{
    // =========================================================
    // QUẢN LÝ QUY TẮC CẢNH BÁO (CRUD)
    // =========================================================

    public function Index()
    {
        $this->requirePermission('alerts');
        $where = [
            'organization_id' => $this->orgId,
            'deleted' => 0, // <--- THÊM: Chỉ lấy quy tắc chưa bị xóa
            'ORDER' => ['created_at' => 'DESC']
        ];

        if ($this->branchId !== 'all') {
            $where['AND']['OR'] = [
                'target_branches' => ['', null],
                'target_branches[~]' => $this->branchId
            ];
        }

        $rules = $this->db->select('alert_rules', '*', $where);

        return view('config/alert-rules', ['rules' => $rules]);
    }

    public function Create()
    {
        $this->requirePermission('alerts.add');
        $userId = $this->user['id'] ?? 0;
        $permissionId = $this->user['permission_id'] ?? 0;

        if ($permissionId == 1) {
            $branches = $this->db->select('branches', ['id', 'name'], ['organization_id' => $this->orgId, 'deleted' => 0, 'ORDER' => ['name' => 'ASC']]);
        } else {
            $branches = $this->db->select('branches', ['[>]brands_linkables' => ['id' => 'branch_id']], ['branches.id', 'branches.name'], 
                ['brands_linkables.account_id' => $userId, 'branches.organization_id' => $this->orgId, 'branches.deleted' => 0, 'ORDER' => ['branches.name' => 'ASC']]
            );
        }

        return view('config/alert-rule-post', [
            'rule' => null,
            'branches' => $branches,
            'currentBranchId' => $this->branchId // <--- THÊM: Truyền xuống View
        ]);
    }

    public function Edit($id)
    {
        $this->requirePermission('alerts.edit');
        
        // <--- THÊM: Đảm bảo không sửa quy tắc đã xóa
        $rule = $this->db->get('alert_rules', '*', ['id' => $id, 'organization_id' => $this->orgId, 'deleted' => 0]); 
        if (!$rule) return "Không tìm thấy dữ liệu hoặc đã bị xóa!";

        $userId = $this->user['id'] ?? 0;
        $permissionId = $this->user['permission_id'] ?? 0;

        if ($permissionId == 1) {
            $branches = $this->db->select('branches', ['id', 'name'], ['organization_id' => $this->orgId, 'deleted' => 0, 'ORDER' => ['name' => 'ASC']]);
        } else {
            $branches = $this->db->select('branches', ['[>]brands_linkables' => ['id' => 'branch_id']], ['branches.id', 'branches.name'], 
                ['brands_linkables.account_id' => $userId, 'branches.organization_id' => $this->orgId, 'branches.deleted' => 0, 'ORDER' => ['branches.name' => 'ASC']]
            );
        }

        return view('config/alert-rule-post', [
            'rule' => $rule,
            'branches' => $branches,
            'currentBranchId' => $this->branchId // <--- THÊM: Truyền xuống View
        ]);
    }

    public function Store()
    {
        $this->requirePermission('alerts.add');
        $validator = app()->validate(['title' => 'required', 'threshold_value' => 'required']);
        if ($validator->fails()) return response()->json(['status' => 'error', 'alert' => $validator->first()], 400);

        $this->db->insert('alert_rules', [
            'id'              => uuid(),
            'organization_id' => $this->orgId,
            'title'           => app()->xss->clean(request('title')),
            'module'          => request('module'),
            'metric'          => request('metric'),
            'condition_type'  => request('condition_type'),
            'threshold_value' => str_replace(',', '', request('threshold_value')),
            'time_frame'      => request('time_frame'),
            'target_branches' => request('target_branches'), 
            'is_active'       => request('is_active') ? 1 : 0,
            'deleted'         => 0, // <--- THÊM: Mặc định là 0
            'created_at'      => date('Y-m-d H:i:s')
        ]);

        return response()->json(['status' => 'success', 'alert' => 'Tạo quy tắc cảnh báo thành công', 'redirect' => '/config/alerts']);
    }

    public function Update($id)
    {
        $this->requirePermission('alerts.edit');
        $validator = app()->validate(['title' => 'required', 'threshold_value' => 'required']);
        if ($validator->fails()) return response()->json(['status' => 'error', 'alert' => $validator->first()], 400);

        $this->db->update('alert_rules', [
            'title'           => app()->xss->clean(request('title')),
            'module'          => request('module'),
            'metric'          => request('metric'),
            'condition_type'  => request('condition_type'),
            'threshold_value' => str_replace(',', '', request('threshold_value')),
            'time_frame'      => request('time_frame'),
            'target_branches' => request('target_branches'),
            'is_active'       => request('is_active') ? 1 : 0
        ], ['id' => $id, 'organization_id' => $this->orgId]);

        return response()->json([
            'status' => 'success', 
            'alert' => 'Cập nhật quy tắc thành công',
            'redirect' => '/config/alerts'
        ]);
    }

    public function Delete($id)
    {
        $this->requirePermission('alerts.delete');
        $this->db->update('alert_rules', ['deleted' => 1], ['id' => $id, 'organization_id' => $this->orgId]);
        return response()->json(['status' => 'success', 'alert' => 'Đã xóa quy tắc', 'redirect' => '/config/alerts']);
    }

    public function Toggle($id)
    {
        $this->requirePermission('alerts.edit');
        $status = request('status') ? 1 : 0;
        $this->db->update('alert_rules', ['is_active' => $status], ['id' => $id, 'organization_id' => $this->orgId]);
        return response()->json(['status' => 'success', 'alert' => 'Đã cập nhật trạng thái']);
    }

    // =========================================================
    // ALERT ENGINE: HỆ THỐNG QUÉT VÀ ĐÁNH GIÁ THÔNG MINH
    // =========================================================

    public function RunScanner()
    {
        $rules = $this->db->select('alert_rules', '*', [
            'organization_id' => $this->orgId,
            'is_active' => 1,
            'deleted' => 0 
        ]);

        $triggeredCount = 0;

        foreach ($rules as $rule) {
            // 1. Xác định danh sách chi nhánh cần kiểm tra cho quy tắc này
            if (empty($rule['target_branches'])) {
                // Nếu rỗng -> Áp dụng cho mọi chi nhánh (Lấy toàn bộ ID)
                $branches = $this->db->select('branches', ['id', 'name'], ['organization_id' => $this->orgId, 'deleted' => 0]);
            } else {
                $bIds = explode(',', $rule['target_branches']);
                $branches = $this->db->select('branches', ['id', 'name'], ['id' => $bIds]);
            }

            // 2. Chạy vòng lặp tính toán ĐỘC LẬP cho TỪNG chi nhánh
            foreach ($branches as $branch) {
                // Chống Spam: Hôm nay chi nhánh này đã bị báo động vì quy tắc này chưa?
                $alreadyAlerted = $this->db->has('alert_logs', [
                    'rule_id' => $rule['id'],
                    'branch_id' => $branch['id'],
                    'created_at[>=]' => date('Y-m-d 00:00:00')
                ]);
                
                if ($alreadyAlerted) continue;

                $currentValue = 0;
                $where = [
                    'organization_id' => $this->orgId, 
                    'branch_id' => $branch['id'], // <- ĐIỂM CHUẨN: Lọc đúng chi nhánh
                ];

                // --- LOGIC CHI PHÍ ---
                if ($rule['module'] == 'expenses') {
                    $where['deleted'] = 0;
                    if ($rule['time_frame'] == 'this_month') $where['expense_date[>=]'] = date('Y-m-01');
                    elseif ($rule['time_frame'] == 'today') $where['expense_date'] = date('Y-m-d');
                    
                    if ($rule['metric'] == 'sum_amount') {
                        $currentValue = $this->db->sum('expenses', 'amount', $where);
                    }
                }
                
                // --- LOGIC DOANH THU ---
                elseif ($rule['module'] == 'invoices') {
                    if ($rule['time_frame'] == 'this_month') $where['invoice_date[>=]'] = date('Y-m-01 00:00:00');
                    elseif ($rule['time_frame'] == 'today') $where['invoice_date[>=]'] = date('Y-m-d 00:00:00');

                    if ($rule['metric'] == 'sum_amount') {
                        $currentValue = $this->db->sum('invoices', 'total', $where);
                    }
                }

                // 3. Đánh giá (Evaluate)
                $isViolated = false;
                $threshold = (float)$rule['threshold_value'];
                $currentValue = (float)$currentValue;

                if ($rule['condition_type'] == '>' && $currentValue > $threshold) $isViolated = true;
                if ($rule['condition_type'] == '<' && $currentValue < $threshold) $isViolated = true;

                // 4. Phát tín hiệu
                if ($isViolated) {
                    $message = "⚠️ [{$branch['name']}] {$rule['title']}! Hiện tại: " . number_format($currentValue) . " (Ngưỡng: " . number_format($threshold) . ").";
                    
                    $this->db->insert('alert_logs', [
                        'id'              => uuid(),
                        'rule_id'         => $rule['id'],
                        'organization_id' => $this->orgId,
                        'branch_id'       => $branch['id'], // Lưu ID chi nhánh vào Log
                        'message'         => $message,
                        'is_read'         => 0,
                        'created_at'      => date('Y-m-d H:i:s')
                    ]);
                    $triggeredCount++;
                }
            }
        }

        return response()->json(['status' => 'success', 'scanned' => count($rules), 'triggered' => $triggeredCount]);
    }

    // =========================================================
    // API CHUÔNG THÔNG BÁO TẠI TOPBAR
    // =========================================================

    public function GetUnreadAlerts()
    {
        $userId = $this->user['id'] ?? 0;
        $permissionId = $this->user['permission_id'] ?? 0;

        $where = [
            'organization_id' => $this->orgId,
            'is_read' => 0,
            'ORDER' => ['created_at' => 'DESC'],
            'LIMIT' => 15
        ];

        // Nếu KHÔNG phải Super Admin -> Chỉ hiện cảnh báo của các chi nhánh được phép
        if ($permissionId != 1) {
            $branches = $this->db->select('brands_linkables', 'branch_id', ['account_id' => $userId]);
            if (empty($branches)) $branches = [-1]; // Chặn nếu không có quyền
            $where['branch_id'] = $branches;
        }

        $alerts = $this->db->select('alert_logs', '*', $where);

        return response()->json(['status' => 'success', 'count' => count($alerts), 'data' => $alerts]);
    }

    public function MarkAlertRead($id)
    {
        $this->db->update('alert_logs', ['is_read' => 1], ['id' => $id, 'organization_id' => $this->orgId]);
        return response()->json(['status' => 'success']);
    }
}