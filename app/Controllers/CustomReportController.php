<?php
namespace App\Controllers;

class CustomReportController extends BaseController {

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
        
        // Nhận tham số
        $module = request('module', 'invoices'); 
        $metric = request('metric', 'sum'); 
        $groupBy = request('group_by', 'none'); 
        $fromDate = request('from_date', date('Y-m-01'));
        $toDate = request('to_date', date('Y-m-d'));
        
        $branchIds = request('branch_ids', []);
        $filters = request('filters', []);
        $columns = request('columns', []);

        $validModules = ['invoices', 'expenses', 'customers'];
        if (!in_array($module, $validModules)) $module = 'invoices';

        $branches = $this->db->select('branches', ['id', 'name'], ['organization_id' => $this->orgId, 'deleted' => 0]);

        $orgId = $this->orgId;
        
        // 1. Build WHERE (Cả 3 bảng giờ đều có deleted và organization_id)
        $whereSql = " t.organization_id = '{$orgId}' AND t.deleted = 0 ";

        $dateCol = 'created_at';
        if ($module == 'invoices') $dateCol = 'invoice_date';
        if ($module == 'expenses') $dateCol = 'expense_date';
        
        $whereSql .= " AND t.{$dateCol} BETWEEN '{$fromDate} 00:00:00' AND '{$toDate} 23:59:59' ";

        // Lọc Chi nhánh (Giờ cả 3 bảng đều có branch_id)
        $bSql = $this->getRawBranchSql('t.');
        if (!empty($branchIds) && is_array($branchIds)) {
            $bIdsStr = implode(',', array_map(function($id) { return "'".addslashes(trim($id))."'"; }, $branchIds));
            $whereSql .= " {$bSql} AND t.branch_id IN ({$bIdsStr}) ";
        } else {
            $whereSql .= " {$bSql} ";
        }

        // Lọc Tự Chọn (Khớp đúng tên cột trong DB)
        foreach ($filters as $key => $val) {
            if ($val !== '' && $val !== null) {
                $safeVal = addslashes(trim($val));
                if (in_array($key, ['full_name', 'phone', 'email', 'invoice_no', 'title', 'note'])) {
                    $whereSql .= " AND t.{$key} LIKE '%{$safeVal}%' ";
                } else {
                    $whereSql .= " AND t.{$key} = '{$safeVal}' ";
                }
            }
        }

        // 2. Build SELECT & GROUP
        $selectSql = "";
        $joinSql = " LEFT JOIN branches b ON t.branch_id = b.id ";
        $groupSql = "";
        $orderSql = "";
        
        $isDetail = ($groupBy == 'none'); 

        if ($isDetail) {
            if (empty($columns)) {
                $selectSql = " t.* "; 
            } else {
                $safeCols = array_map(function($c) { return "t." . preg_replace('/[^a-zA-Z0-9_]/', '', $c); }, $columns);
                $selectSql = implode(", ", $safeCols);
            }
            $selectSql .= ", COALESCE(b.name, 'Hệ thống') as branch_name ";
            $orderSql = " t.{$dateCol} DESC LIMIT 500 "; 
        } else {
            if ($groupBy == 'branch') {
                $selectSql = " COALESCE(b.name, 'Hệ thống') as label ";
                $groupSql = " t.branch_id ";
            } elseif ($groupBy == 'month') {
                $selectSql = " DATE_FORMAT(t.{$dateCol}, '%Y-%m') as label ";
                $groupSql = " label ";
            } else {
                $selectSql = " DATE(t.{$dateCol}) as label ";
                $groupSql = " label ";
            }

            $valCol = ($module == 'invoices') ? 'total' : (($module == 'expenses') ? 'amount' : 'id');
            if ($metric == 'sum' && in_array($module, ['invoices', 'expenses'])) {
                $selectSql .= ", SUM(t.{$valCol}) as value ";
            } else {
                $selectSql .= ", COUNT(t.id) as value ";
            }
            $orderSql = " value DESC ";
        }

        // 3. Thực thi Query
        $results = [];
        if (isset($_GET['module'])) {
            $sql = "SELECT {$selectSql} FROM {$module} t {$joinSql} WHERE {$whereSql} ";
            if (!$isDetail) $sql .= " GROUP BY {$groupSql} ";
            $sql .= " ORDER BY {$orderSql} ";
            
            $stmt = $this->db->query($sql);
            $results = $stmt ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : [];
        }

        return view('reports/custom', compact(
            'results', 'module', 'metric', 'groupBy', 'fromDate', 'toDate', 
            'branches', 'branchIds', 'filters', 'columns', 'isDetail'
        ));
    }
}