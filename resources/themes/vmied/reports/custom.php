<?php $this->extend('layouts/app') ?>
<?php $this->section('content') ?>
<div class="container-fluid py-4 animate-fade-up">
    <div class="mb-4">
        <h2 class="fw-bold mb-1"><i class="bi bi-magic text-primary me-2"></i>Báo cáo Tự chọn (Custom Builder)</h2>
        <p class="text-secondary small">Tự do kết hợp các trường dữ liệu để tạo báo cáo theo ý muốn.</p>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-light">
        <div class="card-body p-4">
            <form action="" method="GET" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Dữ liệu nguồn</label>
                    <select name="module" class="form-select border-0 shadow-sm">
                        <option value="invoices" <?= $module=='invoices'?'selected':'' ?>>Doanh thu / Hóa đơn</option>
                        <option value="expenses" <?= $module=='expenses'?'selected':'' ?>>Chi phí hoạt động</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Tính toán</label>
                    <select name="metric" class="form-select border-0 shadow-sm">
                        <option value="sum" <?= $metric=='sum'?'selected':'' ?>>Tổng tiền (VNĐ)</option>
                        <option value="count" <?= $metric=='count'?'selected':'' ?>>Đếm số lượng (Lượt)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Nhóm dữ liệu theo</label>
                    <select name="group_by" class="form-select border-0 shadow-sm">
                        <option value="branch" <?= $groupBy=='branch'?'selected':'' ?>>Từng Chi nhánh</option>
                        <option value="month" <?= $groupBy=='month'?'selected':'' ?>>Từng Tháng</option>
                        <option value="day" <?= $groupBy=='day'?'selected':'' ?>>Từng Ngày</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Từ ngày</label>
                    <input type="date" name="from_date" class="form-control border-0 shadow-sm" value="<?= $fromDate ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Đến ngày</label>
                    <input type="date" name="to_date" class="form-control border-0 shadow-sm" value="<?= $toDate ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-dark w-100 fw-bold shadow-sm">Xuất Báo Cáo</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-primary text-white small fw-bold text-uppercase">
                <tr>
                    <th class="ps-4 py-3"><?= $groupBy == 'branch' ? 'Chi nhánh' : 'Thời gian' ?></th>
                    <th class="text-end pe-4 py-3">Kết quả Thống kê</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($results)): ?>
                    <tr><td colspan="2" class="text-center py-4 text-muted">Không có dữ liệu phù hợp với bộ lọc.</td></tr>
                <?php else: ?>
                    <?php foreach($results as $r): ?>
                    <tr>
                        <td class="ps-4 py-3 fw-bold text-dark"><?= htmlspecialchars($r['label'] ?? 'Không xác định') ?></td>
                        <td class="text-end pe-4 fw-bold <?= $metric=='sum' ? 'text-success' : 'text-primary' ?>">
                            <?= number_format((float)$r['value']) ?> <?= $metric=='sum' ? '₫' : 'lượt' ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $this->endSection() ?>