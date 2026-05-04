<?php
namespace App\Controllers;

class ExpenseBusinessController extends BaseController
{
    public function Index()
    {
        // 1. Lấy tham số lọc từ Request
        $fromDate = request('from_date');
        $toDate = request('to_date');
        $filterBranchId = request('filter_branch_id');

        // 2. Xây dựng điều kiện lọc (Where clause)
        $where = [
            'expenses.deleted' => 0
        ];

        // Lọc theo ngày
        if ($fromDate) $where['expenses.expense_date[>=]'] = $fromDate;
        if ($toDate) $where['expenses.expense_date[<=]'] = $toDate;

        // Lọc theo chi nhánh (Ưu tiên bộ lọc tại trang, sau đó mới đến bộ lọc Sidebar)
        $finalBranchId = $this->branchId;
        if ($this->branchId === 'all' && !empty($filterBranchId)) {
            $finalBranchId = $filterBranchId;
        }

        if ($finalBranchId !== 'all') {
            $where['expenses.branch_id'] = $finalBranchId;
        }

        $where['ORDER'] = ['expenses.expense_date' => 'DESC', 'expenses.id' => 'DESC'];

        // 3. Thực thi Query lấy danh sách
        $expenses = $this->db->select('expenses', [
            '[>]branches' => ['branch_id' => 'id'],
            '[>]expense_categories' => ['category' => 'id'] 
        ], [
            'expenses.id', 'expenses.title', 'expenses.amount', 'expenses.expense_date',
            'branches.name(branch_name)', 'expense_categories.name(category_name)'
        ], $where);

        // 4. Thống kê (Cũng phải áp dụng bộ lọc vào đây)
        $summaryWhere = [
            "expenses.organization_id" => $this->orgId, // Sửa thành expenses.organization_id cho rõ ràng khi JOIN
            "expenses.deleted" => 0
        ];
        if ($fromDate) $summaryWhere['expenses.expense_date[>=]'] = $fromDate;
        if ($toDate) $summaryWhere['expenses.expense_date[<=]'] = $toDate;
        if ($finalBranchId !== 'all') $summaryWhere['expenses.branch_id'] = $finalBranchId;

        // Bổ sung JOIN sang bảng danh mục để lấy thẳng tên
        $stats = $this->db->select("expenses", [
            "[>]expense_categories" => ["category" => "id"]
        ], [
            "expenses.category", 
            "expense_categories.name(cat_name)", 
            "expenses.amount"
        ], $summaryWhere);

        $totalSum = 0;
        $catSums = [];
        $catNamesMap = [];

        // Từ điển dịch thuật cho các dữ liệu cũ ngày xưa
        $legacyNames = [
            'salary' => 'Lương / Thưởng',
            'rent' => 'Thuê mặt bằng',
            'ads' => 'Quảng cáo',
            'other' => 'Khác'
        ];

        foreach ($stats as $s) {
            $totalSum += $s['amount'];
            $catId = $s['category'];
            
            // Cộng dồn tiền theo ID/Mã danh mục
            $catSums[$catId] = ($catSums[$catId] ?? 0) + $s['amount'];
            
            // Xử lý lấy tên hiển thị
            if (!empty($s['cat_name'])) {
                $catNamesMap[$catId] = $s['cat_name']; // Dữ liệu mới (Có UUID chuẩn)
            } elseif (isset($legacyNames[$catId])) {
                $catNamesMap[$catId] = $legacyNames[$catId]; // Dữ liệu cũ (salary, rent...)
            } else {
                $catNamesMap[$catId] = 'Khác'; // Không khớp gì cả
            }
        }
        
        // Lấy tên danh mục cho Top 3
        arsort($catSums);
        $topCategories = [];
        $count = 0;
        foreach ($catSums as $id => $total) {
            if ($count >= 3) break;
            $topCategories[] = [
                'name' => $catNamesMap[$id] ?? 'Khác',
                'total' => $total
            ];
            $count++;
        }

        // 5. Dữ liệu bổ trợ cho View
        $branches = $this->db->select('branches', ['id', 'name'], ['organization_id' => $this->orgId, 'deleted' => 0]);

        return view('business/expense', [
            'expenses' => $expenses,
            'totalSum' => $totalSum,
            'topCategories' => $topCategories,
            'branches' => $branches,
            'currentBranchId' => $this->branchId,
            'filter' => [ // Gửi lại giá trị đang lọc để hiển thị lên input
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'branch_id' => $filterBranchId
            ]
        ]);
    }

