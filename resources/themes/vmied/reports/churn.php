<?php $this->extend('layouts/app') ?>
<?php $this->section('content') ?>
<div class="container-fluid py-4 animate-fade-up">
    <div class="mb-4">
        <h2 class="fw-bold mb-1"><i class="bi bi-heart-pulse text-danger me-2"></i>Vòng đời (CLV) & Churn Rate</h2>
        <p class="text-secondary small">Cảnh báo và phân tích lượng khách hàng có nguy cơ rời bỏ hệ thống (>30 ngày chưa quay lại).</p>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <h6 class="fw-bold mb-4 text-dark text-center">Tỷ lệ Sức khỏe Khách hàng Toàn hệ thống</h6>
                <div style="height: 280px; display: flex; justify-content: center;"><canvas id="churnChart"></canvas></div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-header bg-danger bg-opacity-10 border-bottom-0 py-3 px-4 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>Danh sách Khách có nguy cơ Rời bỏ</span>
                    <input type="text" id="search-churn" class="form-control form-control-sm w-auto border-0 shadow-sm" placeholder="Tìm kiếm nhanh...">
                </div>
                <div class="table-responsive custom-scrollbar" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0" id="churn-table">
                        <thead class="bg-light sticky-top small fw-bold text-uppercase text-secondary">
                            <tr>
                                <th class="ps-4 border-bottom-0 py-3">Khách hàng</th>
                                <th class="border-bottom-0 py-3">Chi nhánh</th>
                                <th class="text-center border-bottom-0 py-3">Bỏ ngang</th>
                                <th class="text-end pe-4 border-bottom-0 py-3">LTV Bỏ lỡ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($lostCustomers)): ?>
                            <tr><td colspan="4" class="text-center py-4 text-muted">Hệ thống đang hoạt động rất tốt, không có khách hàng rời bỏ!</td></tr>
                            <?php else: ?>
                            <?php foreach($lostCustomers as $r): ?>
                            <tr class="searchable-row">
                                <td class="ps-4 py-3">
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($r['full_name']) ?></div>
                                    <div class="small text-muted font-monospace"><?= htmlspecialchars($r['phone'] ?? "") ?></div>
                                </td>
                                <td><span class="badge bg-light text-secondary border"><?= htmlspecialchars($r['branch_name'] ?? 'Chung') ?></span></td>
                                <td class="text-center text-danger fw-bold"><?= $r['r_days'] ?> ngày</td>
                                <td class="text-end pe-4 fw-bold text-secondary"><?= number_format($r['m_total']) ?> ₫</td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.getElementById('search-churn').addEventListener('input', function() {
        let q = this.value.toLowerCase();
        document.querySelectorAll('#churn-table .searchable-row').forEach(row => { row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none'; });
    });
    
    const churnCtx = document.getElementById('churnChart');
    if(churnCtx) {
        new Chart(churnCtx, {
            type: 'doughnut',
            data: {
                labels: ['Hoạt động (<30 ngày)', 'Rủi ro (30-60 ngày)', 'Ngủ đông (>60 ngày)'],
                datasets: [{
                    data: [<?= $churnData['Hoạt động'] ?>, <?= $churnData['Rủi ro'] ?>, <?= $churnData['Ngủ đông'] ?>],
                    backgroundColor: ['#2a9d8f', '#e9c46a', '#e63946'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } } },
                cutout: '75%'
            }
        });
    }
</script>
<?php $this->endSection() ?>