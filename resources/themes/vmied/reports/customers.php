<?php $this->extend('layouts/app') ?>

<?php $this->section('content') ?>
<div class="container-fluid py-3 animate-fade-up">
    <div class="row g-3 mb-4 text-center">
        <div class="col-md-3">
            <div class="clean-card p-3 border-start border-4 border-primary shadow-sm">
                <div class="text-secondary small fw-bold">TỔNG KHÁCH</div>
                <div class="h3 mb-0 fw-bold"><?= number_format($stats['total_customers'] ?? 0) ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="clean-card p-3 border-start border-4 border-success shadow-sm">
                <div class="text-secondary small fw-bold">DOANH THU</div>
                <div class="h3 mb-0 fw-bold"><?= number_format($stats['gross_revenue'] ?? 0) ?> ₫</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="clean-card p-3 border-start border-4 border-warning shadow-sm">
                <div class="text-secondary small fw-bold">GIÁ TRỊ ĐƠN</div>
                <div class="h3 mb-0 fw-bold"><?= number_format($stats['aov'] ?? 0) ?> ₫</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="clean-card p-3 border-start border-4 border-info shadow-sm">
                <div class="text-secondary small fw-bold">TẦN SUẤT</div>
                <div class="h3 mb-0 fw-bold"><?= round(($stats['total_orders'] ?? 0) / max($stats['total_customers'], 1), 1) ?> đơn/khách</div>
            </div>
        </div>
    </div>

    <ul class="nav nav-pills mb-4 bg-light p-1 rounded-3 shadow-sm" id="pills-tab" role="tablist">
        <li class="nav-item flex-fill">
            <button class="nav-link active fw-bold w-100 border-0" data-bs-toggle="pill" data-bs-target="#tab-rfm">
                <i class="bi bi-people me-1"></i> Báo cáo RFM Phân khúc
            </button>
        </li>
        <li class="nav-item flex-fill">
            <button class="nav-link fw-bold w-100 border-0" data-bs-toggle="pill" data-bs-target="#tab-clv">
                <i class="bi bi-heart-pulse me-1"></i> Phân tích vòng đời (CLV) & Churn Rate
            </button>
        </li>
        <li class="nav-item flex-fill">
            <button class="nav-link fw-bold w-100 border-0" data-bs-toggle="pill" data-bs-target="#tab-cross">
                <i class="bi bi-cart-plus me-1"></i> Báo cáo Cross-sell Hệ sinh thái
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="tab-rfm">
            <div class="clean-card shadow-sm border-0">
                <div class="p-3 border-bottom">
                    <div class="input-group" style="max-width: 400px;">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-secondary"></i>
                        </span>
                        <input type="text" id="search-rfm" class="form-control border-start-0 shadow-none" 
                            placeholder="Tìm tên, SĐT, Phân khúc hoặc Sản phẩm...">
                    </div>
                </div>
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0 fs-7">
                        <thead class="table-light sticky-top" style="top: 0; z-index: 1;">
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
                                <td class="ps-4 py-3">
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($r['full_name']) ?></div>
                                    <div class="text-secondary small font-monospace"><?= htmlspecialchars($r['phone'] ?? "") ?></div>
                                </td>
                                <td class="text-center"><?= $r['r_days'] ?> ngày</td>
                                <td class="text-center fw-bold"><?= $r['f_count'] ?> đơn</td>
                                <td class="text-end fw-bold text-accent"><?= number_format($r['m_total']) ?>đ</td>
                                <td class="text-center pe-4">
                                    <?php 
                                        $cls = $r['segment'] == 'VIP' ? 'success' : ($r['segment'] == 'NGỦ ĐÔNG' ? 'danger' : 'info');
                                    ?>
                                    <span class="badge bg-<?= $cls ?> bg-opacity-10 text-<?= $cls ?> border border-<?= $cls ?>-subtle rounded-pill px-3">
                                        <?= $r['segment'] ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-clv">
            <div class="row g-3">
                <div class="col-md-5">
                    <div class="clean-card p-4 h-100 shadow-sm border-0 text-center">
                        <h6 class="fw-bold mb-4 text-start">Phân bổ sức khỏe khách hàng</h6>
                        <div style="height: 250px;"><canvas id="churnChart"></canvas></div>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="clean-card shadow-sm h-100 border-0">
                        <div class="p-3 border-bottom">
                            <div class="input-group" style="max-width: 400px;">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-search text-secondary"></i>
                                </span>
                                <input type="text" id="search-clv" class="form-control border-start-0 shadow-none" 
                                    placeholder="Tìm tên, SĐT, Phân khúc hoặc Sản phẩm...">
                            </div>
                        </div>
                        <div class="p-3 border-bottom fw-bold text-danger bg-white sticky-top" style="top:0; z-index:2;">
                            <i class="bi bi-exclamation-triangle me-1"></i> Khách sắp rời bỏ (>30 ngày)
                        </div>
                        <div class="table-responsive" style="max-height: 440px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0 fs-7">
                                <thead class="table-light sticky-top" style="top: 0; z-index: 1;">
                                    <tr class="text-uppercase small fw-bold">
                                        <th class="ps-4">Khách hàng</th>
                                        <th class="text-center">Thời gian nghỉ</th>
                                        <th class="text-end pe-4">Tổng chi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($rfmList as $r): if($r['r_days'] > 30): ?>
                                    <tr class="searchable-row">
                                        <td class="ps-4 py-3"><b><?= e($r['full_name']) ?></b></td>
                                        <td class="text-center text-danger fw-bold"><?= $r['r_days'] ?> ngày</td>
                                        <td class="text-end pe-4 fw-bold text-accent"><?= number_format($r['m_total']) ?>đ</td>
                                    </tr>
                                    <?php endif; endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-cross">
            <div class="clean-card shadow-sm border-0">
                <div class="p-3 border-bottom">
                    <div class="input-group" style="max-width: 400px;">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-secondary"></i>
                        </span>
                        <input type="text" id="search-cross" class="form-control border-start-0 shadow-none" 
                            placeholder="Tìm tên, SĐT, Phân khúc hoặc Sản phẩm...">
                    </div>
                </div>
                <div class="p-3 border-bottom fw-bold text-accent bg-white sticky-top" style="top:0; z-index:2;">
                    <i class="bi bi-lightbulb me-1"></i> Phân tích hành vi mua kèm
                </div>
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0 fs-7">
                        <thead class="table-light sticky-top" style="top: 0; z-index: 1;">
                            <tr class="text-uppercase small fw-bold">
                                <th class="ps-4 py-3">Sản phẩm A</th>
                                <th class="text-center py-3">Mua kèm</th>
                                <th class="py-3">Sản phẩm B</th>
                                <th class="text-center pe-4 py-3">Số khách mua chung</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($crossSell as $c): ?>
                            <tr class="searchable-row">
                                <td class="ps-4 py-3 text-dark"><b><?= e($c['p1']) ?></b></td>
                                <td class="text-center text-primary"><i class="bi bi-arrow-left-right fs-5"></i></td>
                                <td class="py-3 text-dark"><b><?= e($c['p2']) ?></b></td>
                                <td class="text-center pe-4">
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-3 py-2">
                                        <?= $c['freq'] ?> khách
                                    </span>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function initSearch(inputId, tabId) {
        const input = document.getElementById(inputId);
        if (!input) return;

        input.addEventListener('input', function () {
            const q = this.value.toLowerCase();

            document.querySelectorAll(`#${tabId} .searchable-row`).forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    }

    document.addEventListener("DOMContentLoaded", function() {
        initSearch('search-rfm', 'tab-rfm');
        initSearch('search-clv', 'tab-clv');
        initSearch('search-cross', 'tab-cross');
    });
</script>

<?php $this->endSection() ?>