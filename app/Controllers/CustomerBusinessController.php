<?php
namespace App\Controllers;

class CustomerBusinessController extends BaseController
{
    public function Index()
    {
        // 1. Tự động lọc danh sách khách theo chi nhánh
        $where = $this->branchFilter([
            'ORDER' => ['created_at' => 'DESC']
        ]);
        
        $customers = $this->db->select('customers', '*', $where);

        // 2. Tính toán các chỉ số (Tổng khách, Khách mới tháng này) theo đúng chi nhánh
        $totalCustomers = $this->db->count('customers', $this->branchFilter());
        
        $currentMonth = date('Y-m');
        $whereNew = $this->branchFilter([
            'created_at[~]' => $currentMonth . '%' // Tìm những ngày tạo chứa "YYYY-MM"
        ]);
        $newCustomers = $this->db->count('customers', $whereNew);

        // 3. Trả về view
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