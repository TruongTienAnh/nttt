<?php $this->extend('layouts/app') ?>
<?php $this->section('content') ?>
<div class="container py-5 mt-5">
    <div class="clean-card p-5 text-center <?= $isAlert ? 'border-danger bg-danger bg-opacity-10' : 'border-success' ?>">
        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-4" 
             style="width:100px;height:100px; background: <?= $isAlert ? '#f25f5c' : '#2a9d8f' ?>; color: white;">
            <i class="bi <?= $isAlert ? 'bi- megaphone-fill' : 'bi-shield-check' ?> fs-1"></i>
        </div>
        
        <h1 class="display-4 fw-bold"><?= $isAlert ? 'BÁO ĐỘNG ĐỎ' : 'VẬN HÀNH ỔN ĐỊNH' ?></h1>
        <p class="fs-5 text-secondary">Doanh thu 7 ngày qua: <b><?= number_format($revenue7Days) ?> ₫</b></p>
        
        <div class="mt-4">
            <div class="small text-uppercase fw-bold text-secondary">Ngưỡng an toàn tối thiểu</div>
            <div class="h4"><?= number_format($criticalThreshold) ?> ₫</div>
        </div>

        <?php if($isAlert): ?>
            <div class="mt-5 p-4 bg-white rounded-4 shadow-sm text-start border-start border-5 border-danger mx-auto" style="max-width: 600px;">
                <h5 class="fw-bold text-danger"><i class="bi bi-lightning-fill"></i> Hành động khẩn cấp:</h5>
                <ul class="mb-0">
                    <li>Kiểm tra lại chiến dịch Marketing (Ads) ngay lập tức.</li>
                    <li>Tổ chức chương trình khuyến mãi chớp nhoáng (Flash Sale).</li>
                    <li>Rà soát lại thái độ phục vụ của nhân viên tại chi nhánh.</li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $this->endSection() ?>