<?php
namespace App\Controllers;

class ExpenseBusinessController
{
    public function Index()
    {
        $orgId = $_SESSION['organization_id'] ?? "e027cf6e-538d-4257-9691-068b36e280f8";
        if (!$orgId) { header('Location: /login'); exit; }

        $db = app()->db;

        // 1. Lấy danh sách chi phí (Loại bỏ những cái đã xóa nếu dùng soft-delete)
        $expenses = $db->select('expenses', '*', [
            'organization_id' => $orgId,
            'deleted' => 0, // Mở comment này nếu bảng của bạn có cột deleted
            'ORDER' => ['expense_date' => 'DESC', 'id' => 'DESC']
        ]);

        // 2. Tính tổng chi phí tháng này theo danh mục
        $currentMonth = date('Y-m');
        $stats = $db->query("
            SELECT category, SUM(amount) as total
            FROM expenses 
            WHERE organization_id = :org AND DATE_FORMAT(expense_date, '%Y-%m') = :month
            -- AND deleted = 0
            GROUP BY category
        ", ['org' => $orgId, 'month' => $currentMonth])->fetchAll();

        $summary = ['total' => 0, 'salary' => 0, 'rent' => 0, 'ads' => 0, 'other' => 0];
        foreach ($stats as $s) {
            $cat = $s['category'];
            if (isset($summary[$cat])) $summary[$cat] += $s['total'];
            $summary['total'] += $s['total'];
        }

        return view('business/expense', [
            'expenses' => $expenses,
            'summary'  => $summary
        ]);
    }

    public function Store()
    {
        $orgId = $_SESSION['organization_id'] ?? "e027cf6e-538d-4257-9691-068b36e280f8";

        $validator = app()->validate(
            [
                'title'        => 'required',
                'amount'       => 'required',
                'expense_date' => 'required'
            ],
            [
                'title.required'        => 'Vui lòng nhập tên khoản chi',
                'amount.required'       => 'Vui lòng nhập số tiền',
                'expense_date.required' => 'Vui lòng chọn ngày chi'
            ]
        );

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'alert' => $validator->first()], 400);
        }

        app()->db->insert('expenses', [
            'organization_id' => $orgId,
            'title'           => app()->xss->clean(request('title')),
            'category'        => request('category') ?? 'other',
            'amount'          => str_replace(',', '', request('amount')),
            'expense_date'    => request('expense_date'),
            'note'            => app()->xss->clean(request('note') ?? ''),
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s')
        ]);

        return response()->json(['status' => 'success', 'alert' => 'Thêm chi phí thành công']);
    }

    public function Edit($id)
    {
        $orgId = $_SESSION['organization_id'] ?? "e027cf6e-538d-4257-9691-068b36e280f8";

        $expense = app()->db->get('expenses', '*', [
            'id' => $id,
            'organization_id' => $orgId
        ]);

        if (!$expense) return "Không tìm thấy dữ liệu!";

        // Trả về một đoạn HTML để nhét vào Modal Edit (giống hệt cách làm của Brands)
        return view('business/expense-post', ['expense' => $expense]);
    }

    public function Update($id)
    {
        $orgId = $_SESSION['organization_id'] ?? "e027cf6e-538d-4257-9691-068b36e280f8";

        $validator = app()->validate(['title' => 'required', 'amount' => 'required']);
        if ($validator->fails()) return response()->json(['status' => 'error', 'alert' => 'Thiếu thông tin bắt buộc']);

        app()->db->update('expenses', [
            'title'        => app()->xss->clean(request('title')),
            'category'     => request('category'),
            'amount'       => str_replace(',', '', request('amount')),
            'expense_date' => request('expense_date'),
            'note'         => app()->xss->clean(request('note') ?? ''),
            'updated_at'   => date('Y-m-d H:i:s')
        ], [
            'id' => $id,
            'organization_id' => $orgId
        ]);

        return response()->json(['status' => 'success', 'alert' => 'Cập nhật thành công']);
    }

    public function Delete($id)
    {
        $orgId = $_SESSION['organization_id'] ?? "e027cf6e-538d-4257-9691-068b36e280f8";

        // Nếu dùng Soft Delete thì đổi sang update ['deleted' => 1]
        app()->db->update('expenses', ['deleted' => 1], ['id' => $id, 'organization_id' => $orgId]);

        return response()->json(['status' => 'success', 'alert' => 'Đã xóa khoản chi']);
    }
}