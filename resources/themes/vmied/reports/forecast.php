<?php $this->extend('layouts/app') ?>
<?php $this->section('content') ?>
<div class="container-fluid py-4 animate-fade-up">
    <div class="mb-4">
        <h2 class="fw-bold mb-1"><i class="bi bi- binoculars text-info me-2"></i>Dự báo Doanh Thu (Forecasting)</h2>
        <p class="text-secondary small">Sử dụng dữ liệu 6 tháng gần nhất để dự phóng doanh thu 3 tháng tiếp theo.</p>
    </div>

    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
        <div style="height: 400px;"><canvas id="forecastChart"></canvas></div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                <div class="card-header bg-light border-bottom py-3"><h6 class="fw-bold text-secondary mb-0">Lịch sử (6 tháng qua)</h6></div>
                <table class="table align-middle mb-0">
                    <tbody>
                        <?php foreach($history as $h): ?>
                        <tr><td class="ps-4 fw-bold text-dark py-3"><?= $h['month'] ?></td><td class="text-end pe-4 fw-bold text-secondary"><?= number_format($h['val']) ?> ₫</td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 border-info">
                <div class="card-header bg-info bg-opacity-10 border-bottom-0 py-3"><h6 class="fw-bold text-info mb-0">Dự phóng (3 tháng tới)</h6></div>
                <table class="table align-middle mb-0">
                    <tbody>
                        <?php foreach($forecast as $f): ?>
                        <tr><td class="ps-4 fw-bold text-dark py-3"><i class="bi bi-stars text-warning me-2"></i><?= $f['month'] ?></td><td class="text-end pe-4 fw-bold text-info fs-5"><?= number_format($f['val']) ?> ₫</td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('forecastChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: [
                    ...<?= json_encode(array_column($history, 'month')) ?>, 
                    ...<?= json_encode(array_column($forecast, 'month')) ?>
                ],
                datasets: [{
                    label: 'Doanh thu Lịch sử',
                    data: [
                        ...<?= json_encode(array_column($history, 'val')) ?>,
                        ...Array(<?= count($forecast) ?>).fill(null)
                    ],
                    borderColor: '#2a9d8f', backgroundColor: '#2a9d8f', fill: false, tension: 0.3, pointRadius: 5
                }, {
                    label: 'Dự báo',
                    data: [
                        ...Array(<?= count($history) - 1 ?>).fill(null),
                        <?= !empty($history) ? end($history)['val'] : 'null' ?>,
                        ...<?= json_encode(array_column($forecast, 'val')) ?>
                    ],
                    borderColor: '#fca311', backgroundColor: '#fca311', fill: false, tension: 0.3, borderDash: [5, 5], pointRadius: 5
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }
</script>
<?php $this->endSection() ?>