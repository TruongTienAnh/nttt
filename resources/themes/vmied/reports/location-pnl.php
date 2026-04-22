<?php $this->extend('layouts/app') ?>
<?php $this->section('content') ?>
<div class="container-fluid py-4 animate-fade-up">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 fw-bold mb-1">Xếp hạng Hiệu quả Chi nhánh (P&L Multi-location)</h1>
            <p class="text-secondary mb-0">Tháng hiện tại: <?= date('m/Y') ?>. Đánh giá dựa trên lợi nhuận ròng và biên độ lợi nhuận (Margin).</p>
        </div>
    </div>
    
    <div style="max-height: 65vh; overflow-y: auto; overflow-x: hidden; padding-right: 12px;" class="custom-scrollbar">
        <div class="clean-card overflow-hidden border-0 shadow-sm">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="text-uppercase small fw-bold text-secondary">
                        <th class="ps-4 py-3" style="width: 5%">Hạng</th>
                        <th style="width: 25%">Chi nhánh</th>
                        <th class="text-end">Doanh thu</th>
                        <th class="text-end">Chi phí</th>
                        <th class="text-end">Lợi nhuận ròng</th>
                        <th class="text-end pe-4" style="width: 20%">Biên độ Lợi nhuận (Margin)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($locationPnL as $idx => $item): 
                        $isLoss = $item['profit'] < 0;
                    ?>
                    <tr>
                        <td class="ps-4 py-3">
                            <span class="badge <?= $idx == 0 ? 'bg-warning text-dark' : ($idx == 1 ? 'bg-secondary' : 'bg-dark') ?> rounded-circle p-2" style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center;">
                                <?= $idx + 1 ?>
                            </span>
                        </td>
                        <td><span class="fw-bold fs-6"><?= htmlspecialchars($item['branch_name']) ?></span></td>
                        <td class="text-end fw-bold text-dark"><?= number_format($item['revenue']) ?>đ</td>
                        <td class="text-end text-danger"><?= number_format($item['cost']) ?>đ</td>
                        <td class="text-end">
                            <span class="fw-bolder fs-6 <?= $isLoss ? 'text-danger' : 'text-success' ?>">
                                <?= ($isLoss ? '-' : '') . number_format(abs($item['profit'])) ?>đ
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex align-items-center justify-content-end gap-2">
                                <span class="fw-bold <?= $isLoss ? 'text-danger' : 'text-primary' ?>"><?= round($item['margin'], 1) ?>%</span>
                                <div class="progress" style="width: 60px; height: 6px;">
                                    <div class="progress-bar <?= $isLoss ? 'bg-danger' : 'bg-primary' ?>" style="width: <?= min(100, max(0, $item['margin'])) ?>%"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $this->endSection() ?>