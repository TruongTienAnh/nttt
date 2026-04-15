<?php $this->extend('layouts/app') ?>
<?php $this->section('content') ?>
<div class="container-fluid py-4 animate-fade-up">
    <div class="mb-4">
        <h1 class="h3 fw-bold">Hiệu quả rót vốn (ROI & Payback)</h1>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="clean-card p-4 border-start border-5 border-info">
                <div class="text-secondary small fw-bold">TỔNG VỐN ĐẦU TƯ</div>
                <div class="h3 fw-bold"><?= number_format($investmentCapital) ?> ₫</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="clean-card p-4 border-start border-5 border-success">
                <div class="text-secondary small fw-bold">LỢI NHUẬN TÍCH LŨY</div>
                <div class="h3 fw-bold"><?= number_format($accumulatedProfit) ?> ₫</div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="clean-card p-4 text-center h-100">
                <h6 class="text-secondary small fw-bold">CHỈ SỐ ROI</h6>
                <div class="display-5 fw-bold text-primary"><?= round($roi, 1) ?>%</div>
                <p class="text-secondary small">Tỷ suất lợi nhuận trên vốn</p>
            </div>
        </div>
        <div class="col-md-8">
            <div class="clean-card p-4 h-100">
                <h6 class="fw-bold mb-3">Dự kiến thời gian hoàn vốn</h6>
                <?php if($paybackMonths > 0): ?>
                    <div class="d-flex align-items-center gap-3">
                        <div class="h1 mb-0 fw-bold text-accent"><?= round($paybackMonths, 1) ?></div>
                        <div class="text-secondary fs-5">Tháng còn lại</div>
                    </div>
                    <div class="progress mt-3" style="height: 10px;">
                        <div class="progress-bar bg-success" style="width: <?= min(100, ($accumulatedProfit/$investmentCapital)*100) ?>%"></div>
                    </div>
                    <small class="text-secondary d-block mt-2">Đã thu hồi được <?= round(($accumulatedProfit/$investmentCapital)*100, 1) ?>% vốn ban đầu</small>
                <?php else: ?>
                    <div class="alert alert-danger mb-0">Hệ thống đang lỗ hoặc chưa có lợi nhuận để tính toán hoàn vốn.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection() ?>