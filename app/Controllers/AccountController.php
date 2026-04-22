<?php
namespace App\Controllers;

use Neo\Core\Controller;
use Firebase\JWT\JWT;

class AccountController
{
    protected $app;

    public function __construct(){
        $this->app = app();
    }

    public function Account() {
        $user = app()->request->user;
        if (app()->request->isHtmx()) {
            
            return view('account/account', [
                'user' => $user,
            ]);
        }
        header('Location: /app/profile');
        exit;
    }

    public function Profiles() {
        $user = app()->request->user;
        $account = (object) app()->db->get("accounts",
        [
            "accounts.email",
            "accounts.name",
            "accounts.avatar",
            "accounts.phone",
            "accounts.organization",
            "accounts.type",
            "accounts.affiliate",
        ],
        [
            "uuid"=>$user->uuid
        ]);
        $account->type = $account->type==0?'Thành Viên':'Quản trị';
        return view('account/profiles', [
            'user' => $account,
        ]);
    }

    public function UpdateInformation(){
        $userId = app()->request->user->uuid;
        if (!$userId) {
            return response()->json(['status' => 'error', 'alert' => 'Vui lòng đăng nhập'], 401);
        }

        // 2. Validate dữ liệu
        $validator = app()->validate(
            [
                'name' => 'required',
                'phone' => 'required', // Ví dụ thêm validate số điện thoại
            ],
            [
                'name.required' => 'Tên không được để trống',
                'phone.required' => 'Số điện thoại không được để trống',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'alert' => $validator->first(),
            ], 400);
        }

        // 3. Chuẩn bị dữ liệu update (Clean XSS)
        $updateData = [
            "name"         => app()->xss->clean(request('name')),
            "phone"        => app()->xss->clean(request('phone')),
            "organization" => app()->xss->clean(request('organization')),
        ];

        // 4. Thực hiện Update vào DB
        // QUAN TRỌNG: Tham số thứ 3 là điều kiện WHERE để không update nhầm người khác
        $result = app()->db->update("accounts", $updateData, ["uuid" => $userId]);

        if ($result) {
            return response()->json([
                'status' => 'success',
                'alert' => 'Cập nhật thông tin thành công',
            ]);
        }

        return response()->json(['status' => 'error', 'alert' => 'Có lỗi xảy ra, vui lòng thử lại'], 500);
    }

    // HÀM 2: CHỈ ĐỔI MẬT KHẨU
    public function ChangePassword()
    {
        // 1. Lấy ID người dùng
        $userId = app()->request->user->uuid;
        if (!$userId) {
            return response()->json(['status' => 'error', 'alert' => 'Vui lòng đăng nhập'], 401);
        }

        // 2. Validate dữ liệu
        $validator = app()->validate(
            [
                'password_old'     => 'required',
                'password'         => 'required|min:6', // Mật khẩu mới tối thiểu 6 ký tự
                'password_confirm' => 'required|same:password', // Phải khớp với mật khẩu mới
            ],
            [
                'password_old.required'     => 'Vui lòng nhập mật khẩu cũ',
                'password.required'         => 'Vui lòng nhập mật khẩu mới',
                'password.min'              => 'Mật khẩu mới phải có ít nhất 6 ký tự',
                'password_confirm.required' => 'Vui lòng nhập lại mật khẩu mới',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'alert' => $validator->first(),
            ], 400);
        }

        // 3. Kiểm tra mật khẩu cũ có đúng không
        // Giả sử lấy user từ DB
        $user = app()->db->get('accounts',"*", ['uuid' => $userId]); 

        if (!$user) {
            return response()->json(['status' => 'error', 'alert' => 'Tài khoản không tồn tại'], 404);
        }

        // So sánh mật khẩu cũ (Dùng password_verify nếu mật khẩu được mã hóa Hash)
        if (!password_verify(request('password_old'), $user['password'])) {
            return response()->json(['status' => 'error', 'alert' => 'Mật khẩu cũ không chính xác'], 400);
        }

        if (request('password')!==request('password_confirm')) {
            return response()->json([
                'status'  => 'error',
                'alert' => 'Mật khẩu xác nhận không giống.'
            ], 401);
        }

        // 4. Update mật khẩu mới (MÃ HÓA TRƯỚC KHI LƯU)
        $updateData = [
            "password" => password_hash(request('password'), PASSWORD_BCRYPT),
        ];

        app()->db->update("accounts", $updateData, ["uuid" => $userId]);

        return response()->json([
            'status' => 'success',
            'alert' => 'Đổi mật khẩu thành công',
        ]);
    }

    public function Affiliate() {
        $user = app()->request->user;
        return view('account/affiliate', [
            'user' => $user,
        ]);
    }

    public function Payments() {
        $user = app()->request->user;
        return view('account/payments', [
            'user' => $user,
        ]);
    }
}