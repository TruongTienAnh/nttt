<?php
namespace App\Controllers;
use App\Helpers\PermissionHelper;

class UserController extends BaseController 
{
    // ==========================================================
    // MODULE 1: QUẢN LÝ NHÓM QUYỀN (PERMISSIONS)
    // ==========================================================

    public function PermissionIndex() {
        $this->requirePermission('permission');
        $roles = $this->db->select("permissions", "*", ["deleted" => 0, "ORDER" => ["id" => "DESC"]]);
        return view('user/permissions/index', ['roles' => $roles]);
    }

    public function PermissionCreate() {
        $this->requirePermission('permission.add');
        return view('user/permissions/post', [
            'role' => null,
            'allPermissions' => PermissionHelper::list()
        ]);
    }

    public function PermissionEdit($active) {
        $this->requirePermission('permission.edit');
        $role = $this->db->get("permissions", "*", ["active" => $active, "deleted" => 0]);
        if (!$role) return "Không tìm thấy dữ liệu!";
        
        $role['permission_array'] = @unserialize($role['permissions']) ?: [];
        return view('user/permissions/post', ['role' => $role, 'allPermissions' => PermissionHelper::list()]);
    }

    public function PermissionStore() {
        $this->requirePermission('permission.add');
        $perms = request('perms') ?: [];
        $dataPerms = array_combine($perms, $perms);

        $this->db->insert("permissions", [
            "name"        => request('name'),
            "permissions" => serialize($dataPerms),
            "status"      => 'A',
            "active"      => uuid(),
            "deleted"     => 0,
            "created_at"  => date('Y-m-d H:i:s')
        ]);
        return response()->json(['status' => 'success', 'alert' => 'Tạo nhóm quyền thành công', 'redirect' => '/user/permissions']);
    }

    public function PermissionUpdate($active) {
        $this->requirePermission('permission.edit');
        $perms = request('perms') ?: [];
        $dataPerms = array_combine($perms, $perms);

        $this->db->update("permissions", [
            "name"        => request('name'),
            "permissions" => serialize($dataPerms)
        ], ["active" => $active]);

        return response()->json(['status' => 'success', 'alert' => 'Cập nhật thành công', 'redirect' => '/user/permissions']);
    }

    public function PermissionToggle($active) {
        $this->requirePermission('permission.edit');
        $role = $this->db->get("permissions", ["status"], ["active" => $active]);
        if ($role) {
            $newStatus = ($role['status'] == 'A') ? 'D' : 'A';
            $this->db->update("permissions", ["status" => $newStatus], ["active" => $active]);
            return response()->json(['status' => 'success', 'toast' => 'Đã cập nhật trạng thái']);
        }
    }

    public function PermissionDelete($active) {
        $this->requirePermission('permission.delete');
        $this->db->update("permissions", ["deleted" => 1], ["active" => $active]);
        return response()->json(['status' => 'success', 'alert' => 'Đã xóa nhóm quyền', 'redirect' => '/user/permissions']);
    }


    // ==========================================================
    // MODULE 2: QUẢN LÝ TÀI KHOẢN (ACCOUNTS)
    // ==========================================================

    public function AccountIndex() {
        $this->requirePermission('accounts');
        $accounts = $this->db->select("accounts", "*", ["deleted" => 0, "ORDER" => ["id" => "DESC"]]);
        
        // Nạp thêm tên nhóm quyền để hiển thị ra bảng
        foreach($accounts as &$acc) {
            if ($acc['permission_id']) {
                $role = $this->db->get("permissions", ["name"], ["id" => $acc['permission_id']]);
                $acc['role_name'] = $role ? $role['name'] : 'Chưa phân quyền';
            } else {
                $acc['role_name'] = 'Chưa phân quyền';
            }
        }

        return view('user/accounts/index', ['accounts' => $accounts]);
    }

    public function AccountCreate() {
        $this->requirePermission('accounts.add');
        $roles = $this->db->select("permissions", ["id", "name"], ["deleted" => 0, "status" => 'A']);
        $branches = $this->db->select("branches", ["id", "name"], ["deleted" => 0]);
        return view('user/accounts/post', ['account' => null, 'roles' => $roles, 'branches' => $branches]);
    }

