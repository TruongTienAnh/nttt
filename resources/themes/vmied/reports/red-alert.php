<?php $this->extend('layouts/app') ?>
<?php $this->section('content') ?>
<div class="container-fluid py-4 animate-fade-up">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Hệ thống Báo động đỏ (Red Alert)</h1>
            <p class="text-secondary mb-0">Theo dõi đà suy giảm doanh thu so với tuần trước (Week over Week).</p>
        </div>
    </div>

    <div class="row g-3 mb-4 text-center">
        <div class="col-md-4">
            <div class="clean-card p-4 border-0 shadow-sm h-100 bg-light">
                <div class="text-secondary small fw-bold mb-2 text-uppercase">Tuần trước (7 ngày trước)</div>
                <div class="h3 mb-0 fw-bold text-secondary"><?= number_format($prev7DaysRev) ?> ₫</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="clean-card p-4 border-0 shadow-sm h-100 <?= $isAlert ? 'bg-danger text-white' : '' ?>">
                <div class="<?= $isAlert ? 'text-white-50' : 'text-secondary' ?> small fw-bold mb-2 text-uppercase">Tuần này (7 ngày qua)</div>
                <div class="h2 mb-0 fw-bold <?= $isAlert ? 'text-white' : 'text-dark' ?>"><?= number_format($revenue7Days) ?> ₫</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="clean-card p-4 border-0 shadow-sm d-flex flex-column justify-content-center align-items-center h-100">
                <div class="text-secondary small fw-bold mb-2">TỐC ĐỘ TĂNG TRƯỞNG (WoW)</div>
                <div class="display-5 fw-bold <?= $wowGrowth < 0 ? 'text-danger' : 'text-success' ?>">
                    <?= $wowGrowth > 0 ? '<i class="bi bi-arrow-up"></i> +' : '<i class="bi bi-arrow-down"></i> ' ?><?= round($wowGrowth, 1) ?>%
                </div>
            </div>
        </div>
    </div>

    <div class="clean-card p-4 border-0 shadow-sm">
        <h6 class="fw-bold mb-4"><i class="bi bi-bar-chart-fill me-2"></i>Biểu đồ Doanh thu 7 ngày gần nhất</h6>
        <canvas id="redAlertChart" height="100"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
<?php
    // Tiền xử lý dữ liệu PHP để code JS bên dưới sạch sẽ và không thể lỗi
    $chartLabels = is_array($dailyData) ? array_column($dailyData, 'day_label') : [];
    $chartData   = is_array($dailyData) ? array_column($dailyData, 'daily_rev') : [];
    $barColor    = $isAlert ? '#f25f5c' : '#2a9d8f';
?>

// Cách cũ siêu đơn giản
new Chart(document.getElementById('redAlertChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [{
            label: 'Doanh thu (VNĐ)',
            data: <?= json_encode($chartData) ?>,
            backgroundColor: '<?= $barColor ?>',
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: { 
            legend: { display: false } 
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>
<?php $this->endSection() ?>