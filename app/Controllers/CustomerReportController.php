<?php
namespace App\Controllers;

class CustomerReportController {
    public function index() {
        $orgId = $_SESSION['organization_id'] ?? "e027cf6e-538d-4257-9691-068b36e280f8";
        if (!$orgId) { header('Location: /login'); exit; }
        $db = app()->db;

        // --- 0. KPI TỔNG QUAN ---
        // Thêm điều kiện customer_id IS NOT NULL để chỉ tính khách có định danh
        $stats = $db->query("
            SELECT COUNT(DISTINCT customer_id) as total_customers, SUM(total) as gross_revenue,
                   AVG(total) as aov, COUNT(id) as total_orders
            FROM invoices 
            WHERE organization_id = :org AND customer_id IS NOT NULL
        ", ['org' => $orgId])->fetch();

        // --- 1. CHURN RATE (Sức khỏe khách hàng) ---
        $churnData = $db->query("
            SELECT churn_group, COUNT(*) as count FROM (
                SELECT CASE 
                    WHEN DATEDIFF(NOW(), MAX(invoice_date)) > 60 THEN 'Ngủ đông'
                    WHEN DATEDIFF(NOW(), MAX(invoice_date)) > 30 THEN 'Rủi ro'
                    ELSE 'Hoạt động'
                END as churn_group
                FROM invoices 
                WHERE organization_id = :org AND customer_id IS NOT NULL 
                GROUP BY customer_id
            ) as t GROUP BY churn_group
        ", ['org' => $orgId])->fetchAll();

        // --- 2. RFM (Phân khúc VIP, MỚI, NGỦ ĐÔNG) ---
        $rfmList = $db->query("
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
                WHERE organization_id = :org AND customer_id IS NOT NULL 
                GROUP BY customer_id
            ) as t
            JOIN customers c ON t.customer_id = c.id
            ORDER BY t.m_total DESC
        ", ['org' => $orgId])->fetchAll();

        // --- 3. CROSS-SELL (Gợi ý bán kèm) ---
        // Sửa GROUP BY p1, p2 thành a.name, b.name để chuẩn SQL Strict Mode
        $crossSell = $db->query("
            SELECT a.name as p1, b.name as p2, COUNT(*) as freq
            FROM invoice_items a
            JOIN invoice_items b ON a.invoice_id = b.invoice_id AND a.service_id < b.service_id
            JOIN invoices i ON a.invoice_id = i.id
            WHERE i.organization_id = :org
            GROUP BY a.name, b.name 
            ORDER BY freq DESC LIMIT 20
        ", ['org' => $orgId])->fetchAll();

        return view('reports/customers', compact('stats', 'churnData', 'rfmList', 'crossSell'));
    }
}