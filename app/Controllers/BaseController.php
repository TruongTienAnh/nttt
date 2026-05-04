<?php
namespace App\Controllers;

class BaseController
{
    protected $db;
    protected $orgId;
    protected $branchId;
    protected $user;

    public function __construct()
    {
        $this->db = app()->db;
        $this->user = $_SESSION['account'] ?? null;

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

    // Ví dụ một hàm parse chi nhánh chuẩn để dùng cho mọi báo cáo
    protected function getMultiBranchFilter($requestBranchParam) {
        // Nếu chọn tất cả, trả về rỗng để Query quét toàn hệ thống
        if (empty($requestBranchParam) || $requestBranchParam === 'all') {
            return []; 
        }

        // Nếu gửi lên là một mảng (VD từ multi-select checkbox)
        if (is_array($requestBranchParam)) {
            return ['branch_id' => $requestBranchParam]; // Trả về dạng mảng để Medoo dùng IN()
        }

        // Nếu gửi lên chuỗi cách nhau bằng dấu phẩy "id1,id2"
        if (strpos($requestBranchParam, ',') !== false) {
            return ['branch_id' => explode(',', $requestBranchParam)];
        }

        // Nếu chỉ có 1 ID
        return ['branch_id' => $requestBranchParam];
    }

    /**
     * Kiểm tra quyền (trả về true/false)
     */
    protected function can($permissionCode)
    {
        if (!$this->user) return false;

        // Super Admin: Luôn có quyền (Ví dụ ID = 1)
        if (isset($this->user['permission_id']) && $this->user['permission_id'] == 1) {
            return true;
        }
        // Kiểm tra trong mảng permissions đã unserialize ở Middleware/Auth
        return isset($this->user['permissions'][$permissionCode]);
    }

    /**
     * Chốt chặn quyền (Chặn đứng nếu không có quyền)
     */
    protected function requirePermission($permissionCode)
    {
        if (!$this->can($permissionCode)) {
            if (app()->request->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'error', 
                    'alert' => 'Bạn không có quyền thực hiện hành động này!'
                ]);
                exit;
            }
            // Nếu không phải Ajax, chuyển hướng hoặc báo lỗi
            die("Cảnh báo: Bạn không có quyền truy cập chức năng này.");
        }
    }
    /**
     * Lấy danh sách ID các chi nhánh mà User hiện tại được phép xem
     */
    protected function getAllowedBranchIds()
    {
        $userId = $this->user['id'] ?? 0;
        $permissionId = $this->user['permission_id'] ?? 0;

        if ($permissionId == 1) return []; // Admin: Trả về mảng rỗng (không giới hạn)

        $branches = $this->db->select('brands_linkables', 'branch_id', ['account_id' => $userId]);
        return !empty($branches) ? $branches : [-1]; // Chặn nếu không có quyền
    }

    /**
     * Tự động sinh điều kiện WHERE lọc theo chi nhánh an toàn
     */
    protected function getSecureBranchFilter($tableAlias = '')
    {
        $prefix = $tableAlias ? $tableAlias . '.' : '';
        $allowedIds = $this->getAllowedBranchIds();
        $where = [$prefix . 'organization_id' => $this->orgId];

        if ($this->branchId !== 'all') {
            // Nếu user chọn 1 chi nhánh: Kiểm tra quyền xem chi nhánh đó
            if ($this->user['permission_id'] == 1 || in_array($this->branchId, $allowedIds)) {
                $where[$prefix . 'branch_id'] = $this->branchId;
            } else {
                $where[$prefix . 'branch_id'] = -1; // Hack URL -> Chặn
            }
        } else {
            // Nếu chọn "Tất cả": Kế toán chỉ được thấy những chi nhánh họ được giao
            if ($this->user['permission_id'] != 1) {
                $where[$prefix . 'branch_id'] = $allowedIds;
            }
        }
        return $where;
    }
}