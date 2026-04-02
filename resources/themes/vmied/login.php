<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Nam Toàn Thịnh</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="/css/app.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid p-0 h-100 bg-white">
        <div class="row g-0 h-100 justify-content-center align-items-center" x-data="{ view: 'login' }" x-cloak>
            <div class="col-lg-6 h-100  d-flex align-items-center justify-content-center position-relative">
                <!-- Mobile Logo -->
                <div class="d-lg-none position-absolute top-0 start-0 p-4 d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded bg-primary text-white fw-bold" style="width: 32px; height: 32px;">V</div>
                    <span class="fw-bold text-dark">AI Vmied</span>
                </div>

                <div class="w-100 p-4" style="max-width: 480px;">
                    
                    <!-- 1. LOGIN FORM -->
                    <div x-show="view === 'login'" 
                         x-transition:enter="transition ease-out duration-300" 
                         x-transition:enter-start="opacity-0 translate-y-3" 
                         x-transition:enter-end="opacity-100 translate-y-0">
                        
                        <div class="text-center mb-5">
                            <h2 class="fw-bold text-dark mb-2">Chào mừng trở lại!</h2>
                            <p class="text-secondary small">Vui lòng đăng nhập để tiếp tục.</p>
                        </div>
                        <div x-data="Form()">
                            <form 
                                hx-post="/login" 
                                hx-swap="none"
                                @htmx:before-request="startRequest()"
                                @htmx:after-request="handleResponse($event)"
                                class="d-grid gap-3"
                            >
                                <!-- Error Alert -->
                                <div  x-show="errorMessage" style="display: none;">
                                    <div class="alert alert-danger d-flex align-items-center small py-2 rounded-3" role="alert">
                                        <i data-lucide="alert-circle" style="width: 16px; margin-right: 8px;"></i>
                                        <div><span class="fw-bold">Lỗi:</span> <span x-text="errorMessage"></span></div>
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label small fw-bold text-secondary ps-1">Email</label>
                                    <div class="input-group-custom">
                                        <input type="text" name="email" class="form-control rounded-4 py-3 bg-light" placeholder="Email" required>
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label small fw-bold text-secondary ps-1">Mật khẩu</label>
                                    <div class="input-group-custom">
                                        <input type="password" name="password" class="form-control rounded-4 py-3 bg-light" placeholder="••••••••" required>
                                    </div>
                                </div>

                                <button type="submit" 
                                        :disabled="isLoading"
                                        class="btn btn-danger w-100 py-3 rounded-4 fw-bold text-white shadow mt-2"
                                        style="transition: all 0.3s;">
                                    
                                    <div x-show="isLoading" class="spinner-border spinner-border-sm text-light" role="status"></div>
                                    <span x-text="isLoading ? 'Đang xử lý...' : 'Đăng nhập'"></span>
                                    <i x-show="!isLoading" data-lucide="arrow-right" style="width: 16px;"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                </div>

                <!-- Footer Copyright -->
                <div class="position-absolute bottom-0 w-100 text-center pb-3 text-secondary" style="font-size: 0.75rem;">
                    &copy; 2026 Nam Toàn Thịnh. All rights reserved.
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/htmx.org@1.9.10"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="/js/app.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>