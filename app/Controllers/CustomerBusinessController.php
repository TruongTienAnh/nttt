<?php
namespace App\Controllers;

class CustomerBusinessController
{
    public function Index()
    {
        $orgId = $_SESSION['organization_id'] ?? "e027cf6e-538d-4257-9691-068b36e280f8";
        $customers = app()->db->select('customers', '*', [
            'organization_id' => $orgId,
            'ORDER' => ['id' => 'DESC']
        ]);
        return view('business/customer', ['customers' => $customers]);
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