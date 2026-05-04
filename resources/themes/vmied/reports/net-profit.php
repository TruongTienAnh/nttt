<?php $this->extend('layouts/app') ?>
<?php $this->section('content') ?>
<div class="container-fluid py-4 animate-fade-up">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-graph-up-arrow text-success me-2"></i>Báo cáo Lãi Lỗ Thuần (P&L)</h2>
            <p class="text-secondary small">Phân tích chênh lệch Doanh thu & Chi phí theo từng tháng trong năm.</p>
        </div>
        <form action="" method="GET" class="d-flex gap-2">
            <select name="year" class="form-select border-0 shadow-sm rounded-3 fw-bold bg-white" onchange="this.form.submit()">
                <?php for($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
                    <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>>Năm tài chính <?= $y ?></option>
                <?php endfor; ?>
            </select>
        </form>
    </div>

    <div class="row g-3 mb-4 text-center">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 border-start border-4 border-primary h-100">
                <div class="text-secondary small fw-bold text-uppercase">Tổng Doanh Thu</div>
                <div class="h3 mb-0 fw-bold text-dark"><?= number_format($totalRev) ?> ₫</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 border-start border-4 border-danger h-100">
                <div class="text-secondary small fw-bold text-uppercase">Tổng Chi Phí</div>
                <div class="h3 mb-0 fw-bold text-danger"><?= number_format($totalExp) ?> ₫</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 border-start border-4 <?= $netProfit >= 0 ? 'border-success' : 'border-danger' ?> h-100">
                <div class="text-secondary small fw-bold text-uppercase">Lợi Nhuận Ròng (Net Profit)</div>
                <div class="h3 mb-0 fw-bold <?= $netProfit >= 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($netProfit) ?> ₫</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 border-start border-4 border-info h-100">
                <div class="text-secondary small fw-bold text-uppercase">Biên Lợi Nhuận TB</div>
                <div class="h3 mb-0 fw-bold text-info"><?= $avgMargin ?>%</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <h6 class="fw-bold mb-4">Biểu đồ Biến động Dòng tiền</h6>
                <div style="height: 350px;"><canvas id="pnlChart"></canvas></div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3"><h6 class="fw-bold mb-0">Hạch toán Chi tiết</h6></div>
                <div class="table-responsive custom-scrollbar" style="max-height: 400px; overflow-y: auto;">
                    <table class="table align-middle mb-0 fs-7">
                        <thead class="bg-light sticky-top small text-secondary">
                            <tr>
                                <th class="ps-3 py-2">Kỳ kế toán</th>
                                <th class="text-end py-2">Lợi nhuận</th>
                                <th class="text-end pe-3 py-2">Biên (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach(array_reverse($monthlyData) as $m): ?>
                            <tr>
                                <td class="ps-3 fw-bold"><?= $m['month'] ?></td>
                                <td class="text-end fw-bold <?= $m['profit'] >= 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($m['profit']) ?></td>
                                <td class="text-end pe-3 text-secondary font-monospace"><?= $m['margin'] ?>%</td>
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
    const pnlCtx = document.getElementById('pnlChart');
    if (pnlCtx) {
        new Chart(pnlCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($monthlyData, 'month')) ?>,
                datasets: [
                    { label: 'Doanh thu', data: <?= json_encode(array_column($monthlyData, 'revenue')) ?>, backgroundColor: '#2a9d8f', borderRadius: 4 },
                    { label: 'Chi phí', data: <?= json_encode(array_column($monthlyData, 'expenses')) ?>, backgroundColor: '#e63946', borderRadius: 4 }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }
</script>
<?php $this->endSection() ?>