<?php
namespace App\Controllers;

class AlertController extends BaseController
{
    public function Index()
    {
        $where = [
            'organization_id' => $this->orgId,
            'ORDER' => ['created_at' => 'DESC']
        ];

        // ĐỒNG BỘ SWITCHER: Lọc theo chi nhánh đang được chọn trên Topbar
        if ($this->branchId !== 'all') {
            $where['AND']['OR'] = [
                'target_branches' => ['', null], // Các quy tắc áp dụng chung cho tất cả
                'target_branches[~]' => $this->branchId // Các quy tắc có dính dáng đến chi nhánh này
            ];
        }

        $rules = $this->db->select('alert_rules', '*', $where);

        return view('config/alert-rules', [
            'rules' => $rules
        ]);
    }

    public function Create()
    {
        $branches = $this->db->select('branches', ['id', 'name'], ['organization_id' => $this->orgId, 'deleted' => 0]);
        return view('config/alert-rule-post', [
            'rule' => null,
            'branches' => $branches
        ]);
    }

    public function Edit($id)
    {
        $rule = $this->db->get('alert_rules', '*', ['id' => $id, 'organization_id' => $this->orgId]);
        if (!$rule) return "Không tìm thấy dữ liệu!";

        $branches = $this->db->select('branches', ['id', 'name'], ['organization_id' => $this->orgId, 'deleted' => 0]);
        return view('config/alert-rule-post', [
            'rule' => $rule,
            'branches' => $branches
        ]);
    }

    public function Store()
    {
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
            'target_branches' => request('target_branches'), // Lưu chuỗi ID chi nhánh
            'is_active'       => request('is_active') ? 1 : 0,
            'created_at'      => date('Y-m-d H:i:s')
        ]);

        return response()->json([
            'status' => 'success', 
            'alert' => 'Tạo quy tắc cảnh báo thành công',
            'redirect' => '/config/alerts'
        ]);
    }

    public function Update($id)
    {
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
        $this->db->delete('alert_rules', ['id' => $id, 'organization_id' => $this->orgId]);
        return response()->json(['status' => 'success', 'alert' => 'Đã xóa quy tắc', 'redirect' => '/config/alerts']);
    }

    public function Toggle($id)
    {
        $status = request('status') ? 1 : 0;
        $this->db->update('alert_rules', ['is_active' => $status], ['id' => $id, 'organization_id' => $this->orgId]);
        return response()->json(['status' => 'success', 'alert' => 'Đã cập nhật trạng thái']);
    }

    // =========================================================
    // ALERT ENGINE: HỆ THỐNG QUÉT VÀ ĐÁNH GIÁ QUY TẮC
    // =========================================================

    public function RunScanner()
    {
        // 1. Lấy toàn bộ quy tắc đang kích hoạt của tổ chức này
        $rules = $this->db->select('alert_rules', '*', [
            'organization_id' => $this->orgId,
            'is_active' => 1
        ]);

        $triggeredCount = 0;

        foreach ($rules as $rule) {
            // Kiểm tra xem hôm nay đã báo động cho quy tắc này chưa (Tránh spam thông báo liên tục)
            $alreadyAlertedToday = $this->db->has('alert_logs', [
                'rule_id' => $rule['id'],
                'created_at[>=]' => date('Y-m-d 00:00:00')
            ]);
            
            if ($alreadyAlertedToday) continue;

            $currentValue = 0;

            // --- KIỂM TRA MODULE CHI PHÍ (EXPENSES) ---
            if ($rule['module'] == 'expenses') {
                $where = ['organization_id' => $this->orgId, 'deleted' => 0];
                
                // Lọc thời gian
                if ($rule['time_frame'] == 'this_month') {
                    $where['expense_date[>=]'] = date('Y-m-01');
                } elseif ($rule['time_frame'] == 'today') {
                    $where['expense_date'] = date('Y-m-d');
                }

                // Lọc chi nhánh
                if (!empty($rule['target_branches'])) {
                    $where['branch_id'] = explode(',', $rule['target_branches']);
                }

                // Tính toán Metric
                if ($rule['metric'] == 'sum_amount') {
                    $currentValue = $this->db->sum('expenses', 'amount', $where);
                }
            }

            // --- THÊM CÁC MODULE KHÁC Ở ĐÂY (INVOICES, CUSTOMERS...) SAU NÀY ---

            // 2. Đánh giá điều kiện (Evaluate)
            $isViolated = false;
            $threshold = (float)$rule['threshold_value'];
            $currentValue = (float)$currentValue;

            if ($rule['condition_type'] == '>' && $currentValue > $threshold) $isViolated = true;
            if ($rule['condition_type'] == '<' && $currentValue < $threshold) $isViolated = true;

            // 3. Nếu vi phạm, ghi vào Log để báo động
            if ($isViolated) {
                $message = "⚠️ Cảnh báo: [{$rule['title']}] đã chạm ngưỡng! Hiện tại: " . number_format($currentValue) . " (Ngưỡng: " . number_format($threshold) . ").";
                
                $this->db->insert('alert_logs', [
                    'id'              => uuid(),
                    'rule_id'         => $rule['id'],
                    'organization_id' => $this->orgId,
                    'message'         => $message,
                    'is_read'         => 0,
                    'created_at'      => date('Y-m-d H:i:s')
                ]);
                $triggeredCount++;
            }
        }

        return response()->json(['status' => 'success', 'scanned' => count($rules), 'triggered' => $triggeredCount]);
    }

    // API Lấy danh sách thông báo chưa đọc (Cho cái Chuông)
    public function GetUnreadAlerts()
    {
        $alerts = $this->db->select('alert_logs', '*', [
            'organization_id' => $this->orgId,
            'is_read' => 0,
            'ORDER' => ['created_at' => 'DESC'],
            'LIMIT' => 10
        ]);

        return response()->json(['status' => 'success', 'count' => count($alerts), 'data' => $alerts]);
    }

    // API Đánh dấu đã đọc
    public function MarkAlertRead($id)
    {
        $this->db->update('alert_logs', ['is_read' => 1], ['id' => $id, 'organization_id' => $this->orgId]);
        return response()->json(['status' => 'success']);
    }
}