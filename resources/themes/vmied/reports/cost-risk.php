<?php $this->extend('layouts/app') ?>
<?php $this->section('content') ?>
<div class="container-fluid py-4 animate-fade-up">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Cảnh báo Biến động Chi phí</h1>
            <p class="text-secondary mb-0">Theo dõi dòng tiền ra và phát hiện lạm chi so với tháng trước.</p>
        </div>
        <?php if($isAlert): ?>
            <div class="badge bg-danger p-3 px-4 fs-6 rounded-pill animate-pulse shadow-sm">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> LẠM CHI NGHIÊM TRỌNG
            </div>
        <?php else: ?>
            <div class="badge bg-success p-3 px-4 fs-6 rounded-pill shadow-sm">
                <i class="bi bi-shield-check me-2"></i> CHI PHÍ ỔN ĐỊNH
            </div>
        <?php endif; ?>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="clean-card p-4 text-center border-0 shadow-sm h-100">
                <div class="text-secondary small fw-bold mb-1">CHI PHÍ THÁNG TRƯỚC</div>
                <div class="h3 fw-bold text-dark"><?= number_format($lastMonthTotal) ?> ₫</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="clean-card p-4 text-center border-0 shadow-sm h-100 <?= $isAlert ? 'border-bottom border-danger border-5' : '' ?>">
                <div class="text-secondary small fw-bold mb-1">CHI PHÍ THÁNG NÀY</div>
                <div class="h2 fw-bold <?= $isAlert ? 'text-danger' : 'text-dark' ?>"><?= number_format($thisMonthTotal) ?> ₫</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="clean-card p-4 border-0 shadow-sm h-100 bg-light d-flex flex-column justify-content-center align-items-center">
                <div class="text-secondary small fw-bold mb-1">TỶ LỆ BIẾN ĐỘNG</div>
                <div class="h2 fw-bold <?= $growthRate > 0 ? 'text-danger' : 'text-success' ?>">
                    <?= $growthRate > 0 ? '<i class="bi bi-arrow-up-right"></i> +' : '<i class="bi bi-arrow-down-right"></i> ' ?><?= round($growthRate, 1) ?>%
                </div>
            </div>
        </div>
    </div>

    <div class="clean-card border-0 shadow-sm overflow-hidden">
        <div class="p-4 border-bottom bg-white">
            <h6 class="fw-bold mb-0"><i class="bi bi-search text-danger me-2"></i>Bóc tách hạng mục gây lạm chi</h6>
        </div>
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr class="text-uppercase small fw-bold text-secondary">
                    <th class="ps-4 py-3">Danh mục chi</th>
                    <th class="text-end">Tháng trước</th>
                    <th class="text-end">Tháng này</th>
                    <th class="text-end">Chênh lệch (₫)</th>
                    <th class="text-end pe-4">Tăng/Giảm (%)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($categoryBreakdown as $cat): 
                    $isSpike = $cat['diff'] > 0;
                ?>
                <tr>
                    <td class="ps-4 py-3 fw-bold text-uppercase"><?= htmlspecialchars($cat['category'] ?: 'Khác') ?></td>
                    <td class="text-end text-secondary"><?= number_format($cat['last_month']) ?>đ</td>
                    <td class="text-end fw-bold text-dark"><?= number_format($cat['this_month']) ?>đ</td>
                    <td class="text-end fw-bold <?= $isSpike ? 'text-danger' : 'text-success' ?>">
                        <?= $isSpike ? '+' : '' ?><?= number_format($cat['diff']) ?>đ
                    </td>
                    <td class="text-end pe-4">
                        <span class="badge <?= $isSpike ? 'bg-danger' : 'bg-success' ?> bg-opacity-10 <?= $isSpike ? 'text-danger' : 'text-success' ?>">
                            <?= $isSpike ? '+' : '' ?><?= round($cat['growth'], 1) ?>%
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($categoryBreakdown)): ?>
                    <tr><td colspan="5" class="text-center py-4 text-secondary">Chưa có dữ liệu chi phí để phân tích.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $this->endSection() ?>