    public function AccountEdit($active) {
        $this->requirePermission('accounts.edit');
        $account = $this->db->get("accounts", "*", ["uuid" => $active]);
        if (!$account) return "Không tìm thấy tài khoản!";

        $roles = $this->db->select("permissions", ["id", "name"], ["deleted" => 0, "status" => 'A']);
        $branches = $this->db->select("branches", ["id", "name"], ["deleted" => 0]);
        
        // Lấy danh sách chi nhánh đã chọn của User này
        $account['branch_ids'] = $this->db->select("brands_linkables", "branch_id", ["account_id" => $account['id']]);

        return view('user/accounts/post', ['account' => $account, 'roles' => $roles, 'branches' => $branches]);
    }

    public function AccountStore() {
        $this->requirePermission('accounts.add');
        $email = app()->xss->clean(request('email'));
        $username = app()->xss->clean(request('account'));

        // Kiểm tra trùng lặp
        if ($this->db->has("accounts", ["email" => $email, "deleted" => 0])) {
            return response()->json(['status' => 'error', 'alert' => 'Email đã được sử dụng!']);
        }
        if ($this->db->has("accounts", ["account" => $username, "deleted" => 0])) {
            return response()->json(['status' => 'error', 'alert' => 'Tên đăng nhập đã tồn tại!']);
        }

        // Tạo tài khoản mới
        $this->db->insert("accounts", [
            "uuid"            => uuid(),
            "organization_id" => $this->orgId,
            "name"            => request('name'),
            "email"           => $email,
            "account"         => $username,
            "password"        => password_hash(request('password'), PASSWORD_DEFAULT),
            "permission_id"   => request('permission_id'),
            "status"          => 1,
            "deleted"         => 0,
            "date"      => date('Y-m-d H:i:s')
        ]);
        
        $accId = $this->db->id();
        
        // Lưu phân quyền chi nhánh
        $branchIds = request('branch_ids') ?: [];
        foreach($branchIds as $bId) {
            $this->db->insert("brands_linkables", ["account_id" => $accId, "branch_id" => $bId]);
        }

        return response()->json(['status' => 'success', 'alert' => 'Tạo tài khoản thành công', 'redirect' => '/user/accounts']);
    }

    public function AccountUpdate($active) {
        $this->requirePermission('accounts.edit');
        
        $account = $this->db->get("accounts", ["id"], ["uuid" => $active]);
        if (!$account) return response()->json(['status' => 'error', 'alert' => 'Tài khoản không tồn tại']);

        $updateData = [
            "name"          => request('name'),
            "permission_id" => request('permission_id'),
        ];

        // Nếu người dùng nhập mật khẩu mới thì mới cập nhật mật khẩu
        $newPassword = request('password');
        if (!empty($newPassword)) {
            $updateData['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        $this->db->update("accounts", $updateData, ["uuid" => $active]);

        // Cập nhật chi nhánh: Xóa cũ -> Thêm mới
        $this->db->delete("brands_linkables", ["account_id" => $account['id']]);
        $branchIds = request('branch_ids') ?: [];
        foreach($branchIds as $bId) {
            $this->db->insert("brands_linkables", ["account_id" => $account['id'], "branch_id" => $bId]);
        }

        return response()->json(['status' => 'success', 'alert' => 'Cập nhật tài khoản thành công', 'redirect' => '/user/accounts']);
    }

    public function AccountToggle($active) {
        $this->requirePermission('accounts.edit');
        $acc = $this->db->get("accounts", ["status"], ["uuid" => $active]);
        if ($acc) {
            $newStatus = ($acc['status'] == 1) ? 0 : 1;
            $this->db->update("accounts", ["status" => $newStatus], ["uuid" => $active]);
            return response()->json(['status' => 'success', 'toast' => 'Cập nhật trạng thái thành công']);
        }
    }

    public function AccountDelete($active) {
        $this->requirePermission('accounts.delete');
        $this->db->update("accounts", ["deleted" => 1], ["uuid" => $active]);
        return response()->json(['status' => 'success', 'alert' => 'Đã xóa tài khoản', 'redirect' => '/user/accounts']);
    }
}