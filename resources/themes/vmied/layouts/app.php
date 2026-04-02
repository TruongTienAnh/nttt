<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eclo</title>
    
    <script>
        const savedTheme = localStorage.getItem('eclo-theme-pref') || 'system';
        const isDark = savedTheme === 'dark' || (savedTheme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
        document.documentElement.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');
    </script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://unpkg.com/htmx.org@1.9.11"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        :root {
            --bs-body-font-size: 0.875rem;
            --eclo-sidebar-width: 260px; 
            --eclo-hover-bg: rgba(0, 0, 0, 0.04);
            --eclo-transition: all 0.2s ease-in-out;
            
            --eclo-accent-color: #f25f5c;
            --eclo-accent-gradient: linear-gradient(135deg, #f25f5c 0%, #dc2f2c 100%);
            --eclo-accent-soft: rgba(242, 95, 92, 0.1);
            
            --eclo-success-color: #2a9d8f;
            --eclo-warning-color: #e9c46a;
            --eclo-blue-color: #457b9d;
            
            --eclo-card-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
            --eclo-card-hover-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
            --eclo-dropdown-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            
            --eclo-dropdown-bg: #ffffff;
            --eclo-card-bg: #ffffff;
        }

        [data-bs-theme="dark"] {
            --bs-body-color: #e0e0e0;
            --bs-secondary-color: #888888;
            --bs-border-color-translucent: rgba(255,255,255, 0.08);
            --eclo-hover-bg: rgba(255, 255, 255, 0.05);
            --eclo-accent-soft: rgba(242, 95, 92, 0.15);
            --eclo-card-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            --eclo-card-hover-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
            --eclo-dropdown-shadow: 0 10px 30px rgba(0, 0, 0, 0.6);
            --eclo-dropdown-bg: #141414;
            --eclo-card-bg: #111111;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            overflow: hidden; 
            transition: background 0.3s ease;
        }
        [data-bs-theme="light"] body { background: #f8f9fa; }
        [data-bs-theme="dark"] body { background: #0a0a0a; }

        .fs-7 { font-size: 0.85rem; }
        .fs-8 { font-size: 0.75rem; }
        .cursor-pointer { cursor: pointer; }
        .text-accent { color: var(--eclo-accent-color) !important; }
        
        .hover-bg { transition: var(--eclo-transition); border-radius: 6px; margin: 0.1rem 0.5rem; }
        .hover-bg:hover { background-color: var(--eclo-hover-bg); color: var(--bs-body-color) !important; }
        
        .clean-card {
            background: var(--eclo-card-bg) !important;
            border: 1px solid var(--bs-border-color-translucent) !important;
            box-shadow: var(--eclo-card-shadow);
            transition: var(--eclo-transition);
        }
        .clean-card.hover-card:hover { 
            transform: translateY(-2px); 
            box-shadow: var(--eclo-card-hover-shadow); 
            border-color: rgba(242, 95, 92, 0.3) !important;
        }

        .dropdown-menu-solid {
            background-color: var(--eclo-dropdown-bg) !important;
            border: 1px solid var(--bs-border-color-translucent);
            box-shadow: var(--eclo-dropdown-shadow) !important;
            border-radius: 8px;
            padding: 0.5rem;
            transform-origin: top;
            animation: dropdownFade 0.15s ease-out;
        }
        @keyframes dropdownFade {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .workspace-btn {
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            transition: var(--eclo-transition);
            border: 1px solid transparent;
        }
        .workspace-btn:hover {
            background-color: var(--eclo-hover-bg);
            border-color: var(--bs-border-color-translucent);
        }
        .workspace-avatar { 
            width: 32px; height: 32px; 
            border-radius: 8px; 
            background: var(--eclo-accent-color); color: white; 
            display: flex; align-items: center; justify-content: center; 
            font-weight: bold; font-size: 0.9rem; 
            flex-shrink: 0;
        }

        /* Nav links */
        .nav-link-custom { 
            display: flex; align-items: center; padding: 0.42rem 1rem; 
            text-decoration: none; color: var(--bs-body-color); 
            border-radius: 6px;
            font-size: 0.875rem;
        }
        .nav-link-custom i.icon-main { 
            width: 22px; color: var(--bs-secondary-color); font-size: 1rem; 
            text-align: left; transition: var(--eclo-transition); 
            flex-shrink: 0;
        }
        .nav-link-custom.active {
            background-color: var(--eclo-accent-soft);
            color: var(--eclo-accent-color) !important;
            font-weight: 600;
        }
        .nav-link-custom.active i.icon-main { color: var(--eclo-accent-color) !important; }
        .nav-link-custom:hover:not(.active) { background-color: var(--eclo-hover-bg); }

        /* Section header */
        .nav-section-header {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--bs-secondary-color);
            padding: 0.4rem 1rem 0.3rem;
            margin-top: 1rem;
        }

        /* Badge pill accent */
        .badge-accent {
            background-color: var(--eclo-accent-color);
            color: white;
            font-size: 0.65rem;
            border-radius: 999px;
            padding: 0.15rem 0.5rem;
        }
        .badge-rfm {
            background-color: #e8f4fd;
            color: #457b9d;
            font-size: 0.65rem;
            border-radius: 4px;
            padding: 0.1rem 0.4rem;
            font-weight: 600;
        }
        [data-bs-theme="dark"] .badge-rfm {
            background-color: rgba(69,123,157,0.2);
            color: #7ab8d4;
        }

        .btn-new-issue {
            border: 1px solid var(--bs-border-color-translucent);
            color: var(--bs-body-color);
            transition: var(--eclo-transition);
            background: transparent;
        }
        .btn-new-issue:hover {
            border-color: var(--eclo-accent-color);
            color: var(--eclo-accent-color);
            background-color: var(--eclo-accent-soft);
        }

        .avatar { width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: 600; border-radius: 4px; flex-shrink: 0; background-color: var(--bs-secondary-bg); color: var(--bs-body-color); border: 1px solid var(--bs-border-color-translucent);}
        .dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 12px; flex-shrink: 0; }

        .chart-area { height: 80px; display: flex; align-items: flex-end; justify-content: center; gap: 6px; margin-top: 1.5rem; border-bottom: 1px solid var(--bs-border-color-translucent); padding-bottom: 4px; position: relative; }
        .bar-group { display: flex; flex-direction: column; width: 12px; gap: 2px;}
        .bar { width: 100%; border-radius: 2px; }
        .legend-dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; margin-right: 4px; }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(136, 136, 136, 0.2); border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(136, 136, 136, 0.4); }

        .dropdown-toggle::after { display: none; }

        .sidebar-solid {
            background-color: var(--bs-body-bg) !important;
            border-right: 1px solid var(--bs-border-color-translucent) !important;
        }

        .animate-fade-up {
            animation: fadeUp 0.4s ease-out forwards;
            opacity: 0;
            transform: translateY(10px);
        }
        @keyframes fadeUp {
            to { opacity: 1; transform: translateY(0); }
        }
        .delay-1 { animation-delay: 0.05s; }
        .delay-2 { animation-delay: 0.1s; }
        .delay-3 { animation-delay: 0.15s; }
    </style>
