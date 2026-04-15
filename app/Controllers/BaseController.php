<?php
namespace App\Controllers;

class BaseController
{
    protected $db;
    protected $orgId;
    protected $branchId;

    public function __construct()
    {
        $this->db = app()->db;

        // 1. Lấy Organization ID từ session
        $this->orgId = $_SESSION['organization_id'] ?? "e027cf6e-538d-4257-9691-068b36e280f8";

        // 2. Logic xử lý ĐỔI CHI NHÁNH khi người dùng bấm chọn ở Sidebar
        if (isset($_GET['switch_branch'])) {
            $val = $_GET['switch_branch'];
            // Nếu chọn "all" thì lưu chuỗi "all", ngược lại lưu ID số
            $_SESSION['current_branch_id'] = ($val === 'all') ? 'all' : $val;
            
            // Reload lại trang để URL sạch sẽ
            header('Location: ' . strtok($_SERVER["REQUEST_URI"], '?'));
            exit;
        }

        // 3. Thiết lập Chi nhánh hiện tại cho các Controller con sử dụng
        $this->branchId = $_SESSION['current_branch_id'] ?? 'all';
    }

    /**
     * Hàm hỗ trợ lọc dữ liệu theo Chi nhánh
     * @param array $where Mảng điều kiện lọc ban đầu
     * @param string $tablePrefix Tên bảng để nối vào (dùng khi có JOIN), VD: 'invoices'
     */
    protected function branchFilter($where = [], $tablePrefix = '')
    {
        // Nếu có truyền tên bảng, tự động nối thêm dấu chấm. VD: 'invoices.'
        $prefix = $tablePrefix ? $tablePrefix . '.' : '';

        // Tạo key có định dạng: 'invoices.organization_id'
        $where[$prefix . 'organization_id'] = $this->orgId;
        
        if ($this->branchId !== 'all') {
            $where[$prefix . 'branch_id'] = $this->branchId;
        }
        
        return $where;
    }
}