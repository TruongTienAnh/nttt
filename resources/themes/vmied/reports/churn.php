<?php $this->extend('layouts/app') ?>
<?php $this->section('content') ?>
<div class="container-fluid py-3 animate-fade-up">
    <div class="mb-3 d-flex align-items-center gap-2">
        <i class="bi bi-heart-pulse fs-4 text-danger"></i> <h4 class="mb-0 fw-bold">Phân tích vòng đời (CLV) & Churn Rate</h4>
    </div>

    <div class="row g-3">
        <div class="col-md-5">
            <div class="clean-card p-4 h-100 shadow-sm border-0 text-center">
                <h6 class="fw-bold mb-4 text-start">Phân bổ sức khỏe khách hàng</h6>
                <div style="height: 250px;"><canvas id="churnChart"></canvas></div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="clean-card shadow-sm h-100 border-0">
                <div class="p-3 border-bottom fw-bold text-danger bg-white">
                    <i class="bi bi-exclamation-triangle me-1"></i> Khách sắp rời bỏ (>30 ngày)
                </div>
                <div class="p-3 border-bottom">
                    <input type="text" id="search-churn" class="form-control" placeholder="Tìm kiếm khách hàng...">
                </div>
                <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0 fs-7" id="churn-table">
                        <thead class="table-light sticky-top">
                            <tr class="text-uppercase small fw-bold">
                                <th class="ps-4">Khách hàng</th>
                                <th class="text-center">Thời gian nghỉ</th>
                                <th class="text-end pe-4">Tổng chi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($lostCustomers as $r): ?>
                            <tr class="searchable-row">
                                <td class="ps-4 py-3"><b><?= htmlspecialchars($r['full_name']) ?></b></td>
                                <td class="text-center text-danger fw-bold"><?= $r['r_days'] ?> ngày</td>
                                <td class="text-end pe-4 fw-bold text-accent"><?= number_format($r['m_total']) ?>đ</td>
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
    document.getElementById('search-churn').addEventListener('input', function() {
        let q = this.value.toLowerCase();
        document.querySelectorAll('#churn-table .searchable-row').forEach(row => { row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none'; });
    });
    // Logic render biểu đồ dựa trên $churnData của bạn...
    const churnCtx = document.getElementById('churnChart');
    if(churnCtx) {
        new Chart(churnCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_column($churnData, 'churn_group')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($churnData, 'count')) ?>,
                    backgroundColor: ['#2a9d8f', '#e9c46a', '#f25f5c']
                }]
            }
        });
    }
</script>
<?php $this->endSection() ?>