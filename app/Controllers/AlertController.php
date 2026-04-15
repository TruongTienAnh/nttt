<?php
namespace App\Controllers;

class AlertController extends BaseController 
{
    // --- 1. RỦI RO CHI PHÍ (Tiền điện / Hoàn hủy tăng vọt) ---
    public function CostRisk() 
    {
        if (!$this->orgId) { header('Location: /login'); exit; }
        
        $params = ['org' => $this->orgId];
        $branchSql = $this->branchId !== 'all' ? " AND branch_id = '{$this->branchId}' " : "";

        // So sánh tổng chi phí tháng này với tháng trước
        $costData = $this->db->query("
            SELECT 
                SUM(CASE WHEN DATE_FORMAT(expense_date, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m') THEN amount ELSE 0 END) as this_month,
                SUM(CASE WHEN DATE_FORMAT(expense_date, '%Y-%m') = DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH), '%Y-%m') THEN amount ELSE 0 END) as last_month
            FROM expenses 
            WHERE organization_id = :org $branchSql AND deleted = 0
            AND expense_date >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH), '%Y-%m-01')
        ", $params)->fetch();

        $thisMonthCost = $costData ? (float)$costData['this_month'] : 0;
        $lastMonthCost = $costData ? (float)$costData['last_month'] : 0;

        // Cảnh báo nếu chi phí tháng này tăng hơn 30% so với tháng trước
        $isAlert = false;
        $growthRate = 0;
        if ($lastMonthCost > 0) {
            $growthRate = (($thisMonthCost - $lastMonthCost) / $lastMonthCost) * 100;
            if ($growthRate > 30) $isAlert = true; 
        }

        return view('reports/cost-risk', compact('thisMonthCost', 'lastMonthCost', 'growthRate', 'isAlert'));
    }

    // --- 2. RỦI RO THẤT THOÁT (Khách đông nhưng ít bill) ---
    public function LossRisk() 
    {
        if (!$this->orgId) { header('Location: /login'); exit; }
        
        $params = ['org' => $this->orgId];
        $branchSql = $this->branchId !== 'all' ? " AND branch_id = '{$this->branchId}' " : "";

        // Lấy số bill hôm nay
        $billData = $this->db->query("
            SELECT COUNT(id) as total_bills 
            FROM invoices 
            WHERE organization_id = :org $branchSql 
            AND DATE(invoice_date) = CURDATE()
        ", $params)->fetch();
        
        $totalBills = $billData ? (int)$billData['total_bills'] : 0;

        // LƯU Ý: Theo sơ đồ của bạn, phần này cần "Camera AI (Đếm footfall)". 
        // Hiện tại vì chưa có bảng footfall, mình giả lập data trả về từ API Camera là 150 lượt vào.
        $footfallTraffic = 150; 

        $conversionRate = $footfallTraffic > 0 ? ($totalBills / $footfallTraffic) * 100 : 0;
        
        // Cảnh báo nếu Tỷ lệ chuyển đổi < 10% (Tức là 100 người vào mà chưa tới 10 người mua)
        $isAlert = ($footfallTraffic > 50 && $conversionRate < 10);

        return view('reports/loss-risk', compact('totalBills', 'footfallTraffic', 'conversionRate', 'isAlert'));
    }

    // --- 3. BÁO ĐỘNG ĐỎ (Doanh thu chạm đáy 7 ngày) ---
    public function RedAlert() 
    {
        if (!$this->orgId) { header('Location: /login'); exit; }
        
        $params = ['org' => $this->orgId];
        $branchSql = $this->branchId !== 'all' ? " AND branch_id = '{$this->branchId}' " : "";

        // Tính tổng doanh thu 7 ngày qua
        $revenue7DaysData = $this->db->query("
            SELECT SUM(total) as rev 
            FROM invoices 
            WHERE organization_id = :org $branchSql 
            AND invoice_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ", $params)->fetch();

        $revenue7Days = $revenue7DaysData ? (float)$revenue7DaysData['rev'] : 0;

        // Ngưỡng báo động đỏ (Ví dụ: Doanh thu 7 ngày < 5.000.000đ thì hú còi)
        $criticalThreshold = 5000000;
        $isAlert = $revenue7Days < $criticalThreshold;

        return view('reports/red-alert', compact('revenue7Days', 'criticalThreshold', 'isAlert'));
    }
}