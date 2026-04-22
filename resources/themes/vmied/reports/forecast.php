<?php $this->extend('layouts/app') ?>
<?php $this->section('content') ?>
<div class="container-fluid py-4 animate-fade-up">
    <div class="mb-4">
        <h1 class="h3 fw-bold">Dự báo Tài chính (Sales Forecasting)</h1>
        <p class="text-secondary">Ước tính kết quả kinh doanh cuối tháng dựa trên hiệu suất ngày (Run-rate).</p>
    </div>

    <div class="row g-3 mb-4 text-center">
        <div class="col-md-4">
            <div class="clean-card p-4 border-0 shadow-sm">
                <div class="text-secondary small fw-bold mb-2">ĐÃ ĐẠT ĐƯỢC (<?= $currentDay ?> ngày)</div>
                <div class="h2 fw-bold text-dark"><?= number_format($revenue) ?> ₫</div>
                <div class="progress mt-3" style="height: 6px;">
                    <div class="progress-bar" style="width: <?= ($currentDay/$daysInMonth)*100 ?>%"></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="clean-card p-4 border-0 shadow-sm border-start border-primary border-5">
                <div class="text-primary small fw-bold mb-2 text-uppercase">Dự báo cuối tháng</div>
                <div class="h2 fw-bold text-primary"><?= number_format($forecastRevenue) ?> ₫</div>
                <div class="small text-secondary mt-2">Dựa trên tốc độ hiện tại</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="clean-card p-4 border-0 shadow-sm">
                <div class="text-secondary small fw-bold mb-2 text-uppercase">Mục tiêu thu/ngày tới</div>
                <div class="h2 fw-bold text-accent">
                    <?= number_format(($forecastRevenue - $revenue) / max(1, ($daysInMonth - $currentDay))) ?> ₫
                </div>
                <div class="small text-secondary mt-2">Để giữ vững tiến độ dự báo</div>
            </div>
        </div>
    </div>

    <div class="clean-card p-4 border-0 shadow-sm">
        <h6 class="fw-bold mb-4">Trực quan hóa tiến độ tháng (Actual vs Forecast)</h6>
        <div style="height: 350px;"><canvas id="forecastChartPro"></canvas></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('forecastChartPro'), {
    type: 'bar',
    data: {
        labels: ['Doanh thu thực tế', 'Dự kiến cần thu thêm', 'Tổng dự báo cuối tháng'],
        datasets: [{
            label: 'Số tiền (VNĐ)',
            data: [<?= $revenue ?>, <?= $forecastRevenue - $revenue ?>, <?= $forecastRevenue ?>],
            backgroundColor: ['#2a9d8f', '#e9c46a', '#264653'],
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: { y: { beginAtZero: true } },
        plugins: { legend: { display: false } }
    }
});
</script>
<?php $this->endSection() ?>