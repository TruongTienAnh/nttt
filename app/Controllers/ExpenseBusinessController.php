<?php
namespace App\Controllers;

class ExpenseBusinessController extends BaseController
{
    public function Index()
    {
        // 1. Sử dụng branchFilter với prefix 'expenses' để tránh lỗi Ambiguous khi JOIN
        $where = $this->branchFilter([
            'expenses.deleted' => 0,
            'ORDER' => ['expenses.expense_date' => 'DESC', 'expenses.id' => 'DESC']
        ], 'expenses');

        $expenses = $this->db->select('expenses', [
            '[>]branches' => ['branch_id' => 'id']
        ], [
            'expenses.id',
            'expenses.title',
            'expenses.amount',
            'expenses.expense_date',
            'expenses.category',
            'expenses.note',
            'expenses.branch_id',
            'branches.name(branch_name)' // Lấy tên chi nhánh từ bảng JOIN
        ], $where);

        // 2. Tính tổng chi phí tháng này (Thống kê)
        $currentMonth = date('Y-m');
        $params = ['org' => $this->orgId, 'month' => $currentMonth];
        
        $branchSql = "";
        if ($this->branchId !== 'all') {
            $branchSql = " AND branch_id = :branch ";
            $params['branch'] = $this->branchId;
        }

        $stats = $this->db->query("
            SELECT category, SUM(amount) as total
            FROM expenses 
            WHERE organization_id = :org $branchSql
            AND DATE_FORMAT(expense_date, '%Y-%m') = :month
            AND deleted = 0
            GROUP BY category
        ", $params)->fetchAll();

        $summary = ['total' => 0, 'salary' => 0, 'rent' => 0, 'ads' => 0, 'other' => 0];
        foreach ($stats as $s) {
            if (isset($summary[$s['category']])) $summary[$s['category']] += $s['total'];
            $summary['total'] += $s['total'];
        }

        // 3. Lấy danh sách chi nhánh cho Modal
        $branches = $this->db->select('branches', ['id', 'name'], [
            'organization_id' => $this->orgId,
            'deleted' => 0
        ]);

        return view('business/expense', [
            'expenses' => $expenses,
            'summary' => $summary,
            'branches' => $branches,
            'currentBranchId' => $this->branchId
        ]);
    }

    public function Store()
    {
        $validator = app()->validate(['title' => 'required', 'amount' => 'required', 'expense_date' => 'required']);
        if ($validator->fails()) return response()->json(['status' => 'error', 'alert' => $validator->first()], 400);

        $branchToSave = ($this->branchId === 'all') ? request('branch_id') : $this->branchId;

        $insert = $this->db->insert('expenses', [
            'organization_id' => $this->orgId,
            'branch_id'       => !empty($branchToSave) ? $branchToSave : null,
            'title'           => app()->xss->clean(request('title')),
            'category'        => request('category') ?? 'other',
            'amount'          => str_replace(',', '', request('amount')),
            'expense_date'    => request('expense_date'),
            'note'            => app()->xss->clean(request('note') ?? ''),
            'deleted'         => 0,
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s')
        ]);

        if (!$insert) {
            return response()->json(['status' => 'error', 'alert' => 'Lỗi lưu dữ liệu: ' . $this->db->error()[2]], 500);
        }

        return response()->json(['status' => 'success', 'alert' => 'Thêm chi phí thành công']);
    }

    public function Edit($id)
    {
        $expense = $this->db->get('expenses', '*', [
            'id' => $id,
            'organization_id' => $this->orgId
        ]);

        if (!$expense) return "Không tìm thấy dữ liệu!";

        $branches = $this->db->select('branches', ['id', 'name'], ['organization_id' => $this->orgId, 'deleted' => 0]);

        return view('business/expense-post', [
            'expense' => $expense,
            'branches' => $branches,
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

        $this->db->update('expenses', $updateData, [
            'id' => $id,
            'organization_id' => $this->orgId
        ]);

        return response()->json(['status' => 'success', 'alert' => 'Cập nhật thành công']);
    }

    public function Delete($id)
    {
        $this->db->update('expenses', ['deleted' => 1], [
            'id' => $id,
            'organization_id' => $this->orgId
        ]);

        return response()->json(['status' => 'success', 'alert' => 'Đã xóa khoản chi']);
    }
}