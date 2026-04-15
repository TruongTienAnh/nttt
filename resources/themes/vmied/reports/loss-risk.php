<?php $this->extend('layouts/app') ?>
<?php $this->section('content') ?>
<div class="container-fluid py-4 animate-fade-up">
    <div class="clean-card p-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="fw-bold"><i class="bi bi-eye text-info"></i> Cảnh báo Thất thoát (Loss Prevention)</h4>
                <p class="text-secondary mb-0">Phân tích chênh lệch giữa lượng khách ra vào và số lượng hóa đơn thực tế.</p>
            </div>
            <div class="col-md-4 text-end">
                <div class="h1 fw-bold mb-0 <?= $isAlert ? 'text-danger' : 'text-info' ?>"><?= round($conversionRate, 1) ?>%</div>
                <div class="small fw-bold text-secondary text-uppercase">Tỷ lệ chuyển đổi</div>
            </div>
        </div>

        <hr class="my-4">

        <div class="row g-4 text-center">
            <div class="col-6">
                <div class="text-secondary small mb-1">LƯỢT KHÁCH (CAMERA AI)</div>
                <div class="h3 fw-bold text-dark"><?= number_format($footfallTraffic) ?></div>
            </div>
            <div class="col-6">
                <div class="text-secondary small mb-1">SỐ BILL (HÔM NAY)</div>
                <div class="h3 fw-bold text-dark"><?= number_format($totalBills) ?></div>
            </div>
        </div>

        <?php if($isAlert): ?>
            <div class="alert alert-danger mt-4 border-0 shadow-sm d-flex align-items-center gap-3">
                <i class="bi bi-shield-exclamation fs-2"></i>
                <div>
                    <h6 class="fw-bold mb-1">PHÁT HIỆN BẤT THƯỜNG!</h6>
                    <p class="mb-0 small">Lượng khách vào rất đông nhưng số bill quá ít. Có thể nhân viên quên thu tiền hoặc có sự gian lận trong khâu xuất hóa đơn.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $this->endSection() ?>