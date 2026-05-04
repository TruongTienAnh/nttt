<?php $this->extend('layouts/app') ?>
<?php $this->section('content') ?>
<div class="container-fluid py-4 animate-fade-up">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-bullseye text-primary me-2"></i>Phân tích Điểm Hòa Vốn (Break-even)</h2>
            <p class="text-secondary small">Tính toán mức doanh thu cần đạt để không bị lỗ dựa trên cấu trúc chi phí.</p>
        </div>
        <form action="" method="GET" class="d-flex gap-2">
            <input type="month" name="month" class="form-control border-0 shadow-sm fw-bold bg-white" value="<?= $month ?>" onchange="this.form.submit()">
        </form>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-4">
                <h6 class="fw-bold text-dark mb-4">Cấu trúc Chi phí</h6>
                <div class="mb-3">
                    <div class="d-flex justify-content-between small fw-bold mb-1"><span>Chi phí cố định (Lương, Mặt bằng...)</span><span class="text-danger"><?= number_format($fixedCosts) ?> ₫</span></div>
                    <div class="progress" style="height: 6px;"><div class="progress-bar bg-danger" style="width: <?= $totalExp > 0 ? ($fixedCosts/$totalExp)*100 : 0 ?>%"></div></div>
                </div>
                <div class="mb-4">
                    <div class="d-flex justify-content-between small fw-bold mb-1"><span>Chi phí biến đổi (Vật tư, Hoa hồng...)</span><span class="text-warning"><?= number_format($variableCosts) ?> ₫</span></div>
                    <div class="progress" style="height: 6px;"><div class="progress-bar bg-warning" style="width: <?= $totalExp > 0 ? ($variableCosts/$totalExp)*100 : 0 ?>%"></div></div>
                </div>
                <div class="p-3 bg-light rounded-3 text-center mt-auto">
                    <div class="small fw-bold text-secondary text-uppercase mb-1">Tổng Chi Phí</div>
                    <div class="h4 fw-bold text-dark mb-0"><?= number_format($totalExp) ?> ₫</div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-4 bg-primary text-white position-relative overflow-hidden">
                <i class="bi bi-bullseye position-absolute text-white opacity-10" style="font-size: 15rem; right: -20px; top: -50px;"></i>
                <div class="row h-100 align-items-center position-relative z-1">
                    <div class="col-sm-6 text-center text-sm-start mb-4 mb-sm-0">
                        <div class="text-white-50 fw-bold text-uppercase mb-2">DOANH THU ĐIỂM HÒA VỐN</div>
                        <div class="display-5 fw-bold mb-2"><?= number_format($breakEvenPoint) ?> ₫</div>
                        <div class="small bg-white bg-opacity-25 d-inline-block px-3 py-1 rounded-pill">
                            Lợi nhuận cận biên: <b><?= round($marginRatio * 100, 1) ?>%</b>
                        </div>
                    </div>
                    <div class="col-sm-6 border-start border-white border-opacity-25 ps-sm-4 text-center text-sm-start">
                        <div class="text-white-50 fw-bold text-uppercase mb-2">DOANH THU THỰC TẾ</div>
                        <div class="h2 fw-bold mb-3"><?= number_format($revenue) ?> ₫</div>
                        <div class="fw-bold <?= $revenue >= $breakEvenPoint ? 'text-info' : 'text-warning' ?>">
                            <i class="bi <?= $revenue >= $breakEvenPoint ? 'bi-emoji-smile' : 'bi-emoji-frown' ?> me-1"></i>
                            <?= $revenue >= $breakEvenPoint ? 'Vượt điểm hòa vốn an toàn!' : 'Chưa đạt điểm hòa vốn.' ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection() ?>