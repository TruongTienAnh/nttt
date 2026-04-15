<?php $this->extend('layouts/app') ?>
<?php $this->section('content') ?>
<div class="container-fluid py-3 animate-fade-up">
    <div class="row g-3 mb-4 text-center">
        <div class="col-md-3"><div class="clean-card p-3 border-start border-4 border-primary shadow-sm"><div class="text-secondary small fw-bold">TỔNG KHÁCH</div><div class="h3 mb-0 fw-bold"><?= number_format($stats['total_customers'] ?? 0) ?></div></div></div>
        <div class="col-md-3"><div class="clean-card p-3 border-start border-4 border-success shadow-sm"><div class="text-secondary small fw-bold">DOANH THU</div><div class="h3 mb-0 fw-bold"><?= number_format($stats['gross_revenue'] ?? 0) ?> ₫</div></div></div>
        <div class="col-md-3"><div class="clean-card p-3 border-start border-4 border-warning shadow-sm"><div class="text-secondary small fw-bold">GIÁ TRỊ ĐƠN</div><div class="h3 mb-0 fw-bold"><?= number_format($stats['aov'] ?? 0) ?> ₫</div></div></div>
        <div class="col-md-3"><div class="clean-card p-3 border-start border-4 border-info shadow-sm"><div class="text-secondary small fw-bold">TẦN SUẤT</div><div class="h3 mb-0 fw-bold"><?= round(($stats['total_orders'] ?? 0) / max($stats['total_customers'], 1), 1) ?> đơn/kh</div></div></div>
    </div>

    <div class="mb-3 d-flex align-items-center gap-2">
        <i class="bi bi-people fs-4 text-primary"></i> <h4 class="mb-0 fw-bold">Báo cáo RFM Phân khúc</h4>
    </div>

    <div class="clean-card shadow-sm border-0">
        <div class="p-3 border-bottom">
            <input type="text" id="search-rfm" class="form-control" placeholder="Tìm kiếm khách hàng...">
        </div>
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <table class="table table-hover align-middle mb-0 fs-7" id="rfm-table">
                <thead class="table-light sticky-top">
                    <tr class="text-uppercase small fw-bold">
                        <th class="ps-4 py-3">Khách hàng</th>
                        <th class="text-center py-3">R (Ngày nghỉ)</th>
                        <th class="text-center py-3">F (Số đơn)</th>
                        <th class="text-end py-3">M (Tổng chi)</th>
                        <th class="text-center pe-4 py-3">Phân loại</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($rfmList as $r): ?>
                    <tr class="searchable-row">
                        <td class="ps-4 py-3 fw-bold text-dark"><?= htmlspecialchars($r['full_name']) ?> <br><span class="text-secondary small fw-normal"><?= htmlspecialchars($r['phone'] ?? "") ?></span></td>
                        <td class="text-center"><?= $r['r_days'] ?> ngày</td>
                        <td class="text-center fw-bold"><?= $r['f_count'] ?> đơn</td>
                        <td class="text-end fw-bold text-accent"><?= number_format($r['m_total']) ?>đ</td>
                        <td class="text-center pe-4">
                            <?php $cls = $r['segment'] == 'VIP' ? 'success' : ($r['segment'] == 'NGỦ ĐÔNG' ? 'danger' : 'info'); ?>
                            <span class="badge bg-<?= $cls ?> bg-opacity-10 text-<?= $cls ?> rounded-pill px-3"><?= $r['segment'] ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
document.getElementById('search-rfm').addEventListener('input', function() {
    let q = this.value.toLowerCase();
    document.querySelectorAll('#rfm-table .searchable-row').forEach(row => { row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none'; });
});
</script>
<?php $this->endSection() ?>