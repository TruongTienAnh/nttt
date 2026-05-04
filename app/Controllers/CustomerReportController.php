<?php
namespace App\Controllers;

use Medoo\Medoo;

class CustomerReportController extends BaseController {

    // --- HÀM DÙNG CHUNG: Lấy KPI ---
    private function getBaseStats($whereInvoices) {
        $whereInvoices['customer_id[!]'] = null; // Bỏ qua khách lẻ không tên

        $stats = $this->db->get("invoices", [
            "total_customers" => Medoo::raw('COUNT(DISTINCT <customer_id>)'),
            "gross_revenue"   => Medoo::raw('SUM(<total>)'),
            "total_orders"    => Medoo::raw('COUNT(<id>)')
        ], $whereInvoices);

        $stats['total_customers'] = $stats['total_customers'] ?? 0;
        $stats['gross_revenue'] = $stats['gross_revenue'] ?? 0;
        $stats['total_orders'] = $stats['total_orders'] ?? 0;
        $stats['aov'] = $stats['total_orders'] > 0 ? ($stats['gross_revenue'] / $stats['total_orders']) : 0;

        return $stats;
    }

    // ====================================================
    // 1. BÁO CÁO RFM PHÂN KHÚC
    // ====================================================
    public function RfmReport() {
        $this->requirePermission('reports.customers');
        $where = $this->getSecureBranchFilter('invoices');
        $where['invoices.customer_id[!]'] = null;

        $stats = $this->getBaseStats($this->getSecureBranchFilter());

        // Lấy dữ liệu RFM bằng Medoo chuẩn hóa
        $rfmRaw = $this->db->select('invoices', [
            '[>]customers' => ['customer_id' => 'id']
        ], [
            'customers.full_name',
            'customers.phone',
            'r_days'  => Medoo::raw('DATEDIFF(NOW(), MAX(<invoices.invoice_date>))'),
            'f_count' => Medoo::raw('COUNT(<invoices.id>)'),
            'm_total' => Medoo::raw('SUM(<invoices.total>)')
        ], array_merge($where, [
            'GROUP' => 'invoices.customer_id',
            'ORDER' => ['m_total' => 'DESC']
        ]));

        $rfmList = [];
        foreach ($rfmRaw as $r) {
            $r['segment'] = 'TIỀM NĂNG';
            if ($r['m_total'] >= 5000000 && $r['f_count'] >= 5) $r['segment'] = 'VIP';
            elseif ($r['r_days'] > 60) $r['segment'] = 'NGỦ ĐÔNG';
            elseif ($r['f_count'] == 1 && $r['r_days'] <= 15) $r['segment'] = 'MỚI';
            $rfmList[] = $r;
        }

        // BẢNG SO SÁNH CHI NHÁNH
        $branchStats = [];
        if ($this->branchId === 'all') {
            $branchStats = $this->db->select('invoices', [
                '[>]branches' => ['branch_id' => 'id']
            ], [
                'branches.name',
                'total_customers' => Medoo::raw('COUNT(DISTINCT <invoices.customer_id>)'),
                'revenue' => Medoo::raw('SUM(<invoices.total>)')
            ], array_merge($where, [
                'GROUP' => 'invoices.branch_id',
                'ORDER' => ['revenue' => 'DESC']
            ]));
        }

        return view('reports/rfm', compact('stats', 'rfmList', 'branchStats'));
    }

    // ====================================================
    // 2. PHÂN TÍCH VÒNG ĐỜI & CHURN RATE
    // ====================================================
    public function ChurnReport() {
        $this->requirePermission('reports.customers');
        $where = $this->getSecureBranchFilter('invoices');
        $where['invoices.customer_id[!]'] = null;

        $stats = $this->getBaseStats($this->getSecureBranchFilter());

        // Lấy Data Phân tích sức khỏe
        $healthRaw = $this->db->select('invoices', [
            'r_days' => Medoo::raw('DATEDIFF(NOW(), MAX(<invoice_date>))')
        ], array_merge($where, ['GROUP' => 'customer_id']));

        $churnData = ['Hoạt động' => 0, 'Rủi ro' => 0, 'Ngủ đông' => 0];
        foreach ($healthRaw as $row) {
            if ($row['r_days'] > 60) $churnData['Ngủ đông']++;
            elseif ($row['r_days'] > 30) $churnData['Rủi ro']++;
            else $churnData['Hoạt động']++;
        }

        // Lấy Khách hàng rời bỏ (>30 ngày)
        $whereLost = $where;
        $whereLost['HAVING'] = Medoo::raw('DATEDIFF(NOW(), MAX(<invoices.invoice_date>)) > 30');
        
        $lostCustomers = $this->db->select('invoices', [
            '[>]customers' => ['customer_id' => 'id'],
            '[>]branches' => ['branch_id' => 'id']
        ], [
            'customers.full_name',
            'customers.phone',
            'branches.name(branch_name)',
            'r_days'  => Medoo::raw('DATEDIFF(NOW(), MAX(<invoices.invoice_date>))'),
            'm_total' => Medoo::raw('SUM(<invoices.total>)')
        ], array_merge($whereLost, [
            'GROUP' => 'invoices.customer_id',
            'ORDER' => ['r_days' => 'DESC']
        ]));

        return view('reports/churn', compact('stats', 'churnData', 'lostCustomers'));
    }

    // ====================================================
    // 3. CROSS-SELL HỆ SINH THÁI
    // ====================================================
    public function CrossSellReport() {
        $this->requirePermission('reports.customers');
        $where = $this->getSecureBranchFilter('i'); // Prefix i cho bảng invoices

        $stats = $this->getBaseStats($this->getSecureBranchFilter());

        // ==========================================
        // FIX LỖI: Xử lý an toàn biến $branchSql
        // ==========================================
        $branchSql = "";
        
        // Chỉ thêm điều kiện lọc chi nhánh nếu $where có giới hạn branch_id
        if (isset($where['i.branch_id'])) {
            $bIds = $where['i.branch_id'];
            if (is_array($bIds)) {
                if (empty($bIds)) {
                    $branchSql = " AND 1=0 "; // Bị chặn quyền
                } else {
                    $inClause = implode(',', array_map('intval', $bIds));
                    $branchSql = " AND i.branch_id IN ($inClause) ";
                }
            } elseif ($bIds == -1) {
                $branchSql = " AND 1=0 "; // Bị chặn quyền
            } else {
                $branchSql = " AND i.branch_id = " . intval($bIds) . " ";
            }
        }
        // Nếu không có 'i.branch_id' => Tức là Admin đang xem Tất cả => $branchSql rỗng (quét toàn hệ thống)

        // Thực thi Query phân tích giỏ hàng (Market Basket Analysis)
        // Lưu ý: Nếu cột id dịch vụ của bạn không phải là 'service_id' (ví dụ: product_id), hãy sửa lại nhé!
        $crossSell = $this->db->query("
            SELECT a.name as p1, b.name as p2, COUNT(*) as freq
            FROM invoice_items a
            JOIN invoice_items b ON a.invoice_id = b.invoice_id AND a.service_id < b.service_id
            JOIN invoices i ON a.invoice_id = i.id
            WHERE i.organization_id = '{$this->orgId}' $branchSql
            GROUP BY a.name, b.name 
            ORDER BY freq DESC LIMIT 20
        ")->fetchAll();

        return view('reports/cross-sell', compact('stats', 'crossSell'));
    }
}