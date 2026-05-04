<?php
namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthApiMiddleware
{
    public function handle($app)
    {
        // 1. Check Session trước
        $sessionUser = $app->session->get('account');

        if ($sessionUser) {
            // Lấy trạng thái mới nhất của User từ DB
            $accDb = $app->db->get("accounts", ["permission_id", "status", "deleted"], [
                "uuid" => $sessionUser['uuid']
            ]);

            // Tài khoản bị khóa hoặc bị xóa -> Đá văng
            if (!$accDb || $accDb['status'] == 0 || $accDb['deleted'] == 1) {
                $this->logout($app);
                return handleUnauthenticated();
            }

            // [THÊM MỚI]: Kiểm tra xem Nhóm quyền có đang bị tắt không?
            // (Bỏ qua check nếu là Super Admin id = 1 để tránh tự bóp mình)
            if ($accDb['permission_id'] != 1) {
                $isRoleActive = $app->db->has("permissions", [
                    "id" => $accDb['permission_id'],
                    "status" => 'A', // Phải đang Active
                    "deleted" => 0
                ]);

                if (!$isRoleActive) {
                    // Nếu nhóm quyền bị tắt -> Hủy session bắt đăng nhập lại
                    $this->logout($app);
                    return handleUnauthenticated(); 
                }
            }
            
            // Gán user vào request để Controller dùng
            $app->request->user = (object) $sessionUser; 
            return true; // Cho qua
        }

        // 2. Check Token (Remember Me)
        $token = $app->cookie->get('token');
        if (!$token) return handleUnauthenticated();

        try {
            $key = $_ENV['APP_KEY'] ?? 'secret_key';
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            
            // Check bảo mật Agent (chống trộm token)
            $currentAgent = $_SERVER["HTTP_USER_AGENT"];
            $loginRecord = $app->db->get("accounts_login", "*", [
                "account"  => $decoded->uid, 
                "token"    => $decoded->token,
                "agent"    => $currentAgent,
                "deleted"  => 0
            ]);

            if (!$loginRecord) {
                throw new \Exception("Session expired or invalid");
            }

            // Lấy thông tin mới nhất từ DB
            $account = $app->db->get("accounts", ["uuid", "name", "email", "avatar", "type", "permission_id"], [
                "uuid" => $decoded->uid
            ]);

            if (!$account) throw new \Exception("Account not found");

            $userPermissions = [];
            if (!empty($account['permission_id'])) {
                $role = $app->db->get("permissions", ["permissions"], [
                    "id" => $account['permission_id'],
                    "deleted" => 0,
                    "status" => 'A'
                ]);

                if ($role && !empty($role['permissions'])) {
                    // Dùng @ để bỏ qua cảnh báo nếu chuỗi lưu bị lỗi format
                    $decodedPerms = @unserialize($role['permissions']);
                    if (is_array($decodedPerms)) {
                        $userPermissions = $decodedPerms;
                    }
                }
            }

            // Tự động set lại session
            $userData = [
                "uuid"          => $account['uuid'],
                "name"          => $account['name'],
                "avatar"        => $account['avatar'],
                "email"         => $account['email'],
                "permission_id" => $account['permission_id'], 
                "permissions"   => $userPermissions // Mảng quyền cực kỳ quan trọng
            ];
            
            $app->session->set('account', $userData);
            $app->request->user = (object) $userData;
            
            return true;

        } catch (\Exception $e) {
            $this->logout($app);
            return handleUnauthenticated();
        }
    }

    protected function logout($app) {
        $app->session->forget('account');
        $app->cookie->forget('token');
    }
}