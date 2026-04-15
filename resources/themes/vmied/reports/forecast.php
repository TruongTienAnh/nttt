<?php $this->extend('layouts/app') ?>
<?php $this->section('content') ?>
<div class="container-fluid py-4 animate-fade-up">
    <div class="mb-4">
        <h1 class="h3 fw-bold">Dự báo Doanh thu (Revenue Forecast)</h1>
        <p class="text-secondary">Dự báo dựa trên tốc độ tăng trưởng hiện tại (Run-rate) của tháng.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="clean-card p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-secondary small fw-bold">TIẾN ĐỘ THÁNG</span>
                    <span class="badge bg-primary"><?= $currentDay ?> / <?= $daysInMonth ?> ngày</span>
                </div>
                <div class="h1 fw-bold mb-1"><?= number_format($revenue) ?> ₫</div>
                <div class="text-secondary small">Doanh thu thực tế đã đạt được</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="clean-card p-4 h-100 border-primary border-2">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-primary small fw-bold">DỰ BÁO CUỐI THÁNG</span>
                    <i class="bi bi-magic text-primary"></i>
                </div>
                <div class="h1 fw-bold text-primary mb-1"><?= number_format($forecastRevenue) ?> ₫</div>
                <div class="text-secondary small">Ước tính doanh thu khi kết thúc tháng</div>
            </div>
        </div>
    </div>

    <div class="clean-card p-4">
        <h6 class="fw-bold mb-4">Biểu đồ So sánh Thực tế vs Dự báo</h6>
        <div style="height: 300px;"><canvas id="forecastChart"></canvas></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('forecastChart'), {
    type: 'bar',
    data: {
        labels: ['Thực tế hiện tại', 'Dự báo cuối tháng'],
        datasets: [{
            label: 'Doanh thu (VNĐ)',
            data: [<?= $revenue ?>, <?= $forecastRevenue ?>],
            backgroundColor: ['#6c757d', '#f25f5c'],
            borderRadius: 10
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } }
    }
});
</script>
<?php $this->endSection() ?>