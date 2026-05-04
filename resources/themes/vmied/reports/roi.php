<?php $this->extend('layouts/app') ?>
<?php $this->section('content') ?>
<div class="container-fluid py-4 animate-fade-up">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-pie-chart text-danger me-2"></i>Hiệu quả Đầu tư (ROI Marketing)</h2>
            <p class="text-secondary small">Đánh giá doanh thu sinh ra trên mỗi đồng chi phí Quảng cáo/Marketing.</p>
        </div>
        <form action="" method="GET" class="d-flex gap-2">
            <select name="year" class="form-select border-0 shadow-sm fw-bold bg-white" onchange="this.form.submit()">
                <?php for($y = date('Y'); $y >= date('Y') - 3; $y--): ?><option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option><?php endfor; ?>
            </select>
        </form>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-4 text-center bg-danger bg-opacity-10 border border-danger border-opacity-25">
                <div class="text-danger small fw-bold text-uppercase mb-2">TỔNG ROI HỆ THỐNG</div>
                <div class="display-4 fw-bold text-danger mb-3"><?= round($overallRoi) ?>%</div>
                <div class="small text-dark">Với mỗi <b class="text-danger">1đ</b> chi cho Marketing,<br>hệ thống thu về <b class="text-success"><?= round(($totalRev / max($totalMkt, 1)), 1) ?>đ</b> doanh thu.</div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <div style="height: 250px;"><canvas id="roiChart"></canvas></div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-light small text-secondary fw-bold text-uppercase">
                    <tr><th class="ps-4 py-3">Kỳ kế toán</th><th class="text-end">Chi phí Marketing</th><th class="text-end">Doanh thu thu về</th><th class="text-end pe-4">Tỷ suất ROI</th></tr>
                </thead>
                <tbody>
                    <?php foreach(array_reverse($monthlyData) as $m): ?>
                    <tr>
                        <td class="ps-4 fw-bold text-dark py-3"><?= $m['month'] ?></td>
                        <td class="text-end fw-bold text-danger"><?= number_format($m['marketing']) ?> ₫</td>
                        <td class="text-end fw-bold text-success"><?= number_format($m['revenue']) ?> ₫</td>
                        <td class="text-end pe-4"><span class="badge <?= $m['roi'] > 0 ? 'bg-success' : 'bg-secondary' ?> px-3 py-2 rounded-pill"><?= $m['roi'] ?>%</span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('roiChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column($monthlyData, 'month')) ?>,
                datasets: [{
                    label: 'Tỷ suất ROI (%)',
                    data: <?= json_encode(array_column($monthlyData, 'roi')) ?>,
                    borderColor: '#e63946', backgroundColor: 'rgba(230, 57, 70, 0.2)', fill: true, tension: 0.4
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }
</script>
<?php $this->endSection() ?>