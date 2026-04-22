<?php $this->extend('layouts/app') ?>
<?php $this->section('content') ?>
<div class="container-fluid py-4 animate-fade-up">
    <div class="mb-4">
        <h1 class="h3 fw-bold mb-1">Báo cáo Lợi nhuận ròng (P&L Summary)</h1>
        <p class="text-secondary">Thống kê doanh thu, chi phí và lợi nhuận thực tế theo tháng.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="clean-card p-4 border-0 shadow-sm text-center">
                <div class="text-secondary small fw-bold mb-2 text-uppercase">Tổng doanh thu</div>
                <div class="h2 mb-0 fw-bold text-success"><?= number_format($revenue) ?> ₫</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="clean-card p-4 border-0 shadow-sm text-center">
                <div class="text-secondary small fw-bold mb-2 text-uppercase">Tổng chi phí</div>
                <div class="h2 mb-0 fw-bold text-danger"><?= number_format($expenses) ?> ₫</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="clean-card p-4 border-0 shadow-sm text-center bg-primary bg-opacity-10">
                <div class="text-primary small fw-bold mb-2 text-uppercase">Lợi nhuận ròng</div>
                <div class="h2 mb-0 fw-bold <?= $netProfit >= 0 ? 'text-primary' : 'text-danger' ?>">
                    <?= ($netProfit < 0 ? '-' : '') . number_format(abs($netProfit)) ?> ₫
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-8">
            <div class="clean-card p-4 border-0 shadow-sm h-100">
                <h6 class="fw-bold mb-4"><i class="bi bi-graph-up me-2"></i>Biểu đồ xu hướng doanh thu (6 tháng)</h6>
                <canvas id="profitTrendChart"></canvas>
            </div>
        </div>
        <div class="col-md-4">
            <div class="clean-card p-4 border-0 shadow-sm h-100">
                <h6 class="fw-bold mb-4"><i class="bi bi-pie-chart me-2"></i>Tỷ trọng Doanh thu Chi nhánh</h6>
                <?php if(!empty($branchBreakdown)): ?>
                    <canvas id="branchPieChart"></canvas>
                <?php else: ?>
                    <div class="h-100 d-flex align-items-center justify-content-center text-secondary small">
                        Chọn "Tất cả chi nhánh" để xem phân tích tỷ trọng.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Biểu đồ đường xu hướng
new Chart(document.getElementById('profitTrendChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($trendData, 'month')) ?>,
        datasets: [{
            label: 'Doanh thu',
            data: <?= json_encode(array_column($trendData, 'revenue')) ?>,
            borderColor: '#f25f5c',
            backgroundColor: 'rgba(242, 95, 92, 0.1)',
            fill: true, tension: 0.4
        }]
    },
    options: { 
        responsive: true, 
        maintainAspectRatio: true 
    }
});

<?php if(!empty($branchBreakdown)): ?>
// Biểu đồ tròn tỷ trọng chi nhánh
new Chart(document.getElementById('branchPieChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_column($branchBreakdown, 'name')) ?>,
        datasets: [{
            data: <?= json_encode(array_column($branchBreakdown, 'rev')) ?>,
            backgroundColor: ['#2a9d8f', '#e9c46a', '#f4a261', '#e76f51', '#264653']
        }]
    },
    options: { 
        responsive: true, 
        maintainAspectRatio: true, 
        plugins: { 
            legend: { 
                position: 'bottom' 
            } 
        } 
    }
});
<?php endif; ?>
</script>
<?php $this->endSection() ?>