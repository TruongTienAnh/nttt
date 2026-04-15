<?php $this->extend('layouts/app') ?>
<?php $this->section('content') ?>
<div class="container-fluid py-4 animate-fade-up">
    <div class="mb-4">
        <h1 class="h3 fw-bold">Xếp hạng Hiệu quả Chi nhánh (P&L Multi-location)</h1>
    </div>

    <div class="clean-card overflow-hidden">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr class="text-uppercase small fw-bold">
                    <th class="ps-4 py-3">Chi nhánh</th>
                    <th class="text-end">Doanh thu</th>
                    <th class="text-end">Chi phí</th>
                    <th class="text-end pe-4">Lợi nhuận</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($locationPnL as $idx => $item): ?>
                <tr>
                    <td class="ps-4 py-3">
                        <span class="badge bg-dark me-2"><?= $idx + 1 ?></span>
                        <span class="fw-bold"><?= htmlspecialchars($item['branch_name']) ?></span>
                    </td>
                    <td class="text-end fw-bold text-success"><?= number_format($item['revenue']) ?>đ</td>
                    <td class="text-end text-danger"><?= number_format($item['cost']) ?>đ</td>
                    <td class="text-end pe-4">
                        <span class="fw-bolder fs-5 <?= $item['profit'] >= 0 ? 'text-primary' : 'text-danger' ?>">
                            <?= number_format($item['profit']) ?>đ
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $this->endSection() ?>