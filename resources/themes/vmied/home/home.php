<?php $this->extend('layouts/app') ?>
<?php $this->section('content') ?>
<div class="container-fluid py-4 animate-fade-up">
    
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h2 class="fw-bold mb-1 text-dark">
                <?= $greeting ?>, <?= htmlspecialchars($_SESSION['account']['name'] ?? 'bạn') ?>! 👋
            </h2>
            <p class="text-secondary small mb-0">Dưới đây là tổng quan tình hình kinh doanh của bạn ngày hôm nay.</p>
        </div>
        <div class="text-end d-none d-md-block">
            <div class="text-muted small fw-bold text-uppercase">Hôm nay</div>
            <div class="fw-bold text-primary fs-5"><?= date('d/m/Y') ?></div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 border-start border-4 border-success h-100 position-relative overflow-hidden">
                <div class="text-secondary small fw-bold text-uppercase mb-2">DOANH THU THÁNG NÀY</div>
                <div class="h3 mb-0 fw-bold text-dark"><?= number_format($revenueThisMonth) ?> ₫</div>
                <i class="bi bi-wallet2 position-absolute text-success opacity-10" style="font-size: 5rem; right: -10px; bottom: -20px;"></i>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 border-start border-4 border-primary h-100 position-relative overflow-hidden">
                <div class="text-secondary small fw-bold text-uppercase mb-2">DOANH THU HÔM NAY</div>
                <div class="h3 mb-0 fw-bold text-primary"><?= number_format($revenueToday) ?> ₫</div>
                <i class="bi bi-cash-stack position-absolute text-primary opacity-10" style="font-size: 5rem; right: -10px; bottom: -20px;"></i>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 border-start border-4 border-warning h-100 position-relative overflow-hidden">
                <div class="text-secondary small fw-bold text-uppercase mb-2">KHÁCH MỚI TRONG THÁNG</div>
                <div class="h3 mb-0 fw-bold text-dark"><?= number_format($newCustomers) ?></div>
                <i class="bi bi-people position-absolute text-warning opacity-10" style="font-size: 5rem; right: -10px; bottom: -20px;"></i>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 border-start border-4 border-info h-100 position-relative overflow-hidden">
                <div class="text-secondary small fw-bold text-uppercase mb-2">TỔNG SỐ ĐƠN HÀNG</div>
                <div class="h3 mb-0 fw-bold text-dark"><?= number_format($ordersThisMonth) ?></div>
                <i class="bi bi-receipt position-absolute text-info opacity-10" style="font-size: 5rem; right: -10px; bottom: -20px;"></i>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold mb-0 text-dark">Biến động Doanh thu (7 ngày qua)</h6>
                    <span class="badge bg-light text-secondary border">Cập nhật lúc: <?= date('H:i') ?></span>
                </div>
                <div style="height: 320px;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">Giao dịch gần đây</h6>
                    <i class="bi bi-arrow-repeat text-primary" style="cursor: pointer;" onclick="location.reload()"></i>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recentInvoices)): ?>
                        <div class="p-5 text-center text-muted">
                            <i class="bi bi-inbox fs-1 opacity-50 d-block mb-2"></i>
                            Chưa có giao dịch nào
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach($recentInvoices as $inv): ?>
                            <div class="list-group-item px-4 py-3 d-flex justify-content-between align-items-center hover-bg-light transition-all">
                                <div>
                                    <div class="fw-bold text-dark mb-1"><?= htmlspecialchars($inv['full_name'] ?? 'Khách lẻ') ?></div>
                                    <div class="small text-muted">
                                        <i class="bi bi-clock me-1"></i> <?= date('H:i - d/m', strtotime($inv['invoice_date'])) ?>
                                        <?php if(isset($inv['branch_name'])): ?>
                                            <span class="mx-1">•</span> <span class="badge bg-light text-secondary border"><?= htmlspecialchars($inv['branch_name']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="fw-bold text-success fs-6">
                                    +<?= number_format((float)$inv['total']) ?> ₫
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-white border-top text-center p-0">
                    <a href="/business/invoices" class="btn btn-link text-decoration-none fw-bold small text-primary w-100 py-3">Xem tất cả hóa đơn <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('revenueChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: <?= json_encode($chartData) ?>,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#0d6efd',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4    
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return new Intl.NumberFormat('vi-VN').format(context.raw) + ' ₫';
                            }
                        }
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true,
                        grid: { borderDash: [4, 4], color: '#e9ecef' },
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) return (value / 1000000) + ' Tr';
                                if (value >= 1000) return (value / 1000) + ' K';
                                return value;
                            }
                        }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    }
</script>
<?php $this->endSection() ?>