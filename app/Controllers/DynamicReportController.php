<?php
namespace App\Controllers;

class DynamicReportController extends BaseController
{
    public function Index()
    {
        $branches = $this->db->select('branches', ['id', 'name'], ['organization_id' => $this->orgId, 'deleted' => 0]);
        $expenseCats = $this->db->select('expense_categories', ['id', 'name'], ['organization_id' => $this->orgId, 'deleted' => 0]);

        return view('reports/dynamic-report', [
            'branches' => $branches,
            'expenseCategories' => $expenseCats
        ]);
    }

    public function Generate()
    {
        try {
            $orgId      = $_SESSION['organization_id'] ?? $this->orgId;
            $dataSource = request('data_source'); // invoices, expenses, customers
            $dimension  = request('dimension');   // month, branch
            $metric     = request('metric');      // sum, count, avg
            $branchIds  = request('filter_branch_id'); // Chuỗi ID chi nhánh chọn lọc

            // 1. Kiểm tra đầu vào an toàn (Chống SQL Injection)
            $allowedTables = ['invoices' => 'invoices', 'expenses' => 'expenses', 'customers' => 'customers'];
            if (!array_key_exists($dataSource, $allowedTables)) {
                return response()->json(['status' => 'error', 'message' => 'Nguồn dữ liệu không hợp lệ']);
            }
            $table = $allowedTables[$dataSource];

            // 2. Xây dựng điều kiện WHERE
            $whereSql = "t.organization_id = :orgId AND t.deleted = 0";
            $params = [':orgId' => $orgId];

            // Lọc đa chi nhánh
            if (!empty($branchIds) && $branchIds !== 'all') {
                $bIds = array_filter(explode(',', $branchIds));
                if (!empty($bIds)) {
                    // Tạo chuỗi 'id1','id2' an toàn
                    $inQuery = implode(',', array_map(function($val) { return "'" . addslashes(trim($val)) . "'"; }, $bIds));
                    $whereSql .= " AND t.branch_id IN ($inQuery)";
                }
            }

            // 3. Xây dựng cột tính toán (Metric)
            $valueCol = "t.id";
            if ($table === 'invoices') $valueCol = "t.total";
            if ($table === 'expenses') $valueCol = "t.amount";

            $selectMetric = "COUNT($valueCol)";
            if ($metric === 'sum') $selectMetric = "SUM($valueCol)";
            if ($metric === 'avg') $selectMetric = "AVG($valueCol)";

            // 4. Xây dựng chiều phân tích (Dimension)
            $dateCol = "t.created_at";
            if ($table === 'expenses') $dateCol = "t.expense_date"; // Bảng expenses dùng cột expense_date

            $selectLabel = "";
            $groupBy = "";
            $joinSql = "";

            if ($dimension === 'branch') {
                // Nhóm theo chi nhánh
                $selectLabel = "COALESCE(b.name, 'Chưa xác định')";
                $groupBy = "t.branch_id";
                $joinSql = "LEFT JOIN branches b ON t.branch_id = b.id";
            } else {
                // Mặc định nhóm theo tháng
                $selectLabel = "DATE_FORMAT($dateCol, '%m/%Y')";
                $groupBy = "DATE_FORMAT($dateCol, '%Y-%m')"; // Group theo năm-tháng để sort cho chuẩn
            }

            // 5. Ráp câu lệnh SQL hoàn chỉnh
            $sql = "
                SELECT 
                    $selectLabel as label, 
                    $selectMetric as value 
                FROM $table t 
                $joinSql
                WHERE $whereSql 
                GROUP BY $groupBy 
                ORDER BY $groupBy ASC
            ";

            // 6. Thực thi truy vấn
            $result = $this->db->query($sql, $params)->fetchAll(\PDO::FETCH_ASSOC);
            
            // 7. Bóc tách dữ liệu gửi về biểu đồ
            $labels = [];
            $values = [];
            foreach ($result as $row) {
                $labels[] = $row['label'];
                $values[] = (float) $row['value'];
            }

            return response()->json([
                'status' => 'success',
                'labels' => $labels,
                'values' => $values
            ]);

        } catch (\Exception $e) {
            // NẾU CÓ LỖI: Luôn trả về chuẩn JSON để JS không bị crash "Unexpected token <"
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi trích xuất số liệu: ' . $e->getMessage()
            ]);
        }
    }
}