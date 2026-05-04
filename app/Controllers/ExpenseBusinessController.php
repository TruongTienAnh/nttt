<?php
namespace App\Controllers;

class ExpenseBusinessController extends BaseController
{
    public function Index()
    {
        $this->requirePermission('expense');

        $userId = $this->user['id'] ?? 0;
        $permissionId = $this->user['permission_id'] ?? 0;

        // ==========================================================
        // 1. LẤY DANH SÁCH CHI NHÁNH ĐƯỢC PHÉP (Dành cho Dropdown)
        // ==========================================================
        if ($permissionId == 1) {
            // Super Admin: Load tất cả chi nhánh
            $branches = $this->db->select('branches', ['id', 'name'], [
                'organization_id' => $this->orgId, 
                'deleted' => 0,
                'ORDER' => ['name' => 'ASC']
            ]);
            $allowedIds = []; // Mảng rỗng = Không bị giới hạn
        } else {
            // Nhân viên: Chỉ load chi nhánh có trong brands_linkables
            $branches = $this->db->select('branches', [
                '[>]brands_linkables' => ['id' => 'branch_id']
            ], [
                'branches.id', 
                'branches.name'
            ], [
                'brands_linkables.account_id' => $userId,
                'branches.organization_id' => $this->orgId,
                'branches.deleted' => 0,
                'ORDER' => ['branches.name' => 'ASC']
            ]);
            
            // Trích xuất mảng chứa các ID chi nhánh hợp lệ
            $allowedIds = array_column($branches, 'id');
            // Nếu không có quyền chi nhánh nào, gán ID ảo (-1) để chặn query xuất ra data
            if (empty($allowedIds)) $allowedIds = [-1]; 
        }

        // ==========================================================
        // 2. TIẾP NHẬN BỘ LỌC TỪ REQUEST
        // ==========================================================
        $fromDate = request('from_date');
        $toDate = request('to_date');
        $filterBranchId = request('filter_branch_id');

        $where = ['expenses.deleted' => 0];
        $summaryWhere = [
            "expenses.organization_id" => $this->orgId,
            "expenses.deleted" => 0
        ];

        // Lọc theo ngày
        if ($fromDate) {
            $where['expenses.expense_date[>=]'] = $fromDate;
            $summaryWhere['expenses.expense_date[>=]'] = $fromDate;
        }
        if ($toDate) {
            $where['expenses.expense_date[<=]'] = $toDate;
            $summaryWhere['expenses.expense_date[<=]'] = $toDate;
        }

        // ==========================================================
        // 3. XỬ LÝ LỌC CHI NHÁNH CÓ BẢO MẬT (CHỐNG HACK URL)
        // ==========================================================
        $finalBranchId = $this->branchId;
        if ($this->branchId === 'all' && !empty($filterBranchId)) {
            $finalBranchId = $filterBranchId;
        }

        if ($finalBranchId !== 'all') {
            // Cắt chuỗi ID từ request (vd: "1,3,4")
            $reqIds = explode(',', $finalBranchId);
            
            // Nếu là nhân viên, loại bỏ các ID chi nhánh họ cố tình gắn bậy bạ lên URL
            $validIds = ($permissionId == 1) ? $reqIds : array_intersect($reqIds, $allowedIds);
            
            $where['expenses.branch_id'] = empty($validIds) ? [-1] : $validIds;
            $summaryWhere['expenses.branch_id'] = empty($validIds) ? [-1] : $validIds;
        } else {
            // NẾU CHỌN "TẤT CẢ":
            // - Super Admin thì không filter gì cả (lấy toàn hệ thống).
            // - Nhân viên thì ép query vào danh sách các chi nhánh họ được phép (allowedIds).
            if ($permissionId != 1) {
                $where['expenses.branch_id'] = $allowedIds;
                $summaryWhere['expenses.branch_id'] = $allowedIds;
            }
        }

        $where['ORDER'] = ['expenses.expense_date' => 'DESC', 'expenses.id' => 'DESC'];

        // ==========================================================
        // 4. THỰC THI QUERY BẢNG DỮ LIỆU
        // ==========================================================
        $expenses = $this->db->select('expenses', [
            '[>]branches' => ['branch_id' => 'id'],
            '[>]expense_categories' => ['category' => 'id'] 
        ], [
            'expenses.id', 'expenses.title', 'expenses.amount', 'expenses.expense_date',
            'branches.name(branch_name)', 'expense_categories.name(category_name)'
        ], $where);

        // ==========================================================
        // 5. THỰC THI QUERY THỐNG KÊ (TOP 3 CARD TRÊN CÙNG)
        // ==========================================================
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

        $legacyNames = [
            'salary' => 'Lương / Thưởng',
            'rent' => 'Thuê mặt bằng',
            'ads' => 'Quảng cáo',
            'other' => 'Khác'
        ];

        foreach ($stats as $s) {
            $totalSum += $s['amount'];
            $catId = $s['category'];
            
            $catSums[$catId] = ($catSums[$catId] ?? 0) + $s['amount'];
            
            if (!empty($s['cat_name'])) {
                $catNamesMap[$catId] = $s['cat_name']; 
            } elseif (isset($legacyNames[$catId])) {
                $catNamesMap[$catId] = $legacyNames[$catId]; 
            } else {
                $catNamesMap[$catId] = 'Khác'; 
            }
        }
        
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

        // 6. TRẢ VỀ VIEW
        // Không query lại $branches ở đây nữa, dùng luôn mảng $branches đã lấy ở bước 1
        return view('business/expense', [
            'expenses' => $expenses,
            'totalSum' => $totalSum,
            'topCategories' => $topCategories,
            'branches' => $branches,
            'currentBranchId' => $this->branchId,
            'filter' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'branch_id' => $filterBranchId
            ]
        ]);
    }

    // HÀM MỚI: Gọi Modal Thêm khoản chi
    public function Create()
    {
        $this->requirePermission('expense.add');
        $userId = $this->user['id'] ?? 0;
        $permissionId = $this->user['permission_id'] ?? 0;

        // 1. LẤY DANH SÁCH CHI NHÁNH THEO PHÂN QUYỀN
        if ($permissionId == 1) {
            // Super Admin: Load tất cả chi nhánh
            $branches = $this->db->select('branches', ['id', 'name'], [
                'organization_id' => $this->orgId, 
                'deleted' => 0,
                'ORDER' => ['name' => 'ASC']
            ]);
        } else {
            // Nhân viên thường: Chỉ load chi nhánh có trong brands_linkables
            $branches = $this->db->select('branches', [
                '[>]brands_linkables' => ['id' => 'branch_id']
            ], [
                'branches.id', 
                'branches.name'
            ], [
                'brands_linkables.account_id' => $userId,
                'branches.organization_id' => $this->orgId,
                'branches.deleted' => 0,
                'ORDER' => ['branches.name' => 'ASC']
            ]);
        }
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
        $this->requirePermission('expense.add');
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
        $this->requirePermission('expense.edit');
        $expense = $this->db->get('expenses', '*', ['id' => $id, 'organization_id' => $this->orgId]);
        if (!$expense) return "Không tìm thấy dữ liệu!";

        $userId = $this->user['id'] ?? 0;
        $permissionId = $this->user['permission_id'] ?? 0;

        // 1. LẤY DANH SÁCH CHI NHÁNH THEO PHÂN QUYỀN
        if ($permissionId == 1) {
            // Super Admin: Load tất cả chi nhánh
            $branches = $this->db->select('branches', ['id', 'name'], [
                'organization_id' => $this->orgId, 
                'deleted' => 0,
                'ORDER' => ['name' => 'ASC']
            ]);
        } else {
            // Nhân viên thường: Chỉ load chi nhánh có trong brands_linkables
            $branches = $this->db->select('branches', [
                '[>]brands_linkables' => ['id' => 'branch_id']
            ], [
                'branches.id', 
                'branches.name'
            ], [
                'brands_linkables.account_id' => $userId,
                'branches.organization_id' => $this->orgId,
                'branches.deleted' => 0,
                'ORDER' => ['branches.name' => 'ASC']
            ]);
        }
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
        $this->requirePermission('expense.edit');
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
        $this->requirePermission('expense.delete');
        $this->db->update('expenses', ['deleted' => 1], ['id' => $id, 'organization_id' => $this->orgId]);
        return response()->json(['status' => 'success', 'alert' => 'Đã xóa khoản chi', 'redirect' => '/business/expenses']);
    }

    public function BulkDelete()
    {
        $this->requirePermission('expense.delete');
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