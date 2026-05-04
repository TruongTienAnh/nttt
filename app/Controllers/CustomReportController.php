<?php
namespace App\Controllers;

class CustomReportController extends BaseController {

    /**
     * Trích xuất điều kiện WHERE chi nhánh thành chuỗi SQL an toàn 
     */
    private function getRawBranchSql($prefix = '') {
        $where = $this->getSecureBranchFilter($prefix ? trim($prefix, '.') : '');
        $col = $prefix . 'branch_id';
        
        if (isset($where[$col])) {
            $bIds = $where[$col];
            if (is_array($bIds)) {
                if (empty($bIds)) return " AND 1=0 "; 
                return " AND {$col} IN (" . implode(',', array_map('intval', $bIds)) . ") ";
            } elseif ($bIds == -1) {
                return " AND 1=0 "; 
            } else {
                return " AND {$col} = " . intval($bIds) . " ";
            }
        }
        return ""; 
    }

    public function Index() {
        $this->requirePermission('reports.custom');
        
        // 1. Nhận tham số từ bộ lọc
        $module = request('module', 'invoices'); 
        $metric = request('metric', 'sum'); 
        $groupBy = request('group_by', 'branch'); 
        $fromDate = request('from_date', date('Y-m-01'));
        $toDate = request('to_date', date('Y-m-d'));

        // 2. Chặn Injection & Hack Parameter
        $validModules = ['invoices', 'expenses'];
        if (!in_array($module, $validModules)) $module = 'invoices';

        $bSql = $this->getRawBranchSql('t.');
        $orgId = $this->orgId;

        $dateCol = $module == 'invoices' ? 'invoice_date' : 'expense_date';
        $valCol = $module == 'invoices' ? 'total' : 'amount';

        // 3. Xây dựng điều kiện WHERE (Có bảo mật Chi nhánh)
        $whereSql = " t.organization_id = '{$orgId}' {$bSql} AND t.{$dateCol} BETWEEN '{$fromDate} 00:00:00' AND '{$toDate} 23:59:59' ";
        if ($module == 'expenses') {
            $whereSql .= " AND t.deleted = 0 ";
        }

        // 4. Xây dựng cấu trúc SELECT, JOIN và GROUP BY
        $selectSql = "";
        $joinSql = "";
        $groupSql = "";
        $orderSql = "";

        if ($groupBy == 'branch') {
            $joinSql = " LEFT JOIN branches b ON t.branch_id = b.id ";
            $selectSql = " b.name as label ";
            $groupSql = " t.branch_id ";
            $orderSql = " value DESC ";
        } elseif ($groupBy == 'month') {
            $selectSql = " DATE_FORMAT(t.{$dateCol}, '%Y-%m') as label ";
            $groupSql = " label ";
            $orderSql = " label DESC ";
        } else {
            $selectSql = " DATE(t.{$dateCol}) as label ";
            $groupSql = " label ";
            $orderSql = " label DESC ";
        }

        // Chọn hàm tính toán
        if ($metric == 'sum') {
            $selectSql .= ", SUM(t.{$valCol}) as value ";
        } else {
            $selectSql .= ", COUNT(t.id) as value ";
        }

        // 5. Thực thi Query Thô (Bỏ qua lỗi ngớ ngẩn của Medoo)
        $sql = "SELECT {$selectSql} FROM {$module} t {$joinSql} WHERE {$whereSql} GROUP BY {$groupSql} ORDER BY {$orderSql}";
        
        $stmt = $this->db->query($sql);
        $results = $stmt ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : [];

        // 6. Xử lý hiển thị
        foreach ($results as &$r) {
            if ($groupBy == 'branch' && empty($r['label'])) {
                $r['label'] = 'Chi nhánh chung';
            }
            $r['value'] = (float)($r['value'] ?? 0);
        }

        return view('reports/custom', compact('results', 'module', 'metric', 'groupBy', 'fromDate', 'toDate'));
    }
}