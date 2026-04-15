<?php $this->extend('layouts/app') ?>
<?php $this->section('content') ?>
<div class="container-fluid py-4 animate-fade-up">
    <div class="clean-card p-4 mb-4 <?= $isAlert ? 'border-danger' : '' ?>">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h4 class="fw-bold mb-0">Cảnh báo Biến động Chi phí</h4>
            <?php if($isAlert): ?>
                <span class="badge bg-danger p-2 px-3 animate-pulse"><i class="bi bi-exclamation-triangle"></i> NGUY CƠ CAO</span>
            <?php else: ?>
                <span class="badge bg-success p-2 px-3">AN TOÀN</span>
            <?php endif; ?>
        </div>

        <div class="row g-4">
            <div class="col-md-6 border-end">
                <div class="text-secondary small mb-1">CHI PHÍ THÁNG TRƯỚC</div>
                <div class="h4 fw-bold"><?= number_format($lastMonthCost) ?>đ</div>
            </div>
            <div class="col-md-6">
                <div class="text-secondary small mb-1">CHI PHÍ THÁNG NÀY</div>
                <div class="h4 fw-bold <?= $isAlert ? 'text-danger' : '' ?>"><?= number_format($thisMonthCost) ?>đ</div>
            </div>
        </div>

        <div class="mt-4 p-3 rounded-3 <?= $isAlert ? 'bg-danger bg-opacity-10 text-danger' : 'bg-light' ?>">
            Tỷ lệ biến động: <b><?= ($growthRate > 0 ? '+' : '') . round($growthRate, 1) ?>%</b>
            <?php if($isAlert): ?>
                <p class="mb-0 small mt-1">Lưu ý: Chi phí tăng vượt ngưỡng 30%. Cần kiểm tra lại các hóa đơn tiền điện hoặc các khoản chi bất thường ngay!</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $this->endSection() ?>