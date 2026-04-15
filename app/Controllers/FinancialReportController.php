<?php
namespace App\Controllers;

class FinancialReportController extends BaseController 
{
    // --- 1. BÁO CÁO LỢI NHUẬN RÒNG (NET PROFIT) ---
    public function NetProfit() 
    {
        if (!$this->orgId) { header('Location: /login'); exit; }
        
        $params = ['org' => $this->orgId, 'month' => date('Y-m')];
        $branchSql = $this->branchId !== 'all' ? " AND branch_id = '{$this->branchId}' " : "";

        // Lấy doanh thu
        $revData = $this->db->query("SELECT SUM(total) as rev FROM invoices WHERE organization_id = :org $branchSql AND DATE_FORMAT(invoice_date, '%Y-%m') = :month", $params)->fetch();
        $revenue = $revData ? (float)$revData['rev'] : 0;

        // Lấy chi phí
        $expData = $this->db->query("SELECT SUM(amount) as exp FROM expenses WHERE organization_id = :org $branchSql AND DATE_FORMAT(expense_date, '%Y-%m') = :month AND deleted = 0", $params)->fetch();
        $expenses = $expData ? (float)$expData['exp'] : 0;

        $netProfit = $revenue - $expenses;

        // Biểu đồ Trend 6 tháng
        $trendData = $this->db->query("
            SELECT m.month, COALESCE(SUM(i.total), 0) as revenue
            FROM (SELECT DATE_FORMAT(DATE_SUB(NOW(), INTERVAL n MONTH), '%Y-%m') as month FROM (SELECT 0 as n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5) d) m
            LEFT JOIN invoices i ON DATE_FORMAT(i.invoice_date, '%Y-%m') = m.month AND i.organization_id = :org $branchSql
            GROUP BY m.month ORDER BY m.month ASC
        ", ['org' => $this->orgId])->fetchAll();

        return view('reports/net-profit', compact('revenue', 'expenses', 'netProfit', 'trendData'));
    }

    // --- 2. BÁO CÁO ĐIỂM HÒA VỐN (BREAK-EVEN) ---
    public function BreakEven() 
    {
        if (!$this->orgId) { header('Location: /login'); exit; }
        
        $params = ['org' => $this->orgId, 'month' => date('Y-m')];
        $branchSql = $this->branchId !== 'all' ? " AND branch_id = '{$this->branchId}' " : "";

        // Doanh thu
        $revenue = $this->db->query("SELECT SUM(total) as rev FROM invoices WHERE organization_id = :org $branchSql AND DATE_FORMAT(invoice_date, '%Y-%m') = :month", $params)->fetch()['rev'] ?? 0;

        // Bóc tách Chi phí Cố định (Lương, Mặt bằng) và Biến phí (Ads, Khác)
        $expenseData = $this->db->query("
            SELECT category, SUM(amount) as amt FROM expenses 
            WHERE organization_id = :org $branchSql AND DATE_FORMAT(expense_date, '%Y-%m') = :month AND deleted = 0 GROUP BY category
        ", $params)->fetchAll();

        $fixedCost = 0; $variableCost = 0;
        foreach ($expenseData as $e) {
            if (in_array($e['category'], ['rent', 'salary'])) $fixedCost += $e['amt'];
            else $variableCost += $e['amt'];
        }

        // Tính toán
        $grossMarginRatio = $revenue > 0 ? ($revenue - $variableCost) / $revenue : 0;
        $breakEvenPoint = $grossMarginRatio > 0 ? $fixedCost / $grossMarginRatio : $fixedCost;
        
        // Tiến độ hòa vốn (%)
        $progress = $breakEvenPoint > 0 ? min(100, ($revenue / $breakEvenPoint) * 100) : 0;

        return view('reports/break-even', compact('revenue', 'fixedCost', 'variableCost', 'breakEvenPoint', 'progress'));
    }

    // --- 3. DỰ BÁO DOANH THU (REVENUE FORECAST) ---
    public function Forecast() 
    {
        if (!$this->orgId) { header('Location: /login'); exit; }
        
        $params = ['org' => $this->orgId, 'month' => date('Y-m')];
        $branchSql = $this->branchId !== 'all' ? " AND branch_id = '{$this->branchId}' " : "";

        $revenue = $this->db->query("SELECT SUM(total) as rev FROM invoices WHERE organization_id = :org $branchSql AND DATE_FORMAT(invoice_date, '%Y-%m') = :month", $params)->fetch()['rev'] ?? 0;

        $currentDay = (int)date('d');
        $daysInMonth = (int)date('t');
        
        // Dự báo theo tốc độ hiện tại (Run-rate)
        $forecastRevenue = $currentDay > 0 ? ($revenue / $currentDay) * $daysInMonth : 0;

        return view('reports/forecast', compact('revenue', 'forecastRevenue', 'currentDay', 'daysInMonth'));
    }

    // --- 4. BÁO CÁO SO SÁNH ĐA ĐIỂM (LOCATION P&L) ---
    public function LocationPnL() 
    {
        if (!$this->orgId) { header('Location: /login'); exit; }
        
        // Báo cáo này chỉ có ý nghĩa khi xem "Tất cả chi nhánh"
        if ($this->branchId !== 'all') {
            return "<div style='padding: 50px; text-align: center; font-family: sans-serif;'>
                        <h2>Báo cáo So sánh Đa điểm</h2>
                        <p>Vui lòng chọn <b>Tất cả chi nhánh</b> trên thanh công cụ để xem báo cáo này.</p>
                    </div>";
        }

        $params = ['org' => $this->orgId, 'month' => date('Y-m')];
        
        $locationPnL = $this->db->query("
            SELECT 
                b.name as branch_name,
                COALESCE(i.revenue, 0) as revenue,
                COALESCE(e.cost, 0) as cost,
                (COALESCE(i.revenue, 0) - COALESCE(e.cost, 0)) as profit
            FROM branches b
            LEFT JOIN (
                SELECT branch_id, SUM(total) as revenue FROM invoices 
                WHERE organization_id = :org AND DATE_FORMAT(invoice_date, '%Y-%m') = :month GROUP BY branch_id
            ) i ON b.id = i.branch_id
            LEFT JOIN (
                SELECT branch_id, SUM(amount) as cost FROM expenses 
                WHERE organization_id = :org AND DATE_FORMAT(expense_date, '%Y-%m') = :month AND deleted = 0 GROUP BY branch_id
            ) e ON b.id = e.branch_id
            WHERE b.organization_id = :org AND b.deleted = 0
            ORDER BY profit DESC
        ", $params)->fetchAll();

        return view('reports/location-pnl', compact('locationPnL'));
    }

    // --- 5. BÁO CÁO HIỆU QUẢ RÓT VỐN (ROI & PAYBACK) ---
    public function RoiPayback() 
    {
        if (!$this->orgId) { header('Location: /login'); exit; }
        
        $params = ['org' => $this->orgId];
        $branchSql = $this->branchId !== 'all' ? " AND branch_id = '{$this->branchId}' " : "";

        // TỔNG LỢI NHUẬN TỪ TRƯỚC ĐẾN NAY (All time Net Profit)
        $revData = $this->db->query("SELECT SUM(total) as rev FROM invoices WHERE organization_id = :org $branchSql", $params)->fetch();
        $totalRevenue = $revData ? (float)$revData['rev'] : 0;

        $expData = $this->db->query("SELECT SUM(amount) as exp FROM expenses WHERE organization_id = :org $branchSql AND deleted = 0", $params)->fetch();
        $totalExpenses = $expData ? (float)$expData['exp'] : 0;

        $accumulatedProfit = $totalRevenue - $totalExpenses;

        // TẠM TÍNH VỐN ĐẦU TƯ (Giả sử 1 chi nhánh setup tốn 1 Tỷ)
        // Lưu ý: Sau này bạn nên có bảng 'branch_capitals' để truy vấn số này
        $investmentCapital = 1000000000; 

        // Tính ROI (Return on Investment)
        $roi = ($accumulatedProfit / $investmentCapital) * 100;

        // Tính Payback Period (Thời gian hoàn vốn ước tính - dựa trên LN trung bình 3 tháng gần nhất)
        $avgProfit3MonthsData = $this->db->query("
            SELECT (COALESCE(SUM(i.rev), 0) - COALESCE(SUM(e.exp), 0)) / 3 as avg_profit
            FROM (
                SELECT branch_id, SUM(total) as rev FROM invoices 
                WHERE organization_id = :org $branchSql AND invoice_date >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
            ) i
            LEFT JOIN (
                SELECT branch_id, SUM(amount) as exp FROM expenses 
                WHERE organization_id = :org $branchSql AND expense_date >= DATE_SUB(NOW(), INTERVAL 3 MONTH) AND deleted = 0
            ) e ON 1=1
        ", $params)->fetch();
        
        $avgMonthlyProfit = $avgProfit3MonthsData ? (float)$avgProfit3MonthsData['avg_profit'] : 0;
        
        // Tránh chia cho 0 hoặc số âm (nếu đang lỗ thì chưa thể hoàn vốn)
        $paybackMonths = $avgMonthlyProfit > 0 ? ($investmentCapital - $accumulatedProfit) / $avgMonthlyProfit : -1;

        return view('reports/roi', compact('accumulatedProfit', 'investmentCapital', 'roi', 'paybackMonths'));
    }
}