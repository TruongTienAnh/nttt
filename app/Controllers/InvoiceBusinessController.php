<?php
namespace App\Controllers;

class InvoiceBusinessController extends BaseController
{
    public function Index()
    {
        $this->requirePermission('invoices');

        $userId = $this->user['id'] ?? 0;
        $permissionId = $this->user['permission_id'] ?? 0;

        // ==========================================================
        // 1. LẤY MẢNG ID CHI NHÁNH ĐƯỢC PHÉP THEO QUYỀN
        // ==========================================================
        if ($permissionId == 1) {
            $allowedIds = []; // Super Admin: Mảng rỗng = không giới hạn
        } else {
            // Nhân viên: Lấy danh sách ID từ bảng brands_linkables
            $branches = $this->db->select('branches', [
                '[>]brands_linkables' => ['id' => 'branch_id']
            ], [
                'branches.id'
            ], [
                'brands_linkables.account_id' => $userId,
                'branches.organization_id' => $this->orgId,
                'branches.deleted' => 0
            ]);
            
            $allowedIds = array_column($branches, 'id');
            if (empty($allowedIds)) $allowedIds = [-1]; // Chặn nếu không có quyền
        }

        // ==========================================================
        // 2. XÂY DỰNG ĐIỀU KIỆN LỌC
        // ==========================================================
        $where = [
            'invoices.organization_id' => $this->orgId,
            // Thêm 'invoices.deleted' => 0 nếu bảng invoices của bạn có cột deleted
        ];

        // Lọc theo chi nhánh đang chọn trên Session ($this->branchId)
        if ($this->branchId !== 'all') {
            if ($permissionId == 1 || in_array($this->branchId, $allowedIds)) {
                $where['invoices.branch_id'] = $this->branchId;
            } else {
                $where['invoices.branch_id'] = -1; 
            }
        } else {
            // NẾU CHỌN "TẤT CẢ" VÀ LÀ NHÂN VIÊN -> Ép điều kiện IN(...)
            if ($permissionId != 1) {
                $where['invoices.branch_id'] = $allowedIds;
            }
        }

        // ==========================================================
        // 3. THỰC THI TRUY VẤN
        // ==========================================================
        $whereList = $where;
        $whereList['ORDER'] = ['invoices.invoice_date' => 'DESC'];

        $invoices = $this->db->select('invoices', [
            '[>]customers' => ['customer_id' => 'id']
        ], [
            'invoices.id', 
            'invoices.invoice_date', 
            'invoices.total', 
            'invoices.invoice_no',
            'invoices.status',
            'invoices.payment_method',
            'invoices.source',
            'customers.full_name', 
            'customers.phone'
        ], $whereList);

        // ==========================================================
        // 4. TÍNH TOÁN CHỈ SỐ THỐNG KÊ (Dựa trên kết quả đã lọc)
        // ==========================================================
        $totalRevenue = 0;
        foreach($invoices as $inv) {
            $totalRevenue += $inv['total'];
        }
        $totalInvoices = count($invoices);

        // ==========================================================
        // 5. TRẢ VỀ VIEW
        // ==========================================================
        return view('business/invoice', [
            'invoices'      => $invoices,
            'totalRevenue'  => $totalRevenue,
            'totalInvoices' => $totalInvoices
        ]);
    }

    public function Show($id)
    {
        // Nếu câu get bình thường không dùng JOIN thì không cần prefix
        $where = $this->branchFilter(['id' => $id]);
        
        $invoice = $this->db->get('invoices', '*', $where);

        if (!$invoice) {
            return "<div class='p-4 text-center text-danger fw-bold'>Không tìm thấy hóa đơn.</div>";
        }

        $customer = [];
        if (!empty($invoice['customer_id'])) {
            $customer = $this->db->get('customers', '*', ['id' => $invoice['customer_id']]);
        }

        $items = $this->db->select('invoice_items', '*', ['invoice_id' => $id]);

        return view('business/invoice-view', [
            'invoice'  => $invoice, 
            'customer' => $customer, 
            'items'    => $items
        ]);
    }
}