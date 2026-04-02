<?php $this->extend('layouts/app') ?>

<?php $this->section('content') ?>
<div class="container py-5">
        
    <!-- HEADER -->
    <div class="text-center py-5">
        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-primary-subtle text-primary border border-primary-subtle mb-3">
            <i data-lucide="sparkles" width="14"></i>
            <span class="small fw-bold text-uppercase ls-1">Chương trình đối tác</span>
        </div>
        <h1 class="display-4 fw-bolder text-dark mb-3 ls-tight">
            Giới thiệu bạn bè,<br>
            <span class="text-primary">Nhận VMIED vô tận</span>
        </h1>
        <p class="text-secondary fs-5 col-lg-7 mx-auto lh-base">
            Nhận ngay <strong class="text-dark">20% hoa hồng trọn đời</strong> mỗi khi người bạn giới thiệu thực hiện giao dịch nạp tiền thành công.
        </p>
    </div>

    <!-- INFO CARDS (Grid 2 Columns) -->
    <div class="row g-4 mb-5">
        <div class="col-lg-6">
            <div class="card h-100 border-0 shadow-lg rounded-5 overflow-hidden bg-white">
                <div class="card-body p-5">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-4 text-primary">
                            <i data-lucide="gift" width="28" height="28"></i>
                        </div>
                        <h3 class="fw-bold fs-4 mb-0">Quyền lợi đặc biệt</h3>
                    </div>
                    
                    <div class="vstack gap-4">
                        <div class="d-flex gap-3">
                            <i data-lucide="check-circle-2" class="text-success flex-shrink-0 mt-1"></i>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">Hoa hồng 20% trọn đời</h6>
                                <p class="text-secondary small mb-0">Nhận 20% giá trị mọi giao dịch nạp tiền từ người được giới thiệu, mãi mãi.</p>
                            </div>
                        </div>
                        <div class="d-flex gap-3">
                            <i data-lucide="check-circle-2" class="text-success flex-shrink-0 mt-1"></i>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">Thu nhập thụ động</h6>
                                <p class="text-secondary small mb-0">Chỉ cần chia sẻ link một lần, hệ thống sẽ tự động ghi nhận.</p>
                            </div>
                        </div>
                        <div class="d-flex gap-3">
                            <i data-lucide="check-circle-2" class="text-success flex-shrink-0 mt-1"></i>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">Thanh toán linh hoạt</h6>
                                <p class="text-secondary small mb-0">Rút tiền về tài khoản ngân hàng nhanh chóng trong 24h.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100 border-0 shadow-lg rounded-5 overflow-hidden bg-white">
                <div class="card-body p-5">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="bg-warning bg-opacity-10 p-3 rounded-4 text-warning">
                            <i data-lucide="shield-alert" width="28" height="28"></i>
                        </div>
                        <h3 class="fw-bold fs-4 mb-0">Lưu ý quan trọng</h3>
                    </div>
                    
                    <div class="vstack gap-4">
                        <div class="d-flex gap-3">
                            <i data-lucide="info" class="text-secondary flex-shrink-0 mt-1"></i>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">Hạn mức rút tối thiểu</h6>
                                <p class="text-secondary small mb-0">Số dư hoa hồng phải đạt ít nhất <strong>100.000 V</strong> để tạo lệnh rút.</p>
                            </div>
                        </div>
                        <div class="d-flex gap-3">
                            <i data-lucide="info" class="text-secondary flex-shrink-0 mt-1"></i>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">Thời gian xử lý</h6>
                                <p class="text-secondary small mb-0">Các yêu cầu rút tiền được xử lý từ Thứ 2 đến Thứ 6.</p>
                            </div>
                        </div>
                        <div class="d-flex gap-3">
                            <i data-lucide="info" class="text-secondary flex-shrink-0 mt-1"></i>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">Quy định chống gian lận</h6>
                                <p class="text-secondary small mb-0">Không được tự giới thiệu chính mình. Tài khoản vi phạm sẽ bị khóa.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- STATS (3 Columns) -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white hover-scale">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-secondary small fw-bold text-uppercase mb-2">Khả dụng</p>
                        <h3 class="fw-bolder text-dark mb-0 display-6">250k <span class="fs-6 text-muted fw-normal">V</span></h3>
                    </div>
                    <div class="bg-success-subtle p-3 rounded-circle text-success">
                        <i data-lucide="wallet" width="24"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white hover-scale">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-secondary small fw-bold text-uppercase mb-2">Đã mời</p>
                        <h3 class="fw-bolder text-dark mb-0 display-6">12 <span class="fs-6 text-muted fw-normal">Bạn</span></h3>
                    </div>
                    <div class="bg-primary-subtle p-3 rounded-circle text-primary">
                        <i data-lucide="users" width="24"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white hover-scale">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-secondary small fw-bold text-uppercase mb-2">Tổng thu nhập</p>
                        <h3 class="fw-bolder text-dark mb-0 display-6">1.25M <span class="fs-6 text-muted fw-normal">V</span></h3>
                    </div>
                    <div class="bg-info-subtle p-3 rounded-circle text-info">
                        <i data-lucide="trending-up" width="24"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ACTION SECTION (Link & Payout) -->
    <div class="row g-4 mb-5">
        
        <!-- Share Link Card -->
        <div class="col-lg-6">
            <div class="card border-0 rounded-5 p-5 text-white bg-dark bg-gradient h-100 shadow-lg position-relative overflow-hidden">
                <!-- Background Decor using Bootstrap utilities -->
                <div class="position-absolute top-0 end-0 p-5 bg-dark opacity-10 rounded-circle translate-middle" style="width: 300px; height: 300px;"></div>

                <div class="position-relative z-1 d-flex flex-column h-100">
                    <div class="mb-4">
                        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-white bg-opacity-10 border border-white border-opacity-10 mb-3">
                            <i data-lucide="link" width="14"></i>
                            <span class="small fw-bold text-uppercase">Link giới thiệu</span>
                        </div>
                        <h3 class="fw-bold mb-2">Chia sẻ & Nhận quà</h3>
                        <p class="text-white-50">Gửi liên kết này cho bạn bè để bắt đầu nhận hoa hồng.</p>
                    </div>

                    <div class="bg-white bg-opacity-10 p-2 rounded-4 border border-white border-opacity-10 mb-4">
                        <div class="input-group">
                            <input type="text" class="form-control bg-transparent border-0 text-white shadow-none font-monospace" id="ref-link" value="https://ai.vmied.com/login?ref=<?=$user->affiliate?>" readonly>
                            <button class="btn btn-primary fw-bold rounded-3 px-4" onclick="navigator.clipboard.writeText(document.getElementById('ref-link').value); alert('Đã sao chép!');">
                                Copy
                            </button>
                        </div>
                    </div>

                    <div class="row g-3 mt-auto">
                        <div class="col-6">
                            <button class="btn btn-outline-light w-100 fw-bold py-2 rounded-4 d-flex align-items-center justify-content-center gap-2">
                                <i data-lucide="facebook" width="18"></i> Facebook
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-outline-light w-100 fw-bold py-2 rounded-4 d-flex align-items-center justify-content-center gap-2">
                                <i data-lucide="send" width="18"></i> Zalo
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Request Payout Card -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-lg rounded-5 p-5 bg-white h-100">
                <div class="mb-4">
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-success-subtle text-success border border-success-subtle mb-3">
                        <i data-lucide="landmark" width="14"></i>
                        <span class="small fw-bold text-uppercase">Rút tiền</span>
                    </div>
                    <h3 class="fw-bold mb-2 text-dark">Yêu cầu thanh toán</h3>
                    <p class="text-secondary">Chuyển đổi VMIED thành tiền mặt về ngân hàng.</p>
                </div>

                <form onsubmit="event.preventDefault(); alert('Yêu cầu rút tiền đã được gửi!');" class="d-flex flex-column h-100">
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold text-uppercase">Số tiền (VMIED)</label>
                        <div class="input-group input-group-lg">
                            <input type="number" class="form-control bg-light border-0 rounded-start-4 fw-bold fs-5 text-dark" placeholder="0">
                            <span class="input-group-text bg-light border-0 rounded-end-4 text-secondary fw-bold pe-4">V</span>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label text-secondary small fw-bold text-uppercase">Ngân hàng</label>
                        <select class="form-select form-select-lg bg-light border-0 rounded-4 text-dark fs-6 py-3">
                            <option>Vietcombank - **** 456</option>
                            <option>MB Bank - **** 999</option>
                            <option>Thêm tài khoản mới...</option>
                        </select>
                    </div>

                    <button class="btn btn-dark w-100 py-3 rounded-4 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2 mt-auto hover-scale">
                        Gửi yêu cầu <i data-lucide="arrow-right" width="20"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- HISTORY SECTION -->
    <div class="card border-0 shadow-sm rounded-5 overflow-hidden bg-white mb-5">
        <div class="card-header bg-white border-bottom border-light-subtle p-4">
            <ul class="nav nav-pills card-header-pills bg-light rounded-pill p-1 d-inline-flex" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill active fw-bold px-4" id="pills-referrals-tab" data-bs-toggle="pill" data-bs-target="#pills-referrals" type="button" role="tab">Giới thiệu</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill fw-bold px-4" id="pills-payouts-tab" data-bs-toggle="pill" data-bs-target="#pills-payouts" type="button" role="tab">Rút tiền</button>
                </li>
            </ul>
        </div>
        
        <div class="card-body p-0">
            <div class="tab-content" id="pills-tabContent">
                <!-- Referrals Table -->
                <div class="tab-pane fade show active" id="pills-referrals" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-secondary small fw-bold text-uppercase">
                                <tr>
                                    <th class="px-4 py-3 border-0">Thành viên</th>
                                    <th class="px-4 py-3 border-0">Ngày</th>
                                    <th class="px-4 py-3 border-0">Gói nạp</th>
                                    <th class="px-4 py-3 border-0">Hoa hồng</th>
                                    <th class="px-4 py-3 border-0 text-end">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-primary-subtle text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                <i data-lucide="user" width="16"></i>
                                            </div>
                                            <span class="fw-medium">nguyen***@gmail.com</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-secondary small">28/12/2024</td>
                                    <td class="px-4 py-3 text-secondary">100.000 ₫</td>
                                    <td class="px-4 py-3 fw-bold text-success">+ 20.000 V</td>
                                    <td class="px-4 py-3 text-end"><span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">Hoàn tất</span></td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-primary-subtle text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                <i data-lucide="user" width="16"></i>
                                            </div>
                                            <span class="fw-medium">le.van***@hcmut.edu.vn</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-secondary small">25/12/2024</td>
                                    <td class="px-4 py-3 text-secondary">500.000 ₫</td>
                                    <td class="px-4 py-3 fw-bold text-success">+ 100.000 V</td>
                                    <td class="px-4 py-3 text-end"><span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">Hoàn tất</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Payouts Table -->
                <div class="tab-pane fade" id="pills-payouts" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-secondary small fw-bold text-uppercase">
                                <tr>
                                    <th class="px-4 py-3 border-0">Mã GD</th>
                                    <th class="px-4 py-3 border-0">Ngày tạo</th>
                                    <th class="px-4 py-3 border-0">Số tiền</th>
                                    <th class="px-4 py-3 border-0">Ngân hàng</th>
                                    <th class="px-4 py-3 border-0 text-end">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="px-4 py-3 font-monospace text-secondary">PO-8821</td>
                                    <td class="px-4 py-3 text-secondary small">10/12/2024</td>
                                    <td class="px-4 py-3 fw-bold text-dark">500.000 V</td>
                                    <td class="px-4 py-3 text-secondary">Vietcombank</td>
                                    <td class="px-4 py-3 text-end"><span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">Thành công</span></td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3 font-monospace text-secondary">PO-9902</td>
                                    <td class="px-4 py-3 text-secondary small">28/12/2024</td>
                                    <td class="px-4 py-3 fw-bold text-dark">200.000 V</td>
                                    <td class="px-4 py-3 text-secondary">MB Bank</td>
                                    <td class="px-4 py-3 text-end"><span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3">Đang xử lý</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<?php $this->endSection() ?>