<?php
namespace App\Helpers;

class PermissionHelper
{
    public static function list()
    {
        return [
            'Quản trị & Hệ thống' => [
                'see-all'           => 'Quyền tối cao (Bỏ qua mọi giới hạn chi nhánh)',
                'accounts'          => 'Xem danh sách Tài khoản',
                'accounts.add'      => 'Thêm tài khoản mới',
                'accounts.edit'     => 'Cập nhật tài khoản',
                'accounts.delete'   => 'Xóa/Khóa tài khoản',
                'permission'        => 'Xem Nhóm quyền',
                'permission.add'    => 'Thêm nhóm quyền',
                'permission.edit'   => 'Sửa nhóm quyền',
                'permission.delete' => 'Xóa nhóm quyền',
            ],
            'Quản lý Cấu hình' => [
                'branch'            => 'Xem danh sách Chi nhánh',
                'branch.add'        => 'Thêm chi nhánh',
                'branch.edit'       => 'Cập nhật chi nhánh',
                'branch.delete'     => 'Xóa chi nhánh',
                'ExpenseCategories' => 'Xem danh sách Danh mục Chi phí',
                'ExpenseCategories.add_edit' => 'Thêm/Sửa danh mục chi phí',
                'ExpenseCategories.delete' => 'Xóa danh mục chi phí',
                'alerts'            => 'Xem danh sách Cảnh báo',
                'alerts.add'        => 'Thêm cảnh báo',
                'alerts.edit'       => 'Cập nhật cảnh báo',
                'alerts.delete'     => 'Xóa cảnh báo',
            ],
            'Quản lý Dữ liệu' => [
                'customers'         => 'Xem danh sách Khách hàng',
                'customers.add'     => 'Thêm khách hàng',
                'customers.edit'    => 'Cập nhật khách hàng',
                'customers.delete'  => 'Xóa khách hàng',
                'invoices'          => 'Xem danh sách Hóa đơn',
                'invoices.add'      => 'Thêm hóa đơn mới',
                'invoices.edit'     => 'Chỉnh sửa hóa đơn',
                'invoices.delete'   => 'Hủy/Xóa hóa đơn',
                'expense'           => 'Xem danh sách Chi phí',
                'expense.add'       => 'Tạo phiếu chi',
                'expense.edit'      => 'Sửa phiếu chi',
                'expense.delete'    => 'Xóa phiếu chi',
            ],
            'Báo cáo & Phân tích' => [
                'reports.customers' => 'Xem báo cáo Khách hàng (RFM, Churn...)',
                'reports.finance'   => 'Xem báo cáo Tài chính (Lợi nhuận, ROI...)',
                'reports.custom'    => 'Xem báo cáo Tùy chọn',
            ]
        ];
    }
}