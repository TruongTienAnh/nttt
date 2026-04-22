<?php
namespace App\Controllers;

class FinancialReportController extends BaseController 
{
    // --- 1. LỢI NHUẬN RÒNG & TỶ TRỌNG CHI NHÁNH ---
    public function NetProfit() 
    {
        if (!$this->orgId) { header('Location: /login'); exit; }
        
        $params = ['org' => $this->orgId, 'month' => date('Y-m')];
        $branchSql = $this->branchId !== 'all' ? " AND branch_id = '{$this->branchId}' " : "";

        $revenue = $this->db->query("SELECT SUM(total) as rev FROM invoices WHERE organization_id = :org $branchSql AND DATE_FORMAT(invoice_date, '%Y-%m') = :month", $params)->fetch()['rev'] ?? 0;
        $expenses = $this->db->query("SELECT SUM(amount) as exp FROM expenses WHERE organization_id = :org $branchSql AND DATE_FORMAT(expense_date, '%Y-%m') = :month AND deleted = 0", $params)->fetch()['exp'] ?? 0;
        $netProfit = $revenue - $expenses;

        $branchBreakdown = [];
        if ($this->branchId === 'all') {
            $branchBreakdown = $this->db->query("
                SELECT b.name, COALESCE(SUM(i.total), 0) as rev
                FROM branches b
                LEFT JOIN invoices i ON i.branch_id = b.id AND DATE_FORMAT(i.invoice_date, '%Y-%m') = :month
                WHERE b.organization_id = :org AND b.deleted = 0
                GROUP BY b.id ORDER BY rev DESC
            ", $params)->fetchAll();
        }

        $trendData = $this->db->query("
            SELECT m.month, COALESCE(SUM(i.total), 0) as revenue
            FROM (SELECT DATE_FORMAT(DATE_SUB(NOW(), INTERVAL n MONTH), '%Y-%m') as month FROM (SELECT 0 as n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5) d) m
            LEFT JOIN invoices i ON DATE_FORMAT(i.invoice_date, '%Y-%m') = m.month AND i.organization_id = :org $branchSql
            GROUP BY m.month ORDER BY m.month ASC
        ", ['org' => $this->orgId])->fetchAll();

        // FIX: Đã bổ sung đường dẫn /finance/
        return view('reports/net-profit', compact('revenue', 'expenses', 'netProfit', 'trendData', 'branchBreakdown'));
    }

    // --- 2. ĐIỂM HÒA VỐN ĐA ĐIỂM ---
    public function BreakEven() 
    {
        if (!$this->orgId) { header('Location: /login'); exit; }
        $params = ['org' => $this->orgId, 'month' => date('Y-m')];
        
        $branches = [];
        if ($this->branchId === 'all') {
            $branches = $this->db->query("SELECT id, name FROM branches WHERE organization_id = :org AND deleted = 0", ['org' => $this->orgId])->fetchAll();
        } else {
            $branches = [['id' => $this->branchId, 'name' => 'Chi nhánh hiện tại']];
        }

        $breakEvenList = [];
        foreach ($branches as $b) {
            $bId = $b['id'];
            
            // FIX: Bổ sung điều kiện organization_id = :org để tránh lỗi sập PDO
            $rev = $this->db->query("SELECT SUM(total) as rev FROM invoices WHERE organization_id = :org AND branch_id = '$bId' AND DATE_FORMAT(invoice_date, '%Y-%m') = :month", $params)->fetch()['rev'] ?? 0;
            $expData = $this->db->query("SELECT category, SUM(amount) as amt FROM expenses WHERE organization_id = :org AND branch_id = '$bId' AND DATE_FORMAT(expense_date, '%Y-%m') = :month AND deleted = 0 GROUP BY category", $params)->fetchAll();
            
            $fixed = 0; $variable = 0;
            foreach ($expData as $e) {
                if (in_array($e['category'], ['rent', 'salary'])) $fixed += $e['amt']; else $variable += $e['amt'];
            }

            $grossMarginRatio = $rev > 0 ? ($rev - $variable) / $rev : 0;
            $bePoint = $grossMarginRatio > 0 ? $fixed / $grossMarginRatio : $fixed;
            $progress = $bePoint > 0 ? min(100, ($rev / $bePoint) * 100) : ($rev > 0 ? 100 : 0);

            $breakEvenList[] = [
                'name' => $b['name'], 'revenue' => $rev, 'fixed' => $fixed, 'variable' => $variable, 'bePoint' => $bePoint, 'progress' => $progress
            ];
        }

        usort($breakEvenList, fn($a, $b) => $b['progress'] <=> $a['progress']);
        return view('reports/break-even', compact('breakEvenList'));
    }

    // --- 3. DỰ BÁO DOANH THU ---
    public function Forecast() 
    {
        if (!$this->orgId) { header('Location: /login'); exit; }
        $params = ['org' => $this->orgId, 'month' => date('Y-m')];
        $branchSql = $this->branchId !== 'all' ? " AND branch_id = '{$this->branchId}' " : "";
        $revenue = $this->db->query("SELECT SUM(total) as rev FROM invoices WHERE organization_id = :org $branchSql AND DATE_FORMAT(invoice_date, '%Y-%m') = :month", $params)->fetch()['rev'] ?? 0;
        
        $currentDay = (int)date('d');
        $daysInMonth = (int)date('t');
        $forecastRevenue = $currentDay > 0 ? ($revenue / $currentDay) * $daysInMonth : 0;
        
        return view('reports/forecast', compact('revenue', 'forecastRevenue', 'currentDay', 'daysInMonth'));
    }

    // --- 4. BẢNG XẾP HẠNG P&L CHI NHÁNH ---
    public function LocationPnL() 
    {
        if (!$this->orgId) { header('Location: /login'); exit; }
        if ($this->branchId !== 'all') {
            return "<div class='alert alert-warning m-5 text-center'>Vui lòng chọn <b>Tất cả chi nhánh</b> trên thanh công cụ để xem bảng xếp hạng so sánh đa điểm.</div>";
        }

        $params = ['org' => $this->orgId, 'month' => date('Y-m')];
        
        $locationPnL = $this->db->query("
            SELECT 
                b.name as branch_name,
                COALESCE(i.revenue, 0) as revenue,
                COALESCE(e.cost, 0) as cost,
                (COALESCE(i.revenue, 0) - COALESCE(e.cost, 0)) as profit,
                CASE WHEN COALESCE(i.revenue, 0) > 0 THEN ((COALESCE(i.revenue, 0) - COALESCE(e.cost, 0)) / COALESCE(i.revenue, 0) * 100) ELSE 0 END as margin
            FROM branches b
            LEFT JOIN (
                SELECT branch_id, SUM(total) as revenue FROM invoices WHERE organization_id = :org AND DATE_FORMAT(invoice_date, '%Y-%m') = :month GROUP BY branch_id
            ) i ON b.id = i.branch_id
            LEFT JOIN (
                SELECT branch_id, SUM(amount) as cost FROM expenses WHERE organization_id = :org AND DATE_FORMAT(expense_date, '%Y-%m') = :month AND deleted = 0 GROUP BY branch_id
            ) e ON b.id = e.branch_id
            WHERE b.organization_id = :org AND b.deleted = 0
            ORDER BY profit DESC
        ", $params)->fetchAll();

        return view('reports/location-pnl', compact('locationPnL'));
    }

    // --- 5. SO SÁNH HIỆU QUẢ RÓT VỐN (ROI) ---
    public function RoiPayback() 
    {
        if (!$this->orgId) { header('Location: /login'); exit; }
        if ($this->branchId !== 'all') {
            return "<div class='alert alert-warning m-5 text-center'>Vui lòng chọn <b>Tất cả chi nhánh</b> để xem báo cáo so sánh tốc độ thu hồi vốn giữa các điểm bán.</div>";
        }

        $params = ['org' => $this->orgId];
        $branches = $this->db->query("SELECT id, name FROM branches WHERE organization_id = :org AND deleted = 0", $params)->fetchAll();
        
        $roiData = [];
        $investmentPerBranch = 1000000000; // Giả sử vốn setup 1 tỷ / chi nhánh.

        foreach ($branches as $b) {
            $bId = $b['id'];
            
            // FIX: Bổ sung điều kiện organization_id = :org
            $rev = $this->db->query("SELECT SUM(total) as rev FROM invoices WHERE organization_id = :org AND branch_id = '$bId'", $params)->fetch()['rev'] ?? 0;
            $exp = $this->db->query("SELECT SUM(amount) as exp FROM expenses WHERE organization_id = :org AND branch_id = '$bId' AND deleted = 0", $params)->fetch()['exp'] ?? 0;
            
            $accumulatedProfit = $rev - $exp;
            $roi = ($accumulatedProfit / $investmentPerBranch) * 100;

            // FIX: Bổ sung organization_id = :org vào các subquery và an toàn hóa lệnh fetch()
            $avgProfit3MData = $this->db->query("
                SELECT (COALESCE((SELECT SUM(total) FROM invoices WHERE organization_id = :org AND branch_id = '$bId' AND invoice_date >= DATE_SUB(NOW(), INTERVAL 3 MONTH)), 0) 
                      - COALESCE((SELECT SUM(amount) FROM expenses WHERE organization_id = :org AND branch_id = '$bId' AND expense_date >= DATE_SUB(NOW(), INTERVAL 3 MONTH) AND deleted = 0), 0)) / 3 as avg_profit
            ", $params)->fetch();
            
            $avgProfit3M = $avgProfit3MData ? (float)$avgProfit3MData['avg_profit'] : 0;
            $paybackMonths = $avgProfit3M > 0 ? ($investmentPerBranch - $accumulatedProfit) / $avgProfit3M : -1;

            $roiData[] = [
                'name' => $b['name'], 'accumulatedProfit' => $accumulatedProfit, 'roi' => $roi, 'avgProfit3M' => $avgProfit3M, 'paybackMonths' => $paybackMonths
            ];
        }

        usort($roiData, fn($a, $b) => $b['roi'] <=> $a['roi']);
        return view('reports/roi', compact('roiData', 'investmentPerBranch'));
    }
}