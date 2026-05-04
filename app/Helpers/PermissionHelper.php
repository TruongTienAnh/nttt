<?php
namespace App\Helpers;

class PermissionHelper
{
    public static function list()
    {
        return [
            '1. Quản trị & Hệ thống' => [
                'see-all'           => 'Quyền tối cao (Bỏ qua mọi giới hạn chi nhánh)',
                'config'            => 'Cấu hình hệ thống (Cài đặt, Cảnh báo, Danh mục)',
                'accounts'          => 'Xem danh sách Tài khoản',
                'accounts.add'      => 'Thêm tài khoản mới',
                'accounts.edit'     => 'Cập nhật tài khoản',
                'accounts.delete'   => 'Xóa/Khóa tài khoản',
                'permission'        => 'Xem Nhóm quyền',
                'permission.add'    => 'Thêm nhóm quyền',
                'permission.edit'   => 'Sửa nhóm quyền',
                'permission.delete' => 'Xóa nhóm quyền',
            ],
            '2. Quản lý Chi nhánh' => [
                'branch'            => 'Xem danh sách Chi nhánh',
                'branch.add'        => 'Thêm chi nhánh',
                'branch.edit'       => 'Cập nhật chi nhánh',
                'branch.delete'     => 'Xóa chi nhánh',
            ],
            '3. Dữ liệu Khách hàng & Lịch hẹn' => [
                'customers'         => 'Xem danh sách Khách hàng',
                'customers.add'     => 'Thêm khách hàng',
                'customers.edit'    => 'Cập nhật khách hàng',
                'customers.delete'  => 'Xóa khách hàng',
                'booking'           => 'Xem danh sách Lịch hẹn',
                'booking.add'       => 'Tạo lịch hẹn mới',
                'booking.edit'      => 'Cập nhật lịch hẹn',
                'booking.delete'    => 'Hủy lịch hẹn',
            ],
            '4. Hóa đơn & Thu Chi' => [
                'invoices'          => 'Xem danh sách Hóa đơn',
                'invoices.add'      => 'Tạo hóa đơn mới',
                'invoices.edit'     => 'Chỉnh sửa hóa đơn',
                'invoices.delete'   => 'Hủy/Xóa hóa đơn',
                'expenditure'       => 'Xem danh sách Chi phí',
                'expenditure.add'   => 'Tạo phiếu chi',
                'expenditure.edit'  => 'Sửa phiếu chi',
                'expenditure.delete'=> 'Xóa phiếu chi',
            ],
            '5. Báo cáo & Phân tích' => [
                'reports.customers' => 'Xem báo cáo Khách hàng (RFM, Churn...)',
                'reports.finance'   => 'Xem báo cáo Tài chính (Lợi nhuận, ROI...)',
                'export.data'       => 'Quyền xuất dữ liệu (Export Excel/PDF)',
            ]
        ];
    }
}