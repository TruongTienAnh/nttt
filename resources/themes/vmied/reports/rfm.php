<?php $this->extend('layouts/app') ?>
<?php $this->section('content') ?>
<div class="container-fluid py-4 animate-fade-up">
    <div class="mb-4">
        <h2 class="fw-bold mb-1"><i class="bi bi-people-fill text-primary me-2"></i>Phân khúc Khách hàng (RFM)</h2>
        <p class="text-secondary small">Đánh giá sức khỏe tập khách hàng thông qua mức độ chi tiêu và tần suất mua sắm.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 border-start border-4 border-primary">
                <div class="text-secondary small fw-bold text-uppercase">TỔNG KHÁCH ĐÃ MUA</div>
                <div class="h3 mb-0 fw-bold text-dark"><?= number_format($stats['total_customers']) ?> <i class="bi bi-person float-end text-primary opacity-50"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 border-start border-4 border-success">
                <div class="text-secondary small fw-bold text-uppercase">TỔNG LTV (DOANH THU)</div>
                <div class="h3 mb-0 fw-bold text-dark"><?= number_format($stats['gross_revenue']) ?> ₫ <i class="bi bi-cash-coin float-end text-success opacity-50"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 border-start border-4 border-warning">
                <div class="text-secondary small fw-bold text-uppercase">GIÁ TRỊ ĐƠN (AOV)</div>
                <div class="h3 mb-0 fw-bold text-dark"><?= number_format($stats['aov']) ?> ₫ <i class="bi bi-receipt float-end text-warning opacity-50"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 border-start border-4 border-info">
                <div class="text-secondary small fw-bold text-uppercase">TẦN SUẤT TRUNG BÌNH</div>
                <div class="h3 mb-0 fw-bold text-dark"><?= round($stats['total_orders'] / max($stats['total_customers'], 1), 1) ?> <span class="fs-6 fw-normal text-muted">đơn/kh</span> <i class="bi bi-arrow-repeat float-end text-info opacity-50"></i></div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <?php if (!empty($branchStats)): ?>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-dark">Hiệu quả khai thác theo Chi nhánh</h6>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light small fw-bold text-secondary">
                            <tr>
                                <th class="ps-3">Chi nhánh</th>
                                <th class="text-center">Số khách</th>
                                <th class="text-end pe-3">ARPU (Doanh thu TB)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($branchStats as $bs): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-dark"><?= $bs['name'] ?></td>
                                <td class="text-center text-secondary"><?= number_format($bs['total_customers']) ?></td>
                                <?php $arpu = $bs['total_customers'] > 0 ? $bs['revenue'] / $bs['total_customers'] : 0; ?>
                                <td class="text-end pe-3 fw-bold text-success"><?= number_format($arpu) ?> ₫</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="<?= empty($branchStats) ? 'col-12' : 'col-md-8' ?>">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">Danh sách Phân khúc Khách hàng</h6>
                    <input type="text" id="search-rfm" class="form-control form-control-sm w-auto bg-light border-0" placeholder="Tìm tên khách hàng...">
                </div>
                <div class="table-responsive custom-scrollbar" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0" id="rfm-table">
                        <thead class="bg-light sticky-top small fw-bold text-uppercase text-secondary">
                            <tr>
                                <th class="ps-4 py-3 border-bottom-0">Khách hàng</th>
                                <th class="text-center py-3 border-bottom-0" title="Recency: Thời gian từ lần cuối mua">Ngày nghỉ</th>
                                <th class="text-center py-3 border-bottom-0" title="Frequency: Số lần mua">Số đơn</th>
                                <th class="text-end py-3 border-bottom-0" title="Monetary: Tổng tiền">Tổng chi (LTV)</th>
                                <th class="text-center pe-4 py-3 border-bottom-0">Phân khúc</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($rfmList as $r): ?>
                            <tr class="searchable-row">
                                <td class="ps-4 py-3 fw-bold text-dark">
                                    <?= htmlspecialchars($r['full_name']) ?> <br>
                                    <span class="text-secondary small fw-normal font-monospace"><?= htmlspecialchars($r['phone'] ?? "") ?></span>
                                </td>
                                <td class="text-center text-muted"><?= $r['r_days'] ?> ngày</td>
                                <td class="text-center fw-bold text-dark"><?= $r['f_count'] ?></td>
                                <td class="text-end fw-bold text-danger"><?= number_format($r['m_total']) ?> ₫</td>
                                <td class="text-center pe-4">
                                    <?php 
                                        $badges = [
                                            'VIP' => 'bg-success text-success',
                                            'NGỦ ĐÔNG' => 'bg-danger text-danger',
                                            'MỚI' => 'bg-info text-info',
                                            'TIỀM NĂNG' => 'bg-warning text-warning'
                                        ];
                                        $cls = $badges[$r['segment']] ?? 'bg-secondary text-secondary';
                                    ?>
                                    <span class="badge <?= $cls ?> bg-opacity-10 rounded-pill px-3 py-1 border border-light"><?= $r['segment'] ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
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