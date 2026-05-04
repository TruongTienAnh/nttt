<?php
namespace App\Controllers;

class CustomerBusinessController extends BaseController
{
    public function Index()
    {
        $this->requirePermission('customers');

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
            if (empty($allowedIds)) $allowedIds = [-1]; // Chặn truy vấn nếu không được gán chi nhánh nào
        }

        // ==========================================================
        // 2. XÂY DỰNG ĐIỀU KIỆN LỌC (CHỐNG HACK & RÒ RỈ DATA)
        // ==========================================================
        $where = [
            'organization_id' => $this->orgId,
            'deleted' => 0 // Giả sử bảng customers có cột deleted (có thể bỏ nếu không dùng)
        ];

        // Lọc theo chi nhánh đang chọn trên Session ($this->branchId)
        if ($this->branchId !== 'all') {
            // Nếu nhân viên cố tình đổi tham số sang chi nhánh không thuộc quyền -> Ép về -1
            if ($permissionId == 1 || in_array($this->branchId, $allowedIds)) {
                $where['branch_id'] = $this->branchId;
            } else {
                $where['branch_id'] = -1; 
            }
        } else {
            // NẾU CHỌN "TẤT CẢ" VÀ LÀ NHÂN VIÊN -> Ép điều kiện IN(...) các chi nhánh được phép
            if ($permissionId != 1) {
                $where['branch_id'] = $allowedIds;
            }
        }

        // ==========================================================
        // 3. THỰC THI TRUY VẤN
        // ==========================================================
        $whereList = $where;
        $whereList['ORDER'] = ['created_at' => 'DESC'];
        
        $customers = $this->db->select('customers', '*', $whereList);

        // ==========================================================
        // 4. TÍNH TOÁN CHỈ SỐ
        // ==========================================================
        $totalCustomers = $this->db->count('customers', $where);
        
        $currentMonth = date('Y-m');
        $whereNew = $where;
        $whereNew['created_at[~]'] = $currentMonth . '%';
        $newCustomers = $this->db->count('customers', $whereNew);

        // ==========================================================
        // 5. TRẢ VỀ VIEW
        // ==========================================================
        return view('business/customer', [
            'customers'      => $customers,
            'totalCustomers' => $totalCustomers,
            'newCustomers'   => $newCustomers
        ]);
    }

    public function Show($id)
    {
        $orgId = $_SESSION['organization_id'] ?? "e027cf6e-538d-4257-9691-068b36e280f8";
        // Lấy toàn bộ (*) dữ liệu thay vì chỉ vài trường
        $customer = app()->db->get('customers', '*', [
            'id' => $id,
            'organization_id' => $orgId
        ]);

        if (!$customer) return "<div class='p-3 text-danger'>Không tìm thấy khách hàng.</div>";
        return view('business/customer-view', ['customer' => $customer]);
    }
}