</head>
<body class="bg-body text-body vh-100 d-flex flex-column flex-lg-row overflow-hidden" x-data="themeManager()" :data-bs-theme="actualTheme">

    <!-- MOBILE HEADER -->
    <div class="d-lg-none d-flex align-items-center justify-content-between p-3 border-bottom border-secondary-subtle w-100 sidebar-solid z-3">
        <div class="d-flex align-items-center gap-2">
            <div class="workspace-avatar" style="width:28px;height:28px;font-size:0.8rem;">S</div>
            <span class="fw-bold fs-6">Spa System</span>
        </div>
        <button class="btn btn-sm btn-outline-secondary border-0" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas">
            <i class="bi bi-list fs-3 text-body"></i>
        </button>
    </div>

    <!-- SIDEBAR -->
    <nav class="offcanvas-lg offcanvas-start sidebar-solid d-flex flex-column h-100 z-2" tabindex="-1" id="sidebarOffcanvas" style="width: var(--eclo-sidebar-width); flex-shrink: 0;">
        
        <!-- Workspace Switcher -->
        <div class="px-3 pt-4 pb-2">
            <div class="dropdown">
                <div class="workspace-btn d-flex align-items-center justify-content-between cursor-pointer dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="d-flex align-items-center gap-3">
                        <div class="workspace-avatar">G</div>
                        <div class="d-flex flex-column lh-1">
                            <span class="fw-semibold fs-7 text-body">Gun Shop</span>
                            <span class="fs-8 text-secondary mt-1">Premium Plan</span>
                        </div>
                    </div>
                    <i class="bi bi-chevron-expand text-secondary fs-7 opacity-75"></i>
                </div>
                
                <ul class="dropdown-menu dropdown-menu-solid w-100 mt-2">
                    <li><h6 class="dropdown-header fs-8 text-uppercase opacity-75 fw-semibold">Switch Workspace</h6></li>
                    <li>
                        <a class="dropdown-item fs-7 py-2 d-flex align-items-center rounded mx-1 px-2 mb-1" style="background-color: var(--eclo-hover-bg);" href="#">
                            <div class="workspace-avatar me-3" style="width: 20px; height: 20px; font-size: 0.6rem;">S</div> 
                            <span class="fw-semibold">Gun Shop</span>
                            <i class="bi bi-check2 ms-auto text-accent fs-6"></i>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider border-secondary-subtle opacity-50 my-2"></li>
                    <li>
                        <a class="dropdown-item fs-7 py-2 d-flex align-items-center rounded mx-1 px-2 text-secondary m-0" href="#">
                            <i class="bi bi-plus-circle me-3"></i> Thêm Workspace
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Scrollable Navigation -->
        <div class="flex-grow-1 overflow-y-auto pb-4 px-2">
            
            <!-- Dashboard -->
            <a href="#" class="nav-link-custom active mt-1">
                <i class="bi bi-grid-1x2 icon-main"></i>
                <span class="ms-2">Dashboard</span>
            </a>

            <!-- Thông báo -->
            <a href="#" class="nav-link-custom">
                <i class="bi bi-align-start icon-main"></i>
                <span class="ms-2 flex-grow-1">Thông báo</span>
                <span class="badge-accent">3</span>
            </a>

            <!-- CẤU HÌNH -->
            <div class="nav-section-header">Cấu hình</div>
            
            <a href="#" class="nav-link-custom">
                <i class="bi bi-geo-alt icon-main"></i>
                <span class="ms-2">Chi nhánh</span>
            </a>
            <a href="#" class="nav-link-custom">
                <i class="bi bi-person icon-main"></i>
                <span class="ms-2">Nhân viên</span>
            </a>
            <a href="#" class="nav-link-custom">
                <i class="bi bi-credit-card icon-main"></i>
                <span class="ms-2">Dịch vụ</span>
            </a>
            <a href="#" class="nav-link-custom">
                <i class="bi bi-activity icon-main"></i>
                <span class="ms-2">Tích hợp API</span>
            </a>

            <!-- DỮ LIỆU -->
            <div class="nav-section-header">Dữ liệu</div>

            <a href="#" class="nav-link-custom">
                <i class="bi bi-receipt icon-main"></i>
                <span class="ms-2">Hóa đơn</span>
            </a>
            <a href="#" class="nav-link-custom">
                <i class="bi bi-person-lines-fill icon-main"></i>
                <span class="ms-2">Khách hàng</span>
            </a>
            <a href="#" class="nav-link-custom">
                <i class="bi bi-clock icon-main"></i>
                <span class="ms-2">Lịch hẹn</span>
            </a>
            <a href="#" class="nav-link-custom">
                <i class="bi bi-graph-up icon-main"></i>
                <span class="ms-2">Chi phí</span>
            </a>

            <!-- BÁO CÁO -->
            <div class="nav-section-header">Báo cáo</div>

            <a href="#" class="nav-link-custom">
                <i class="bi bi-clock-history icon-main"></i>
                <span class="ms-2">Tài chính</span>
            </a>
            <a href="#" class="nav-link-custom">
                <i class="bi bi-bar-chart-line icon-main"></i>
                <span class="ms-2 flex-grow-1">Khách hàng</span>
                <span class="badge-rfm">RFM</span>
            </a>
            <a href="#" class="nav-link-custom">
                <i class="bi bi-file-earmark-text icon-main"></i>
                <span class="ms-2">Vận hành</span>
            </a>

            <!-- HỆ THỐNG -->
            <div class="nav-section-header">Hệ thống</div>

            <a href="#" class="nav-link-custom">
                <i class="bi bi-gear icon-main"></i>
                <span class="ms-2">Cài đặt</span>
            </a>
            <a href="#" class="nav-link-custom">
                <i class="bi bi-file-earmark-arrow-down icon-main"></i>
                <span class="ms-2">Xuất báo cáo</span>
            </a>
        </div>

        <!-- Sidebar Footer -->
        <div class="p-3 d-flex align-items-center justify-content-between border-top border-secondary-subtle mt-auto z-2 bg-body">
            
            <div class="dropup d-flex align-items-center flex-grow-1">
                <div class="d-flex align-items-center p-1 cursor-pointer dropdown-toggle w-100 rounded-2" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="avatar bg-secondary-subtle text-secondary rounded-circle me-2" style="width: 24px; height: 24px;">U</div>
                    <span class="fs-7 text-secondary text-truncate">Cài đặt tài khoản</span>
                </div>
                
                <ul class="dropdown-menu dropdown-menu-solid py-2 mb-2 w-100 ms-2">
                    <li><h6 class="dropdown-header fs-8 text-uppercase fw-semibold opacity-75">Tài khoản</h6></li>
                    <li><a class="dropdown-item fs-7 py-2 d-flex align-items-center rounded mx-1 px-3 m-0" href="#"><i class="bi bi-person me-3 text-secondary"></i> Hồ sơ</a></li>
                    <li><a class="dropdown-item fs-7 py-2 d-flex align-items-center rounded mx-1 px-3 m-0" href="#"><i class="bi bi-gear me-3 text-secondary"></i> Tùy chỉnh</a></li>
                    <li><hr class="dropdown-divider border-secondary-subtle opacity-50 my-1"></li>
                    <li><a class="dropdown-item fs-7 py-2 d-flex align-items-center rounded mx-1 px-3 text-danger m-0" href="#"><i class="bi bi-box-arrow-right me-3"></i> Đăng xuất</a></li>
                </ul>
            </div>

            <!-- THEME SELECTOR -->
            <div class="dropup d-flex align-items-center ms-2">
                <div class="p-2 rounded cursor-pointer dropdown-toggle d-flex align-items-center justify-content-center text-secondary border border-secondary-subtle" data-bs-toggle="dropdown" aria-expanded="false" title="Giao diện" style="width: 32px; height: 32px; background: var(--eclo-hover-bg);">
                    <i class="bi bi-circle-half fs-7"></i>
                </div>
                
                <ul class="dropdown-menu dropdown-menu-solid py-2 mb-2 dropdown-menu-end me-2">
                    <li><h6 class="dropdown-header fs-8 text-uppercase fw-semibold opacity-75">Giao diện</h6></li>
                    <li>
                        <a class="dropdown-item fs-7 py-2 d-flex align-items-center rounded mx-1 px-3 m-0 mb-1" href="#" 
                           @click.prevent="setTheme('light')" 
                           :class="{ 'bg-primary-subtle text-primary': theme === 'light', 'text-body': theme !== 'light' }">
                            <i class="bi bi-sun me-3"></i> Sáng
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item fs-7 py-2 d-flex align-items-center rounded mx-1 px-3 m-0 mb-1" href="#" 
                           @click.prevent="setTheme('dark')" 
                           :class="{ 'bg-primary-subtle text-primary': theme === 'dark', 'text-body': theme !== 'dark' }">
                            <i class="bi bi-moon-stars me-3"></i> Tối
                        </a>
                    </li>
                    <li><hr class="dropdown-divider border-secondary-subtle opacity-50 my-1"></li>
                    <li>
                        <a class="dropdown-item fs-7 py-2 d-flex align-items-center rounded mx-1 px-3 m-0" href="#" 
                           @click.prevent="setTheme('system')" 
                           :class="{ 'bg-primary-subtle text-primary': theme === 'system', 'text-body': theme !== 'system' }">
                            <i class="bi bi-display me-3"></i> Tự động
                        </a>
                    </li>
                </ul>
            </div>

        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <main class="flex-grow-1 overflow-y-auto p-4 p-md-5 w-100 position-relative z-1">
        
        <div class="d-flex justify-content-between align-items-center mb-4 animate-fade-up">
            <h1 class="fs-6 fw-bold m-0 text-body text-uppercase" style="letter-spacing: 1px;">Dashboard</h1>
            <div class="fs-8 text-secondary border border-secondary-subtle rounded-pill px-3 py-1 bg-body d-flex align-items-center">
                Vừa cập nhật
            </div>
        </div>

        <!-- STATS ROW -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3 animate-fade-up">
                <div class="card clean-card hover-card h-100 rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h2 class="display-6 fw-medium mb-0 lh-1 text-body">5</h2>
                            <i class="bi bi-boxes text-secondary fs-5 lh-1"></i>
                        </div>
                        <div class="text-body fw-medium fs-7 mb-1">Chi nhánh hoạt động</div>
                        <div class="text-secondary fs-8">0 tạm dừng, 5 đang hoạt động</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3 animate-fade-up delay-1">
                <div class="card clean-card hover-card h-100 rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h2 class="display-6 fw-medium mb-0 lh-1 text-body">12</h2>
                            <i class="bi bi-calendar-check text-secondary fs-5 lh-1"></i>
                        </div>
                        <div class="text-body fw-medium fs-7 mb-1">Lịch hẹn hôm nay</div>
                        <div class="text-secondary fs-8">3 đang chờ, 2 đã hủy</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3 animate-fade-up delay-2">
                <div class="card clean-card hover-card h-100 rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h2 class="display-6 fw-medium mb-0 lh-1 text-body">₫0</h2>
                            <i class="bi bi-currency-dollar text-secondary fs-5 lh-1"></i>
                        </div>
                        <div class="text-body fw-medium fs-7 mb-1">Doanh thu tháng</div>
                        <div class="text-secondary fs-8">Không giới hạn ngân sách</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3 animate-fade-up delay-3">
                <div class="card clean-card hover-card h-100 rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h2 class="display-6 fw-medium mb-0 lh-1 text-body">0</h2>
                            <i class="bi bi-shield-check text-secondary fs-5 lh-1"></i>
                        </div>
                        <div class="text-body fw-medium fs-7 mb-1">Chờ phê duyệt</div>
                        <div class="text-secondary fs-8">Không có yêu cầu mới</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CHARTS & LISTS ROW -->
        <div class="row g-4 pb-4">
            <div class="col-xl-6 d-flex flex-column gap-3">
                <div class="fs-8 fw-semibold text-secondary text-uppercase tracking-wide ps-1 animate-fade-up">Phân tích</div>
                
                <div class="row g-3 flex-grow-1">
                    <div class="col-md-6 animate-fade-up delay-1">
                        <div class="card clean-card hover-card h-100 rounded-3">
                            <div class="card-body p-4 d-flex flex-column">
                                <div class="text-body fw-medium fs-7 mb-1">Hoạt động dịch vụ</div>
                                <div class="text-secondary fs-8 mb-2">14 ngày qua</div>
                                <div class="chart-area flex-grow-1">
                                    <div class="bar-group">
                                        <div class="bar" style="height: 65%; background-color: var(--eclo-accent-color);"></div>
                                        <div class="bar" style="height: 35%; background-color: var(--eclo-success-color);"></div>
                                    </div>
                                    <div class="bar-group">
                                        <div class="bar" style="height: 20%; background-color: var(--eclo-warning-color);"></div>
                                        <div class="bar bg-secondary" style="height: 40%;"></div>
                                    </div>
                                    <div class="bar-group">
                                        <div class="bar" style="height: 80%; background-color: var(--eclo-accent-color);"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 animate-fade-up delay-2">
                        <div class="card clean-card hover-card h-100 rounded-3">
                            <div class="card-body p-4 d-flex flex-column">
                                <div class="text-body fw-medium fs-7 mb-1">Trạng thái lịch hẹn</div>
                                <div class="text-secondary fs-8 mb-2">14 ngày qua</div>
                                <div class="chart-area flex-grow-1">
                                    <div class="bar-group">
                                        <div class="bar" style="height: 15%; background-color: var(--eclo-success-color);"></div>
                                        <div class="bar" style="height: 20%; background-color: var(--eclo-accent-color);"></div>
                                        <div class="bar" style="height: 55%; background-color: var(--eclo-blue-color);"></div>
                                    </div>
                                </div>
                                <div class="d-flex gap-3 mt-4 pt-2 fs-8 text-secondary flex-wrap justify-content-center">
                                    <div class="d-flex align-items-center"><span class="legend-dot" style="background-color: var(--eclo-blue-color);"></span> Chờ</div>
                                    <div class="d-flex align-items-center"><span class="legend-dot" style="background-color: var(--eclo-success-color);"></span> Xong</div>
                                    <div class="d-flex align-items-center"><span class="legend-dot" style="background-color: var(--eclo-accent-color);"></span> Hủy</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RECENT TASKS COLUMN -->
            <div class="col-xl-6 animate-fade-up delay-3 d-flex flex-column gap-3">
                <div class="fs-8 fw-semibold text-secondary text-uppercase tracking-wide ps-1">Lịch hẹn gần đây</div>
                
                <div class="card clean-card rounded-3 overflow-hidden flex-grow-1 border-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item bg-transparent border-secondary-subtle d-flex align-items-center py-3 px-4 fs-7 text-body m-0 rounded-0" style="transition: var(--eclo-transition);">
                            <i class="bi bi-dash text-secondary me-3"></i>
                            <i class="bi bi-circle-fill me-3" style="color: var(--eclo-success-color); font-size: 0.5rem;"></i>
                            <span class="text-body fw-medium me-3">LH-31</span>
                            <div class="avatar border-0 me-2 text-secondary">AN</div>
                            <span class="text-secondary me-3">Nhân viên A</span>
                            <span class="text-truncate flex-grow-1">Massage thư giãn</span>
                            <span class="text-secondary fs-8 ms-3 text-nowrap">6 ngày trước</span>
                        </div>
                        
                        <div class="list-group-item bg-transparent border-secondary-subtle d-flex align-items-center py-3 px-4 fs-7 text-body m-0 rounded-0" style="transition: var(--eclo-transition);">
                            <i class="bi bi-exclamation-triangle text-accent me-3"></i>
                            <i class="bi bi-circle-fill text-primary me-3" style="font-size: 0.5rem;"></i>
                            <span class="text-body fw-medium me-3">LH-25</span>
                            <div class="avatar border-0 me-2 text-secondary">BN</div>
                            <span class="text-secondary me-3">Nhân viên B</span>
                            <span class="text-truncate flex-grow-1">Chăm sóc da mặt</span>
                            <span class="text-secondary fs-8 ms-3 text-nowrap">6 ngày trước</span>
                        </div>
                        
                        <div class="list-group-item bg-transparent border-secondary-subtle d-flex align-items-center py-3 px-4 fs-7 text-body m-0 rounded-0" style="transition: var(--eclo-transition);">
                            <i class="bi bi-arrow-up-short text-warning me-3 fs-5"></i>
                            <i class="bi bi-circle-fill text-primary me-3" style="font-size: 0.5rem;"></i>
                            <span class="text-body fw-medium me-3">LH-26</span>
                            <div class="avatar border-0 me-2 text-secondary">CN</div>
                            <span class="text-secondary me-3">Nhân viên C</span>
                            <span class="text-truncate flex-grow-1">Gội đầu dưỡng sinh</span>
                            <span class="text-secondary fs-8 ms-3 text-nowrap">6 ngày trước</span>
                        </div>

                        <div class="list-group-item bg-transparent border-transparent d-flex align-items-center py-3 px-4 fs-7 text-body m-0 rounded-0 border-bottom-0" style="transition: var(--eclo-transition);">
                            <i class="bi bi-arrow-down-short text-primary me-3 fs-5"></i>
                            <i class="bi bi-circle-fill text-secondary me-3" style="font-size: 0.5rem;"></i>
                            <span class="text-body fw-medium me-3">LH-27</span>
                            <div class="avatar border-0 me-2 text-secondary">DN</div>
                            <span class="text-secondary me-3">Nhân viên D</span>
                            <span class="text-truncate flex-grow-1">Tắm trắng toàn thân</span>
                            <span class="text-secondary fs-8 ms-3 text-nowrap">6 ngày trước</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('themeManager', () => ({
            theme: localStorage.getItem('eclo-theme-pref') || 'system',
            actualTheme: document.documentElement.getAttribute('data-bs-theme') || 'dark',
            
            init() {
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
                    if (this.theme === 'system') this.updateActualTheme();
                });
                this.$watch('theme', () => this.updateActualTheme());
            },
            
            updateActualTheme() {
                localStorage.setItem('eclo-theme-pref', this.theme);
                this.actualTheme = this.theme === 'system'
                    ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
                    : this.theme;
                document.documentElement.setAttribute('data-bs-theme', this.actualTheme);
            },
            
            setTheme(newTheme) { this.theme = newTheme; }
        }));
    });
</script>

</body>
</html>