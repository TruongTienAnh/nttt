<?php
namespace App\Controllers;

class InvoiceBusinessController
{
    public function Index()
    {
        $orgId = $_SESSION['organization_id'] ?? "e027cf6e-538d-4257-9691-068b36e280f8";
        
        // CHỈ LẤY CÁC CỘT CHẮC CHẮN CÓ ĐỂ TRÁNH LỖI (id, date, total)
        $invoices = app()->db->select('invoices', 
            ['[>]customers' => ['customer_id' => 'id']], 
            [
                'invoices.id', 
                'invoices.invoice_date', 
                'invoices.total', 
                'customers.full_name', 
                'customers.phone'
            ], 
            [
                'invoices.organization_id' => $orgId,
                'ORDER' => ['invoices.invoice_date' => 'DESC']
            ]
        );
        return view('business/invoice', ['invoices' => $invoices]);
    }

    public function Show($id)
    {
        $orgId = $_SESSION['organization_id'] ?? "e027cf6e-538d-4257-9691-068b36e280f8";
        
        // CHIẾN THUẬT MỚI: Dùng SELECT * cho từng bảng độc lập, dập tắt mọi lỗi SQL
        $invoice = app()->db->get('invoices', '*', [
            'id' => $id,
            'organization_id' => $orgId
        ]);

        if (!$invoice) return "<div class='p-3 text-danger'>Không tìm thấy hóa đơn.</div>";

        // Lấy thông tin khách hàng nếu có
        $customer = [];
        if (!empty($invoice['customer_id'])) {
            $customer = app()->db->get('customers', '*', ['id' => $invoice['customer_id']]);
        }

        // Lấy chi tiết sản phẩm
        $items = app()->db->select('invoice_items', '*', ['invoice_id' => $id]);

        // Trả về view với 3 biến riêng biệt
        return view('business/invoice-view', [
            'invoice' => $invoice, 
            'customer' => $customer, 
            'items' => $items
        ]);
    }
}