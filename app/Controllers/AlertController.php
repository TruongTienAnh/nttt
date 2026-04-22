<?php
namespace App\Controllers;

class AlertController extends BaseController 
{
    // --- 1. RỦI RO CHI PHÍ (Bóc tách theo hạng mục) ---
    public function CostRisk() 
    {
        if (!$this->orgId) { header('Location: /login'); exit; }
        $params = ['org' => $this->orgId];
        $branchSql = $this->branchId !== 'all' ? " AND branch_id = '{$this->branchId}' " : "";

        $costData = $this->db->query("
            SELECT 
                category,
                SUM(CASE WHEN DATE_FORMAT(expense_date, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m') THEN amount ELSE 0 END) as this_month,
                SUM(CASE WHEN DATE_FORMAT(expense_date, '%Y-%m') = DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH), '%Y-%m') THEN amount ELSE 0 END) as last_month
            FROM expenses 
            WHERE organization_id = :org $branchSql AND deleted = 0
            AND expense_date >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH), '%Y-%m-01')
            GROUP BY category
        ", $params)->fetchAll();

        $thisMonthTotal = 0; $lastMonthTotal = 0;
        $categoryBreakdown = [];

        foreach ($costData as $row) {
            $thisMonthTotal += $row['this_month'];
            $lastMonthTotal += $row['last_month'];
            
            // Logic tính % tăng trưởng thông minh: Nếu tháng trước = 0 thì mặc định là tăng 100%
            $catGrowth = $row['last_month'] > 0 ? (($row['this_month'] - $row['last_month']) / $row['last_month']) * 100 : ($row['this_month'] > 0 ? 100 : 0);
            
            $categoryBreakdown[] = [
                'category' => $row['category'],
                'this_month' => $row['this_month'],
                'last_month' => $row['last_month'],
                'growth' => $catGrowth,
                'diff' => $row['this_month'] - $row['last_month']
            ];
        }

        $growthRate = $lastMonthTotal > 0 ? (($thisMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100 : ($thisMonthTotal > 0 ? 100 : 0);

        // FIX LOGIC Ở ĐÂY: Chỉ báo đỏ khi tăng > 20% VÀ số tiền tăng > 1.000.000đ
        $isAlert = ($growthRate > 20 && ($thisMonthTotal - $lastMonthTotal) > 1000000);

        return view('reports/cost-risk', compact('thisMonthTotal', 'lastMonthTotal', 'growthRate', 'isAlert', 'categoryBreakdown'));
    }

    // --- 2. RỦI RO THẤT THOÁT (Tính toán doanh thu bị lọt) ---
    public function LossRisk() 
    {
        if (!$this->orgId) { header('Location: /login'); exit; }
        
        $params = ['org' => $this->orgId];
        $branchSql = $this->branchId !== 'all' ? " AND branch_id = '{$this->branchId}' " : "";

        // 1. Tách riêng Query và Fetch để chống lỗi Fatal Error 500
        $query = $this->db->query("
            SELECT COUNT(id) as total_bills, SUM(total) as total_revenue
            FROM invoices 
            WHERE organization_id = :org $branchSql 
            AND DATE(invoice_date) = CURDATE()
        ", $params);
        
        $billData = $query ? $query->fetch() : null;
        
        // 2. Ép kiểu an toàn, chống chia cho 0
        $totalBills = $billData ? (int)$billData['total_bills'] : 0;
        $totalRevenue = $billData ? (float)$billData['total_revenue'] : 0;
        $aov = $totalBills > 0 ? $totalRevenue / $totalBills : 0;

        // Giả lập Camera AI đếm được 250 lượt khách hôm nay
        $footfallTraffic = 250; 
        $conversionRate = $footfallTraffic > 0 ? ($totalBills / $footfallTraffic) * 100 : 0;
        
        // 3. Tính toán thiệt hại
        $targetConversion = 30; 
        $targetBills = $footfallTraffic * ($targetConversion / 100);
        $lostBills = max(0, $targetBills - $totalBills);
        $estimatedRevenueLost = $lostBills * $aov;

        $isAlert = ($footfallTraffic > 50 && $conversionRate < 15);

        // LƯU Ý: Nếu file giao diện nằm trong thư mục alerts/, hãy sửa chữ 'reports/' thành 'alerts/' nhé!
        return view('reports/loss-risk', compact(
            'totalBills', 
            'footfallTraffic', 
            'conversionRate', 
            'isAlert', 
            'aov', 
            'estimatedRevenueLost', 
            'targetConversion'
        ));
    }

    // --- 3. BÁO ĐỘNG ĐỎ (Trending 7 ngày & WoW) ---
    public function RedAlert() 
    {
        if (!$this->orgId) { header('Location: /login'); exit; }
        $params = ['org' => $this->orgId];
        $branchSql = $this->branchId !== 'all' ? " AND branch_id = '{$this->branchId}' " : "";

        // Lấy doanh thu 7 ngày qua (Từng ngày để vẽ biểu đồ)
        $dailyData = $this->db->query("
            SELECT DATE_FORMAT(invoice_date, '%d/%m') as day_label, SUM(total) as daily_rev 
            FROM invoices 
            WHERE organization_id = :org $branchSql 
            AND invoice_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
            GROUP BY DATE(invoice_date)
            ORDER BY DATE(invoice_date) ASC
        ", $params)->fetchAll();

        $revenue7Days = array_sum(array_column($dailyData, 'daily_rev'));

        // Lấy tổng doanh thu 7 ngày trước đó (Để so sánh WoW)
        $prev7DaysRev = $this->db->query("
            SELECT SUM(total) as prev_rev 
            FROM invoices 
            WHERE organization_id = :org $branchSql 
            AND invoice_date >= DATE_SUB(CURDATE(), INTERVAL 13 DAY)
            AND invoice_date < DATE_SUB(CURDATE(), INTERVAL 6 DAY)
        ", $params)->fetch()['prev_rev'] ?? 0;

        $wowGrowth = $prev7DaysRev > 0 ? (($revenue7Days - $prev7DaysRev) / $prev7DaysRev) * 100 : 0;

        // Cảnh báo nếu doanh thu 7 ngày qua giảm hơn 20% so với tuần trước
        $isAlert = $wowGrowth < -20;

        return view('reports/red-alert', compact('revenue7Days', 'prev7DaysRev', 'wowGrowth', 'isAlert', 'dailyData'));
    }
}