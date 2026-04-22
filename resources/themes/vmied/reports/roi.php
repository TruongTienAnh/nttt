<?php $this->extend('layouts/app') ?>
<?php $this->section('content') ?>
<div class="container-fluid py-4 animate-fade-up">
    <div class="mb-4">
        <h1 class="h3 fw-bold">Tốc độ Thu hồi vốn Đa điểm (ROI Tracking)</h1>
        <p class="text-secondary">Giả định vốn đầu tư ban đầu: <b><?= number_format($investmentPerBranch) ?>đ / chi nhánh</b>. Tốc độ thu hồi vốn dựa trên Lợi nhuận trung bình 3 tháng gần nhất.</p>
    </div>

    <div style="max-height: 65vh; overflow-y: auto; overflow-x: hidden; padding-right: 12px;" class="custom-scrollbar">
        <div class="row g-4">
            <?php foreach($roiData as $idx => $r): 
                $progress = min(100, max(0, ($r['accumulatedProfit'] / $investmentPerBranch) * 100));
                $isRecovered = $progress >= 100;
            ?>
            <div class="col-md-6">
                <div class="clean-card p-4 h-100 border-0 shadow-sm position-relative overflow-hidden">
                    <?php if($isRecovered): ?>
                        <div class="position-absolute top-0 end-0 bg-success text-white px-3 py-1 fw-bold" style="border-bottom-left-radius: 10px;">Đã hoàn vốn</div>
                    <?php endif; ?>
                    
                    <h4 class="fw-bold mb-3 d-flex align-items-center gap-2">
                        <span class="text-secondary">#<?= $idx + 1 ?></span> <?= htmlspecialchars($r['name']) ?>
                    </h4>
                    
                    <div class="row text-center mb-4">
                        <div class="col-6 border-end">
                            <div class="text-secondary small fw-bold mb-1">LỢI NHUẬN TÍCH LŨY</div>
                            <div class="h4 fw-bold text-success"><?= number_format($r['accumulatedProfit']) ?>đ</div>
                        </div>
                        <div class="col-6">
                            <div class="text-secondary small fw-bold mb-1">ROI</div>
                            <div class="h4 fw-bold text-primary"><?= round($r['roi'], 1) ?>%</div>
                        </div>
                    </div>

                    <div class="mb-2 d-flex justify-content-between small fw-bold">
                        <span class="text-secondary">Tiến độ thu hồi vốn</span>
                        <span class="<?= $isRecovered ? 'text-success' : 'text-dark' ?>"><?= round($progress, 1) ?>%</span>
                    </div>
                    <div class="progress mb-4" style="height: 10px; border-radius: 10px;">
                        <div class="progress-bar <?= $isRecovered ? 'bg-success' : 'bg-primary progress-bar-striped progress-bar-animated' ?>" style="width: <?= $progress ?>%"></div>
                    </div>

                    <div class="bg-light p-3 rounded-3 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-secondary fw-bold">Dự kiến hoàn vốn</div>
                            <?php if($isRecovered): ?>
                                <div class="fw-bold text-success">Đã hoàn thành</div>
                            <?php elseif($r['paybackMonths'] > 0): ?>
                                <div class="fw-bold text-dark">Còn <span class="text-danger fs-5"><?= round($r['paybackMonths'], 1) ?></span> tháng nữa</div>
                            <?php else: ?>
                                <div class="fw-bold text-danger">Đang lỗ, không thể tính</div>
                            <?php endif; ?>
                        </div>
                        <div class="text-end">
                            <div class="small text-secondary fw-bold">Tốc độ LN / Tháng</div>
                            <div class="fw-bold text-dark"><?= number_format($r['avgProfit3M']) ?>đ</div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php $this->endSection() ?>