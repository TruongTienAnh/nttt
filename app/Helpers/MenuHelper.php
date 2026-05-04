<?php
namespace App\Helpers;

class MenuHelper
{
    /**
     * Kiểm tra quyền xem menu
     */
    public static function canSee($permission)
    {
        $user = $_SESSION['account'] ?? null;
        if (!$user) return false;
        
        // Super Admin (ID = 1) luôn thấy tất cả
        if (($user['permission_id'] ?? 0) == 1) return true;
        
        // Nếu mục không yêu cầu quyền, ai cũng thấy
        if (empty($permission)) return true;
        
        // Kiểm tra trong mảng quyền
        return isset($user['permissions'][$permission]);
    }

    /**
     * Lấy danh sách Sidebar Menu
     */
    public static function getSidebar()
    {
        return [
            'Cấu hình' => [
                [
                    'title' => 'Chi nhánh', 
                    'link' => '/config/brands', 
                    'icon' => 'bi-geo-alt', 
                    'perm' => 'branch'
                ],
                [
                    'title' => 'Danh mục chi phí', 
                    'link' => '/config/expense-categories', 
                    'icon' => 'bi-wallet2', 
                    'perm' => 'ExpenseCategories'
                ],
                [
                    'title' => 'Cảnh báo', 
                    'link' => '/config/alerts', 
                    'icon' => 'bi-exclamation-triangle', 
                    'perm' => 'alerts'
                ],
            ],
            'Dữ liệu' => [
                [
                    'title' => 'Hóa đơn', 
                    'link' => '/business/invoices', 
                    'icon' => 'bi-receipt', 
                    'perm' => 'invoices'
                ],
                [
                    'title' => 'Khách hàng', 
                    'link' => '/business/customers', 
                    'icon' => 'bi-person-lines-fill', 
                    'perm' => 'customers'
                ],
                [
                    'title' => 'Chi phí', 
                    'link' => '/business/expenses', 
                    'icon' => 'bi-graph-up', 
                    'perm' => 'expense'
                ],
            ],
            'Báo cáo Khách hàng' => [
                [
                    'title' => 'Phân khúc RFM', 
                    'link' => '/reports/customers/rfm', 
                    'icon' => 'bi-people', 
                    'perm' => 'reports.customers'
                ],
                [
                    'title' => 'Vòng đời & Churn', 
                    'link' => '/reports/customers/churn', 
                    'icon' => 'bi-heart-pulse', 
                    'perm' => 'reports.customers'
                ],
                [
                    'title' => 'Bán chéo (Cross-sell)', 
                    'link' => '/reports/customers/cross-sell', 
                    'icon' => 'bi-cart-plus', 
                    'perm' => 'reports.customers'
                ],
            ],
            'Tài chính Chiến lược' => [
                [
                    'title' => 'Lợi nhuận ròng', 
                    'link' => '/reports/finance/net-profit', 
                    'icon' => 'bi-wallet2', 
                    'perm' => 'reports.finance'
                ],
                [
                    'title' => 'Điểm hòa vốn', 
                    'link' => '/reports/finance/break-even', 
                    'icon' => 'bi-water', 
                    'perm' => 'reports.finance'
                ],
                [
                    'title' => 'Dự báo doanh thu', 
                    'link' => '/reports/finance/forecast', 
                    'icon' => 'bi-graph-up', 
                    'perm' => 'reports.finance'
                ],
                [
                    'title' => 'Hiệu quả rót vốn', 
                    'link' => '/reports/finance/roi', 
                    'icon' => 'bi-piggy-bank', 
                    'perm' => 'reports.finance'
                ],
                [
                    'title' => 'So sánh chi nhánh', 
                    'link' => '/reports/finance/location-pnl', 
                    'icon' => 'bi-trophy', 
                    'perm' => 'reports.finance'
                ],
                [
                    'title' => 'Báo cáo động', 
                    'link' => '/reports/dynamic', 
                    'icon' => 'bi-graph-down', 
                    'perm' => 'reports.finance'
                ],
            ],
            'Quản trị' => [
                [
                    'title' => 'Tài khoản', 
                    'link' => '/user/accounts', 
                    'icon' => 'bi-person-badge', 
                    'perm' => 'accounts'
                ],
                [
                    'title' => 'Nhóm quyền', 
                    'link' => '/user/permissions', 
                    'icon' => 'bi-shield-lock', 
                    'perm' => 'permission'
                ],
            ]
        ];
    }
}