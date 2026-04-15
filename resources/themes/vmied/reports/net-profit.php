<?php $this->extend('layouts/app') ?>
<?php $this->section('content') ?>
<div class="container-fluid py-4 animate-fade-up">
    <div class="mb-4">
        <h1 class="h3 fw-bold">Báo cáo Lợi nhuận ròng (P&L)</h1>
        <p class="text-secondary">Dữ liệu tính toán dựa trên thực thu (Invoices) và thực chi (Expenses).</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="clean-card p-4 text-center">
                <div class="text-secondary small fw-bold">TỔNG DOANH THU</div>
                <div class="h2 mb-0 fw-bold text-success"><?= number_format($revenue) ?> ₫</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="clean-card p-4 text-center">
                <div class="text-secondary small fw-bold">TỔNG CHI PHÍ</div>
                <div class="h2 mb-0 fw-bold text-danger"><?= number_format($expenses) ?> ₫</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="clean-card p-4 text-center bg-primary bg-opacity-10">
                <div class="text-primary small fw-bold">LỢI NHUẬN RÒNG</div>
                <div class="h2 mb-0 fw-bold <?= $netProfit >= 0 ? 'text-primary' : 'text-danger' ?>">
                    <?= ($netProfit < 0 ? '-' : '') . number_format(abs($netProfit)) ?> ₫
                </div>
            </div>
        </div>
    </div>

    <div class="clean-card p-4">
        <h6 class="fw-bold mb-4">Biểu đồ Doanh thu 6 tháng gần nhất</h6>
        <div style="height: 300px;"><canvas id="profitTrendChart"></canvas></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('profitTrendChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($trendData, 'month')) ?>,
        datasets: [{
            label: 'Doanh thu',
            data: <?= json_encode(array_column($trendData, 'revenue')) ?>,
            borderColor: '#f25f5c',
            backgroundColor: 'rgba(242, 95, 92, 0.1)',
            fill: true,
            tension: 0.4
        }]
    }
});
</script>
<?php $this->endSection() ?>