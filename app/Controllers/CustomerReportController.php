<?php
namespace App\Controllers;

class CustomerReportController extends BaseController {

    // --- HÀM DÙNG CHUNG: Lấy Điều kiện SQL và KPI ---
    private function getBaseData() {
        if (!$this->orgId) { header('Location: /login'); exit; }

        $params = ['org' => $this->orgId];
        $branchSql = "";
        $branchSqlAlias = "";
        
        if ($this->branchId !== 'all') {
            $branchSql = " AND branch_id = :branch ";
            $branchSqlAlias = " AND i.branch_id = :branch ";
            $params['branch'] = $this->branchId;
        }

        // Lấy KPI Tổng quan (Dùng chung cho cả 3 màn hình)
        $stats = $this->db->query("
            SELECT COUNT(DISTINCT customer_id) as total_customers, 
                   SUM(total) as gross_revenue,
                   AVG(total) as aov, 
                   COUNT(id) as total_orders
            FROM invoices 
            WHERE organization_id = :org $branchSql AND customer_id IS NOT NULL
        ", $params)->fetch();

        if (!$stats) {
            $stats = ['total_customers' => 0, 'gross_revenue' => 0, 'aov' => 0, 'total_orders' => 0];
        }

        return [$params, $branchSql, $branchSqlAlias, $stats];
    }

    // ====================================================
    // 1. BÁO CÁO RFM PHÂN KHÚC
    // ====================================================
    public function RfmReport() {
        list($params, $branchSql, $branchSqlAlias, $stats) = $this->getBaseData();

        $rfmList = $this->db->query("
            SELECT c.full_name, c.phone, t.r_days, t.f_count, t.m_total,
                CASE 
                    WHEN t.m_total >= 5000000 AND t.f_count >= 5 THEN 'VIP'
                    WHEN t.r_days > 60 THEN 'NGỦ ĐÔNG'
                    WHEN t.f_count = 1 AND t.r_days <= 15 THEN 'MỚI'
                    ELSE 'TIỀM NĂNG'
                END as segment
            FROM (
                SELECT customer_id, DATEDIFF(NOW(), MAX(invoice_date)) as r_days, 
                       COUNT(id) as f_count, SUM(total) as m_total
                FROM invoices 
                WHERE organization_id = :org $branchSql AND customer_id IS NOT NULL 
                GROUP BY customer_id
            ) as t
            JOIN customers c ON t.customer_id = c.id
            ORDER BY t.m_total DESC
        ", $params)->fetchAll();

        return view('reports/rfm', compact('stats', 'rfmList'));
    }

    // ====================================================
    // 2. PHÂN TÍCH VÒNG ĐỜI & CHURN RATE
    // ====================================================
    public function ChurnReport() {
        list($params, $branchSql, $branchSqlAlias, $stats) = $this->getBaseData();

        $churnData = $this->db->query("
            SELECT churn_group, COUNT(*) as count FROM (
                SELECT CASE 
                    WHEN DATEDIFF(NOW(), MAX(invoice_date)) > 60 THEN 'Ngủ đông'
                    WHEN DATEDIFF(NOW(), MAX(invoice_date)) > 30 THEN 'Rủi ro'
                    ELSE 'Hoạt động'
                END as churn_group
                FROM invoices 
                WHERE organization_id = :org $branchSql AND customer_id IS NOT NULL 
                GROUP BY customer_id
            ) as t GROUP BY churn_group
        ", $params)->fetchAll();

        // Lấy danh sách khách hàng có nguy cơ rời bỏ (r_days > 30)
        $lostCustomers = $this->db->query("
            SELECT c.full_name, c.phone, t.r_days, t.m_total
            FROM (
                SELECT customer_id, DATEDIFF(NOW(), MAX(invoice_date)) as r_days, SUM(total) as m_total
                FROM invoices 
                WHERE organization_id = :org $branchSql AND customer_id IS NOT NULL 
                GROUP BY customer_id HAVING r_days > 30
            ) as t
            JOIN customers c ON t.customer_id = c.id
            ORDER BY t.r_days DESC
        ", $params)->fetchAll();

        return view('reports/churn', compact('stats', 'churnData', 'lostCustomers'));
    }

    // ====================================================
    // 3. CROSS-SELL HỆ SINH THÁI
    // ====================================================
    public function CrossSellReport() {
        list($params, $branchSql, $branchSqlAlias, $stats) = $this->getBaseData();

        $crossSell = $this->db->query("
            SELECT a.name as p1, b.name as p2, COUNT(*) as freq
            FROM invoice_items a
            JOIN invoice_items b ON a.invoice_id = b.invoice_id AND a.service_id < b.service_id
            JOIN invoices i ON a.invoice_id = i.id
            WHERE i.organization_id = :org $branchSqlAlias
            GROUP BY a.name, b.name 
            ORDER BY freq DESC LIMIT 20
        ", $params)->fetchAll();

        return view('reports/cross-sell', compact('stats', 'crossSell'));
    }
}