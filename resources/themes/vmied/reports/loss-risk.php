<?php $this->extend('layouts/app') ?>
<?php $this->section('content') ?>
<div class="container-fluid py-4 animate-fade-up">
    <div class="mb-4">
        <h1 class="h3 fw-bold mb-1">Phân tích Thất thoát (Loss Prevention)</h1>
        <p class="text-secondary">Theo dõi Phễu chuyển đổi: Lượt khách ra vào (Footfall) vs Số lượng hóa đơn thực tế trong ngày.</p>
    </div>

    <div class="row g-3">
        <div class="col-md-5">
            <div class="clean-card p-4 border-0 shadow-sm h-100 text-center d-flex flex-column justify-content-center">
                <div class="mb-4">
                    <div class="text-secondary small fw-bold mb-1">LƯỢT KHÁCH (CAMERA ĐẾM)</div>
                    <div class="display-4 fw-bold text-dark"><?= number_format($footfallTraffic) ?></div>
                </div>
                <div class="text-primary mb-3">
                    <i class="bi bi-arrow-down fs-3"></i>
                </div>
                <div class="mb-4">
                    <div class="text-secondary small fw-bold mb-1">SỐ HÓA ĐƠN XUẤT RA</div>
                    <div class="display-4 fw-bold text-success"><?= number_format($totalBills) ?></div>
                </div>
                <hr>
                <div>
                    <div class="text-secondary small fw-bold mb-1">TỶ LỆ CHUYỂN ĐỔI (CONVERSION)</div>
                    <div class="h2 fw-bold <?= $isAlert ? 'text-danger' : 'text-primary' ?>"><?= round($conversionRate, 1) ?>%</div>
                    <small class="text-secondary">Chuẩn mục tiêu: <?= $targetConversion ?>%</small>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="clean-card p-4 border-0 shadow-sm h-100 <?= $isAlert ? 'bg-danger bg-opacity-10 border border-danger border-opacity-25' : '' ?>">
                <h5 class="fw-bold mb-4"><i class="bi bi-cash-coin me-2"></i>Phân tích thiệt hại tài chính</h5>
                
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="p-3 bg-white rounded shadow-sm">
                            <div class="text-secondary small fw-bold">GIÁ TRỊ ĐƠN TRUNG BÌNH (AOV)</div>
                            <div class="h4 fw-bold text-dark mt-2"><?= number_format($aov) ?> ₫</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-white rounded shadow-sm border-start border-danger border-4">
                            <div class="text-danger small fw-bold">ƯỚC TÍNH DOANH THU THẤT THOÁT</div>
                            <div class="h4 fw-bold text-danger mt-2"><?= number_format($estimatedRevenueLost) ?> ₫</div>
                        </div>
                    </div>
                </div>

                <?php if($isAlert): ?>
                    <div class="alert alert-danger border-0 bg-white shadow-sm d-flex gap-3 mb-0">
                        <i class="bi bi-shield-exclamation fs-1 text-danger"></i>
                        <div>
                            <h6 class="fw-bold text-danger mb-1">BÁO ĐỘNG ĐỎ VẬN HÀNH!</h6>
                            <p class="mb-1 small">Tỷ lệ khách mua hàng đang tụt sâu dưới mức tiêu chuẩn. Việc này dẫn đến thất thoát dòng tiền cực kỳ lớn.</p>
                            <hr class="my-2">
                            <div class="small fw-bold text-dark">Hành động cần làm ngay:</div>
                            <ul class="mb-0 small text-dark">
                                <li>Kiểm tra camera xem nhân viên có thu tiền nhưng không xuất bill không (Gian lận).</li>
                                <li>Review lại kịch bản tư vấn chốt sale của nhân viên (Kỹ năng yếu).</li>
                                <li>Kiểm tra xem sản phẩm có đang bị hết hàng (Out of stock) không.</li>
                            </ul>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success border-0 bg-white shadow-sm d-flex align-items-center gap-3 mb-0">
                        <i class="bi bi-check-circle-fill fs-2 text-success"></i>
                        <div>
                            <h6 class="fw-bold mb-0">Vận hành xuất sắc!</h6>
                            <p class="mb-0 small text-secondary">Tỷ lệ chuyển đổi đang đạt chuẩn. Ít có dấu hiệu thất thoát hoặc gian lận tại điểm bán.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection() ?>