    // HÀM MỚI: Gọi Modal Thêm khoản chi
    public function Create()
    {
        $branches = $this->db->select('branches', ['id', 'name'], ['organization_id' => $this->orgId, 'deleted' => 0]);
        $expenseCategories = $this->db->select('expense_categories', ['id', 'name'], [
            'organization_id' => $this->orgId,
            'deleted' => 0,
            'is_active' => 1,
            'ORDER' => ['name' => 'ASC']
        ]);

        return view('business/expense-post', [
            'expense' => null, // null nghĩa là form Thêm mới
            'branches' => $branches,
            'expenseCategories' => $expenseCategories,
            'currentBranchId' => $this->branchId
        ]);
    }

    public function Store()
    {
        $validator = app()->validate(['title' => 'required', 'amount' => 'required', 'expense_date' => 'required']);
        if ($validator->fails()) return response()->json(['status' => 'error', 'alert' => $validator->first()], 400);

        $branchToSave = ($this->branchId === 'all') ? request('branch_id') : $this->branchId;

        $this->db->insert('expenses', [
            'organization_id' => $this->orgId,
            'branch_id'       => !empty($branchToSave) ? $branchToSave : null,
            'title'           => app()->xss->clean(request('title')),
            'category'        => request('category'), 
            'amount'          => str_replace(',', '', request('amount')),
            'expense_date'    => request('expense_date'),
            'note'            => app()->xss->clean(request('note') ?? ''),
            'deleted'         => 0,
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s')
        ]);

        return response()->json([
            'status' => 'success', 
            'alert' => 'Thêm khoản chi thành công',
            'redirect' => '/business/expenses'
        ]);
    }

    public function Edit($id)
    {
        $expense = $this->db->get('expenses', '*', ['id' => $id, 'organization_id' => $this->orgId]);
        if (!$expense) return "Không tìm thấy dữ liệu!";

        $branches = $this->db->select('branches', ['id', 'name'], ['organization_id' => $this->orgId, 'deleted' => 0]);
        $expenseCategories = $this->db->select('expense_categories', ['id', 'name'], [
            'organization_id' => $this->orgId,
            'deleted' => 0,
            'is_active' => 1,
            'ORDER' => ['name' => 'ASC']
        ]);

        return view('business/expense-post', [
            'expense' => $expense,
            'branches' => $branches,
            'expenseCategories' => $expenseCategories,
            'currentBranchId' => $this->branchId
        ]);
    }

    public function Update($id)
    {
        $validator = app()->validate(['title' => 'required', 'amount' => 'required']);
        if ($validator->fails()) return response()->json(['status' => 'error', 'alert' => 'Thiếu thông tin'], 400);

        $updateData = [
            'title'        => app()->xss->clean(request('title')),
            'category'     => request('category'), 
            'amount'       => str_replace(',', '', request('amount')),
            'expense_date' => request('expense_date'),
            'note'         => app()->xss->clean(request('note') ?? ''),
            'updated_at'   => date('Y-m-d H:i:s')
        ];

        if ($this->branchId === 'all' && isset($_POST['branch_id'])) {
            $selected = request('branch_id');
            $updateData['branch_id'] = empty($selected) ? null : $selected;
        }

        $this->db->update('expenses', $updateData, ['id' => $id, 'organization_id' => $this->orgId]);

        return response()->json([
            'status' => 'success', 
            'alert' => 'Cập nhật thành công',
            'redirect' => '/business/expenses'
        ]);
    }

    public function Delete($id)
    {
        $this->db->update('expenses', ['deleted' => 1], ['id' => $id, 'organization_id' => $this->orgId]);
        return response()->json(['status' => 'success', 'alert' => 'Đã xóa khoản chi', 'redirect' => '/business/expenses']);
    }

    public function BulkDelete()
    {
        $idsRaw = request('ids'); 
        $ids = [];
        if (is_string($idsRaw) && !empty($idsRaw)) {
            $ids = array_filter(explode(',', $idsRaw));
        }

        if (empty($ids)) return response()->json(['status' => 'error', 'alert' => 'Vui lòng chọn mục để xóa!']);

        $this->db->update('expenses', ['deleted' => 1], ['id' => $ids, 'organization_id' => $this->orgId]);

        return response()->json([
            'status' => 'success',
            'alert' => 'Đã xóa ' . count($ids) . ' khoản chi!',
            'redirect' => '/business/expenses'
        ]);
    }
}