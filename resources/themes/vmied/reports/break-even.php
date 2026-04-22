<?php $this->extend('layouts/app') ?>
<?php $this->section('content') ?>
<div class="container-fluid py-4 animate-fade-up">
    <div class="mb-4">
        <h1 class="h3 fw-bold">Trạng thái Điểm Hòa Vốn (Break-even Tracker)</h1>
        <p class="text-secondary">Tháng hiện tại: <?= date('m/Y') ?>. Theo dõi chi nhánh nào đã gánh xong định phí (Mặt bằng, Lương).</p>
    </div>

    <div style="max-height: 65vh; overflow-y: auto; overflow-x: hidden; padding-right: 12px;" class="custom-scrollbar">
        <div class="clean-card border-0 shadow-sm p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="small fw-bold text-secondary text-uppercase">
                        <th class="ps-4 py-3">Chi nhánh</th>
                        <th class="text-end">Doanh thu hiện tại</th>
                        <th class="text-end">Mốc Hòa Vốn</th>
                        <th class="pe-4" style="width: 30%">Tiến độ Về bờ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($breakEvenList as $b): 
                        $isSafe = $b['progress'] >= 100;
                    ?>
                    <tr>
                        <td class="ps-4 py-3 fw-bold fs-6"><?= htmlspecialchars($b['name']) ?></td>
                        <td class="text-end fw-bold text-dark"><?= number_format($b['revenue']) ?>đ</td>
                        <td class="text-end fw-bold text-secondary"><?= number_format($b['bePoint']) ?>đ</td>
                        <td class="pe-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="progress flex-grow-1" style="height: 12px; border-radius: 6px;">
                                    <div class="progress-bar <?= $isSafe ? 'bg-success' : 'bg-warning progress-bar-striped progress-bar-animated' ?>" style="width: <?= $b['progress'] ?>%"></div>
                                </div>
                                <span class="fw-bold <?= $isSafe ? 'text-success' : 'text-dark' ?>" style="min-width: 45px;"><?= round($b['progress']) ?>%</span>
                            </div>
                            <?php if(!$isSafe): ?>
                                <div class="small text-danger text-end mt-1">Cần thêm <?= number_format($b['bePoint'] - $b['revenue']) ?>đ</div>
                            <?php else: ?>
                                <div class="small text-success text-end mt-1"><i class="bi bi-check-circle-fill"></i> Đã có lãi</div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $this->endSection() ?>