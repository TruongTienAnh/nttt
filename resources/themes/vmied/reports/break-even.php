<?php $this->extend('layouts/app') ?>
<?php $this->section('content') ?>
<div class="container-fluid py-4 animate-fade-up">
    <div class="clean-card p-5 text-center">
        <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width:80px;height:80px;">
            <i class="bi bi-water fs-1"></i>
        </div>
        <h2 class="fw-bold">Phân tích Điểm hòa vốn</h2>
        <p class="text-secondary mx-auto" style="max-width: 500px;">
            Dựa trên định phí (Mặt bằng, Lương) là <b><?= number_format($fixedCost) ?>đ</b>, bạn cần đạt doanh thu mục tiêu dưới đây để bắt đầu có lãi.
        </p>

        <div class="my-5">
            <h4 class="text-secondary small fw-bold mb-2">TIẾN ĐỘ HÒA VỐN THÁNG NÀY</h4>
            <div class="h1 fw-bolder mb-3"><?= number_format($revenue) ?> / <?= number_format($breakEvenPoint) ?> ₫</div>
            <div class="progress rounded-pill shadow-sm mx-auto" style="height: 30px; max-width: 600px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated <?= $progress >= 100 ? 'bg-success' : 'bg-warning' ?>" 
                     role="progressbar" style="width: <?= $progress ?>%">
                     <?= round($progress) ?>%
                </div>
            </div>
        </div>

        <?php if($progress >= 100): ?>
            <div class="alert alert-success d-inline-block px-5 rounded-4">
                <i class="bi bi-check-circle-fill me-2"></i> Chúc mừng! Bạn đã vượt qua điểm hòa vốn và bắt đầu sinh lời.
            </div>
        <?php else: ?>
            <div class="alert alert-light border d-inline-block px-5 rounded-4">
                <i class="bi bi-info-circle me-2"></i> Bạn cần thêm <b><?= number_format($breakEvenPoint - $revenue) ?>đ</b> nữa để hòa vốn.
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $this->endSection() ?>