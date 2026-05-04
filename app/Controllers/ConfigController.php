<?php
namespace App\Controllers;

class ConfigController extends BaseController
{
    public function Brands()
    {
        $this->requirePermission('branch');
        
        $branches = $this->db->select('branches', '*', [
            'deleted' => 0,
            'ORDER'   => ['id' => 'DESC'],
        ]);
    
        return view('config/brands', [
            'brands' => $branches
        ]);
    }
    
    public function BrandCreate() {
        return view('config/brands-post', ['brand' => null]);
    }

    public function BrandEdit($active) {
        $brand = $this->db->get("branches", "*", ["active" => $active, "deleted" => 0]);
        if (!$brand) return "Không tìm thấy chi nhánh!";
        return view('config/brands-post', ['brand' => $brand]);
    }

    public function BrandStore()
    {
        $this->requirePermission('branch.add');
        $this->db->insert('branches', [
            'organization_id' => $this->orgId,
            'active'    => uuid(),
            'name'      => request('name'),
            'address'   => request('address'),
            'phone'     => request('phone'),
            'type'      => request('type'),
            'is_active' => 1,
            'deleted'   => 0,
            'created_at'=> date('Y-m-d H:i:s')
        ]);

        return response()->json(['status' => 'success', 'alert' => 'Thêm chi nhánh thành công', 'redirect' => '/config/brands']);
    }

    public function BrandUpdate($active)
    {
        $this->requirePermission('branch.edit');
        $this->db->update('branches', [
            'name'      => request('name'),
            'address'   => request('address'),
            'phone'     => request('phone'),
            'type'      => request('type'),
            'updated_at'=> date('Y-m-d H:i:s')
        ], ["active" => $active]);

        return response()->json(['status' => 'success', 'alert' => 'Cập nhật thành công', 'redirect' => '/config/brands']);
    }

    public function BrandToggle($active) {
        $this->requirePermission('branch.edit');
        $brand = $this->db->get("branches", ["is_active"], ["active" => $active]);
        if ($brand) {
            $newStatus = ($brand['is_active'] == 1) ? 0 : 1;
            $this->db->update("branches", ["is_active" => $newStatus], ["active" => $active]);
            return response()->json(['status' => 'success', 'toast' => 'Đã chuyển trạng thái']);
        }
    }

    public function BrandDelete($active) {
        $this->requirePermission('branch.delete');
        $this->db->update("branches", ["deleted" => 1], ["active" => $active]);
        return response()->json(['status' => 'success', 'alert' => 'Đã xóa chi nhánh', 'redirect' => '/config/brands']);
    }

    // ==========================================
    // DANH MỤC CHI PHÍ (EXPENSE CATEGORIES)
    // ==========================================

    // ==========================================
    // DANH MỤC CHI PHÍ (EXPENSE CATEGORIES)
    // ==========================================

    public function ExpenseCategories() {
        $this->requirePermission('ExpenseCategories');
        $orgId = $_SESSION['organization_id'] ?? "e027cf6e-538d-4257-9691-068b36e280f8";
        
        $categories = app()->db->select("expense_categories", "*", [
            "organization_id" => $orgId,
            "deleted" => 0,
            "ORDER" => ["created_at" => "DESC"]
        ]);

        return view('config/expense-categories', [
            'categories' => $categories
        ]);
    }

    public function ExpenseCategoryPost() {
        $this->requirePermission('ExpenseCategories.add_edit');
        $id = request('id');
        $category = null;
        if ($id) {
            $category = app()->db->get("expense_categories", "*", ["id" => $id]);
        }

        return view('config/expense-categories-post', [
            'category' => $category
        ]);
    }

    public function SaveExpenseCategory() {
        $this->requirePermission('ExpenseCategories.add_edit');
        $id = request('id');
        $orgId = $_SESSION['organization_id'] ?? "e027cf6e-538d-4257-9691-068b36e280f8";
        
        $data = [
            "name" => app()->xss->clean(request('name')),
            "description" => app()->xss->clean(request('description')),
            "is_active" => request('is_active') ? 1 : 0,
            "updated_at" => date('Y-m-d H:i:s')
        ];

        if (empty($data['name'])) {
            return response()->json(['status' => 'error', 'alert' => 'Vui lòng nhập tên danh mục!']);
        }

        if (!$id) {
            $data['id'] = uuid();
            $data['organization_id'] = $orgId;
            $data['deleted'] = 0;
            $data['created_at'] = date('Y-m-d H:i:s');
            app()->db->insert("expense_categories", $data);
        } else {
            app()->db->update("expense_categories", $data, ["id" => $id]);
        }

        return response()->json([
            'status' => 'success',
            'alert' => 'Lưu danh mục thành công!',
            'redirect' => '/config/expense-categories' // main.bundle.js sẽ bắt lệnh này để đóng modal và load lại bảng
        ]);
    }

    public function ToggleExpenseCategoryStatus() {
        $this->requirePermission('ExpenseCategories.add_edit');
        $id = request('id');
        if ($id) {
            app()->db->update("expense_categories", [
                "is_active" => request('status'),
                "updated_at" => date('Y-m-d H:i:s')
            ], ["id" => $id]);

            return response()->json([
                'status' => 'success',
                'alert' => 'Đã cập nhật trạng thái!'
            ]);
        }
    }

    public function DeleteExpenseCategory() {
        $this->requirePermission('ExpenseCategories.delete');
        $id = request('id');
        if ($id) {
            app()->db->update("expense_categories", [
                "deleted" => 1,
                "updated_at" => date('Y-m-d H:i:s')
            ], ["id" => $id]);
            
            return response()->json([
                'status' => 'success', 
                'alert' => 'Đã xóa danh mục thành công!',
                'redirect' => '/config/expense-categories' // Reload bảng để cập nhật
            ]);
        }
    }

    public function BulkDeleteExpenseCategories() {
        $this->requirePermission('ExpenseCategories.delete');
        $idsRaw = request('ids'); // Nhận về chuỗi "id1,id2,id3"
        
        // Cắt chuỗi thành mảng
        if (is_string($idsRaw)) {
            $ids = array_filter(explode(',', $idsRaw));
        } else {
            $ids = $idsRaw;
        }

        if (empty($ids) || !is_array($ids)) {
            return response()->json(['status' => 'error', 'alert' => 'Vui lòng chọn ít nhất một mục để xóa!']);
        }

        app()->db->update("expense_categories", [
            "deleted" => 1,
            "updated_at" => date('Y-m-d H:i:s')
        ], [
            "id" => $ids // Medoo hỗ trợ truyền mảng ID để update hàng loạt
        ]);

        return response()->json([
            'status' => 'success',
            'alert' => 'Đã xóa ' . count($ids) . ' mục thành công!',
            'redirect' => '/config/expense-categories'
        ]);
    }
}