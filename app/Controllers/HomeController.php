<?php
namespace App\Controllers;

class HomeController extends BaseController 
{
    public function index() {
        // Chặn nếu chưa đăng nhập
        if (!isset($this->user)) {
            header('Location: /login');
            exit;
        }
        // Lấy bộ lọc chi nhánh an toàn
        $where = $this->getSecureBranchFilter();
        $whereInv = $this->getSecureBranchFilter('invoices');

        $thisMonth = date('Y-m');
        $today = date('Y-m-d');

        // ==========================================
        // 1. TÍNH TOÁN CÁC KPI TỔNG QUAN THÁNG NÀY
        // ==========================================
        
        // Doanh thu tháng này
        $whereThisMonth = $where;
        $whereThisMonth['invoice_date[~]'] = $thisMonth . '%';
        $revenueThisMonth = (float)($this->db->sum('invoices', 'total', $whereThisMonth) ?? 0);

        // Doanh thu hôm nay
        $whereToday = $where;
        $whereToday['invoice_date[~]'] = $today . '%';
        $revenueToday = (float)($this->db->sum('invoices', 'total', $whereToday) ?? 0);

        // Số khách hàng mới tháng này
        $whereCustomers = $where;
        $whereCustomers['created_at[~]'] = $thisMonth . '%';
        $newCustomers = (int)$this->db->count('customers', $whereCustomers);

        // Số hóa đơn tháng này
        $ordersThisMonth = (int)$this->db->count('invoices', $whereThisMonth);

        // ==========================================
        // 2. DỮ LIỆU BIỂU ĐỒ (7 NGÀY GẦN NHẤT)
        // ==========================================
        $chartLabels = [];
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $chartLabels[] = date('d/m', strtotime($date));

            $whereDay = $where;
            $whereDay['invoice_date[~]'] = $date . '%';
            $chartData[] = (float)($this->db->sum('invoices', 'total', $whereDay) ?? 0);
        }

        // ==========================================
        // 3. DANH SÁCH GIAO DỊCH MỚI NHẤT
        // ==========================================
        $recentInvoices = $this->db->select('invoices', [
            '[>]customers' => ['customer_id' => 'id'],
            '[>]branches' => ['branch_id' => 'id']
        ], [
            'invoices.id',
            'invoices.invoice_date',
            'invoices.total',
            'customers.full_name',
            'branches.name(branch_name)'
        ], array_merge($whereInv, [
            'ORDER' => ['invoices.invoice_date' => 'DESC'],
            'LIMIT' => 6 // Lấy 6 đơn mới nhất
        ]));

        if (!$recentInvoices) $recentInvoices = []; // Bảo vệ mảng rỗng

        // Tính lời chào theo giờ
        $hour = date('H');
        $greeting = 'Chào buổi sáng';
        if ($hour >= 12 && $hour < 18) $greeting = 'Chào buổi chiều';
        elseif ($hour >= 18) $greeting = 'Chào buổi tối';

        return view('home/home', compact(
            'greeting', 'revenueThisMonth', 'revenueToday', 'newCustomers', 
            'ordersThisMonth', 'chartLabels', 'chartData', 'recentInvoices'
        ));
    }
}