<?php
namespace App\Controllers;

use Medoo\Medoo;

class FinancialReportController extends BaseController {

    /**
     * Hàm bổ trợ: Trích xuất điều kiện WHERE chi nhánh thành chuỗi SQL an toàn 
     * (Dùng cho các câu query thuần phức tạp)
     */
    private function getRawBranchSql($prefix = '') {
        $where = $this->getSecureBranchFilter($prefix ? trim($prefix, '.') : '');
        $col = $prefix . 'branch_id';
        
        if (isset($where[$col])) {
            $bIds = $where[$col];
            if (is_array($bIds)) {
                if (empty($bIds)) return " AND 1=0 "; // Chặn nếu không có quyền
                return " AND {$col} IN (" . implode(',', array_map('intval', $bIds)) . ") ";
            } elseif ($bIds == -1) {
                return " AND 1=0 "; // Chặn nếu cố tình hack
            } else {
                return " AND {$col} = " . intval($bIds) . " ";
            }
        }
        return ""; // Trả về rỗng nếu Admin xem Tất cả (quét toàn hệ thống)
    }

    // ====================================================
    // 1. BÁO CÁO LÃI LỖ THUẦN (NET PROFIT)
    // ====================================================
    public function NetProfitReport() {
        $this->requirePermission('reports.finance');
        $year = request('year', date('Y'));

        $bSqlInv = $this->getRawBranchSql('i.');
        $bSqlExp = $this->getRawBranchSql('e.');
        $orgId = $this->orgId;

        $revData = $this->db->query("
            SELECT DATE_FORMAT(invoice_date, '%m') as month, SUM(total) as val 
            FROM invoices i 
            WHERE organization_id = '{$orgId}' AND YEAR(invoice_date) = '{$year}' {$bSqlInv} 
            GROUP BY month
        ")->fetchAll();

        $expData = $this->db->query("
            SELECT DATE_FORMAT(expense_date, '%m') as month, SUM(amount) as val 
            FROM expenses e 
            WHERE organization_id = '{$orgId}' AND deleted = 0 AND YEAR(expense_date) = '{$year}' {$bSqlExp} 
            GROUP BY month
        ")->fetchAll();

        $monthlyData = [];
        $totalRev = 0; $totalExp = 0;

        for ($i = 1; $i <= 12; $i++) {
            $m = str_pad($i, 2, '0', STR_PAD_LEFT);
            $rev = 0; $exp = 0;
            
            foreach ($revData as $r) if ($r['month'] === $m) $rev = $r['val'];
            foreach ($expData as $e) if ($e['month'] === $m) $exp = $e['val'];

            $totalRev += $rev;
            $totalExp += $exp;

            $monthlyData[] = [
                'month' => "Tháng $m",
                'revenue' => (float)$rev,
                'expenses' => (float)$exp,
                'profit' => (float)($rev - $exp),
                'margin' => $rev > 0 ? round((($rev - $exp)/$rev)*100, 1) : 0
            ];
        }

        $netProfit = $totalRev - $totalExp;
        $avgMargin = $totalRev > 0 ? round(($netProfit/$totalRev)*100, 2) : 0;

        return view('reports/net-profit', compact('year', 'monthlyData', 'totalRev', 'totalExp', 'netProfit', 'avgMargin'));
    }

    // ====================================================
    // 2. BÁO CÁO P&L & SO SÁNH CHI NHÁNH (LOCATION P&L)
    // ====================================================
    public function LocationPnlReport() {
        $this->requirePermission('reports.finance');
        $from = request('from_date', date('Y-m-01'));
        $to = request('to_date', date('Y-m-d'));
        
        $userId = $this->user['id'] ?? 0;
        $permissionId = $this->user['permission_id'] ?? 0;

        if ($permissionId == 1) {
            $branches = $this->db->select('branches', ['id', 'name'], ['organization_id' => $this->orgId, 'deleted' => 0]);
        } else {
            $branches = $this->db->select('branches', ['[>]brands_linkables' => ['id' => 'branch_id']], ['branches.id', 'branches.name'], [
                'brands_linkables.account_id' => $userId, 'branches.organization_id' => $this->orgId, 'branches.deleted' => 0
            ]);
        }

        $pnlData = [];
        $totalSystemRev = 0.0;

        foreach ($branches as $b) {
            // Ép kiểu (float) ngay lúc query xong
            $rev = (float)($this->db->sum('invoices', 'total', [
                'branch_id' => $b['id'], 'organization_id' => $this->orgId, 
                'invoice_date[<>]' => [$from . ' 00:00:00', $to . ' 23:59:59']
            ]) ?? 0);

            $exp = (float)($this->db->sum('expenses', 'amount', [
                'branch_id' => $b['id'], 'organization_id' => $this->orgId, 'deleted' => 0,
                'expense_date[<>]' => [$from, $to]
            ]) ?? 0);

            $profit = $rev - $exp;
            $totalSystemRev += $rev;

            $pnlData[$b['id']] = [
                'id' => $b['id'],
                'name' => $b['name'],
                'revenue' => $rev,
                'expenses' => $exp,
                'profit' => $profit,
                'margin' => $rev > 0 ? round(($profit / $rev) * 100, 2) : 0
            ];
        }

        foreach ($pnlData as $key => $p) {
            $pnlData[$key]['contribution'] = $totalSystemRev > 0 ? round(($p['revenue'] / $totalSystemRev) * 100, 1) : 0;
        }

        $branchA = request('branch_a');
        $branchB = request('branch_b');
        $comparison = null;

        if ($branchA && $branchB && isset($pnlData[$branchA]) && isset($pnlData[$branchB])) {
            $comparison = [ 'A' => $pnlData[$branchA], 'B' => $pnlData[$branchB] ];
        }

        usort($pnlData, fn($a, $b) => $b['profit'] <=> $a['profit']);

        return view('reports/location-pnl', [
            'pnlData' => $pnlData, 'branches' => $branches, 'comparison' => $comparison,
            'filter' => ['from_date' => $from, 'to_date' => $to, 'branch_a' => $branchA, 'branch_b' => $branchB]
        ]);
    }

    // ====================================================
    // 3. ĐIỂM HÒA VỐN (BREAK-EVEN ANALYSIS)
    // ====================================================
    public function BreakEvenReport() {
        $this->requirePermission('reports.finance');
        $month = request('month', date('Y-m'));
        $bSqlInv = $this->getRawBranchSql('i.');
        $bSqlExp = $this->getRawBranchSql('e.');
        $orgId = $this->orgId;

        $revenue = $this->db->query("SELECT SUM(total) as val FROM invoices i WHERE organization_id = '{$orgId}' AND DATE_FORMAT(invoice_date, '%Y-%m') = '{$month}' {$bSqlInv}")->fetch()['val'] ?? 0;
        
        // Tách chi phí cố định (Fixed) và biến đổi (Variable)
        $expensesData = $this->db->query("
            SELECT c.name as cat_name, SUM(e.amount) as val 
            FROM expenses e 
            LEFT JOIN expense_categories c ON e.category = c.id
            WHERE e.organization_id = '{$orgId}' AND e.deleted = 0 AND DATE_FORMAT(e.expense_date, '%Y-%m') = '{$month}' {$bSqlExp}
            GROUP BY e.category
        ")->fetchAll();

        $fixedCosts = 0; $variableCosts = 0;
        foreach ($expensesData as $e) {
            $name = mb_strtolower($e['cat_name'] ?? '', 'UTF-8');
            // Cố định: Lương, Thuê mặt bằng, Điện nước...
            if (strpos($name, 'lương') !== false || strpos($name, 'thuê') !== false || strpos($name, 'mặt bằng') !== false) {
                $fixedCosts += $e['val'];
            } else {
                $variableCosts += $e['val'];
            }
        }
        
        if ($fixedCosts == 0 && $variableCosts == 0) {
            $totalExp = $this->db->query("SELECT SUM(amount) as val FROM expenses e WHERE organization_id = '{$orgId}' AND deleted = 0 AND DATE_FORMAT(expense_date, '%Y-%m') = '{$month}' {$bSqlExp}")->fetch()['val'] ?? 0;
            $fixedCosts = $totalExp * 0.6; // Mặc định 60% fixed nếu không phân loại
            $variableCosts = $totalExp * 0.4;
        }

        $totalExp = $fixedCosts + $variableCosts;
        $profit = $revenue - $totalExp;
        
        $marginRatio = $revenue > 0 ? (($revenue - $variableCosts) / $revenue) : 0;
        $breakEvenPoint = $marginRatio > 0 ? ($fixedCosts / $marginRatio) : 0;
        $safetyMargin = $revenue > 0 ? (($revenue - $breakEvenPoint) / $revenue) * 100 : 0;

        return view('reports/break-even', compact('month', 'revenue', 'fixedCosts', 'variableCosts', 'totalExp', 'profit', 'breakEvenPoint', 'marginRatio', 'safetyMargin'));
    }

    // ====================================================
    // 4. DỰ BÁO TÀI CHÍNH (FORECASTING)
    // ====================================================
    public function ForecastReport() {
        $this->requirePermission('reports.finance');
        $bSqlInv = $this->getRawBranchSql('i.');
        $orgId = $this->orgId;
        
        // Lấy lịch sử 6 tháng gần nhất
        $history = $this->db->query("
            SELECT DATE_FORMAT(invoice_date, '%Y-%m') as month, SUM(total) as val 
            FROM invoices i 
            WHERE organization_id = '{$orgId}' {$bSqlInv}
            GROUP BY month ORDER BY month DESC LIMIT 6
        ")->fetchAll();
        
        $history = array_reverse($history);
        $forecast = [];
        $sum = 0; $count = count($history);
        
        foreach ($history as $h) { $sum += $h['val']; }
        $avg = $count > 0 ? $sum / $count : 0;
        $growthRate = 1.05; // Giả định tăng trưởng trung bình 5%/tháng
        
        if ($count > 0) {
            $lastMonthStr = $history[$count-1]['month'];
            for ($i = 1; $i <= 3; $i++) {
                $nextMonth = date('Y-m', strtotime($lastMonthStr . "-01 +$i month"));
                $projectedVal = $avg * pow($growthRate, $i);
                $forecast[] = [ 'month' => $nextMonth, 'val' => $projectedVal ];
            }
        }
        
        return view('reports/forecast', compact('history', 'forecast'));
    }

    // ====================================================
    // 5. HIỆU QUẢ ĐẦU TƯ (ROI MARKETING)
    // ====================================================
    public function RoiReport() {
        $this->requirePermission('reports.finance');
        $year = request('year', date('Y'));
        $bSqlInv = $this->getRawBranchSql('i.');
        $bSqlExp = $this->getRawBranchSql('e.');
        $orgId = $this->orgId;

        $stmtMkt = $this->db->query("
            SELECT DATE_FORMAT(e.expense_date, '%m') AS month, SUM(e.amount) AS val 
            FROM expenses e 
            LEFT JOIN expense_categories c ON e.category = c.id
            WHERE e.organization_id = '{$orgId}' AND e.deleted = 0 AND YEAR(e.expense_date) = '{$year}' 
            AND (LOWER(c.name) LIKE '%quảng cáo%' OR LOWER(c.name) LIKE '%marketing%' OR LOWER(c.name) LIKE '%ads%')
            {$bSqlExp} GROUP BY month
        ");
        $marketingData = $stmtMkt ? $stmtMkt->fetchAll(\PDO::FETCH_ASSOC) : [];

        $stmtRev = $this->db->query("
            SELECT DATE_FORMAT(invoice_date, '%m') AS month, SUM(total) AS val 
            FROM invoices i 
            WHERE organization_id = '{$orgId}' AND YEAR(invoice_date) = '{$year}' {$bSqlInv} GROUP BY month
        ");
        $revData = $stmtRev ? $stmtRev->fetchAll(\PDO::FETCH_ASSOC) : [];

        $monthlyData = [];
        $totalRev = 0; $totalMkt = 0;

        for ($i = 1; $i <= 12; $i++) {
            $m = str_pad($i, 2, '0', STR_PAD_LEFT);
            $rev = 0; $mkt = 0;
            
            foreach ($revData as $r) {
                if (isset($r['month']) && $r['month'] === $m) $rev = $r['val'];
            }
            foreach ($marketingData as $e) {
                if (isset($e['month']) && $e['month'] === $m) $mkt = $e['val'];
            }

            $totalRev += $rev; $totalMkt += $mkt;
            $roi = $mkt > 0 ? (($rev - $mkt) / $mkt) * 100 : 0;

            $monthlyData[] = [
                'month' => "Tháng $m", 'revenue' => (float)$rev, 'marketing' => (float)$mkt, 'roi' => round($roi, 2)
            ];
        }

        $overallRoi = $totalMkt > 0 ? (($totalRev - $totalMkt) / $totalMkt) * 100 : 0;
        return view('reports/roi', compact('year', 'monthlyData', 'totalRev', 'totalMkt', 'overallRoi'));
    }
}