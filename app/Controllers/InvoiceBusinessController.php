<?php
namespace App\Controllers;

class InvoiceBusinessController extends BaseController
{
    public function Index()
    {
        // Truyền thêm chữ 'invoices' để hàm tự động sinh ra 'invoices.organization_id'
        $where = $this->branchFilter([
            'ORDER' => ['invoices.invoice_date' => 'DESC']
        ], 'invoices');

        $invoices = $this->db->select('invoices', 
            ['[>]customers' => ['customer_id' => 'id']], 
            [
                'invoices.id', 
                'invoices.invoice_date', 
                'invoices.total', 
                'invoices.invoice_no',
                'invoices.status',
                'invoices.payment_method',
                'invoices.source',
                'customers.full_name', 
                'customers.phone'
            ], 
            $where
        );

        return view('business/invoice', ['invoices' => $invoices]);
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