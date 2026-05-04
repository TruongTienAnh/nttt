<?php $this->extend('layouts/app') ?>
<?php $this->section('content') ?>

<div class="container-fluid py-4 animate-fade-up">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark">Hệ thống Cảnh báo Động</h1>
            <p class="text-secondary mb-0">Tự động giám sát dữ liệu và phát tín hiệu khi chạm ngưỡng.</p>
        </div>
        <button onclick="modal('/config/alerts/create')" class="btn btn-danger fw-bold rounded-pill px-4 shadow-sm hover-lift d-flex align-items-center gap-2">
            <i class="bi bi-bell-fill"></i> Tạo cảnh báo mới
        </button>
    </div>

    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary small fw-bold text-uppercase">Tên cảnh báo</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase">Logic giám sát</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase">Trạng thái</th>
                        <th class="pe-4 py-3 text-end text-secondary small fw-bold text-uppercase">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    <?php if (empty($rules)): ?>
                        <tr><td colspan="4" class="text-center text-secondary py-5">Chưa có quy tắc cảnh báo nào được thiết lập.</td></tr>
                    <?php else: ?>
                        <?php 
                        $modMap = ['expenses' => 'Chi phí', 'invoices' => 'Doanh thu'];
                        $metricMap = ['sum_amount' => 'Tổng tiền'];
                        $timeMap = ['this_month' => 'Tháng này', 'today' => 'Hôm nay'];
                        ?>
                        <?php foreach($rules as $r): ?>
                        <tr class="hover-bg-light transition-all">
                            <td class="ps-4 py-3 fw-bold text-dark">
                                <i class="bi bi-shield-exclamation text-danger me-2"></i><?= htmlspecialchars($r['title']) ?>
                            </td>
                            <td class="py-3">
                                <div class="small bg-light p-2 rounded-3 border d-inline-block">
                                    Nếu <b><?= $modMap[$r['module']] ?? $r['module'] ?></b> (<?= $timeMap[$r['time_frame']] ?? $r['time_frame'] ?>) 
                                    <span class="text-danger fw-bold"><?= $r['condition_type'] ?> <?= number_format($r['threshold_value']) ?></span>
                                </div>
                            </td>
                            <td class="py-3" x-data="Form()">
                                <div class="form-check form-switch">
                                    <input class="form-check-input cursor-pointer" type="checkbox" role="switch"
                                           <?= $r['is_active'] ? 'checked' : '' ?>
                                           hx-post="/config/alerts/<?= $r['id'] ?>/toggle"
                                           hx-vals='{"status": "<?= $r['is_active'] ? 0 : 1 ?>"}'
                                           hx-swap="none" @htmx:after-request="handleResponse($event)">
                                </div>
                            </td>
                            <td class="pe-4 py-3 text-end" x-data="Form()">
                                <div class="d-flex gap-2 justify-content-end">
                                    <button onclick="modal('/config/alerts/<?= $r['id'] ?>/edit')" class="btn btn-light btn-sm text-primary bg-primary-subtle border-0 rounded-3"><i class="bi bi-pencil-square"></i></button>
                                    <button hx-post="/config/alerts/<?= $r['id'] ?>/delete" hx-confirm="Xóa quy tắc này?" hx-swap="none" @htmx:after-request="handleResponse($event)" class="btn btn-light btn-sm text-danger bg-danger-subtle border-0 rounded-3"><i class="bi bi-trash3"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $this->endSection() ?>