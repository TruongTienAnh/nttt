<?php $this->extend('layouts/app') ?>
<?php $this->section('content') ?>
<div class="container-fluid py-4 animate-fade-up">
    <div class="mb-4">
        <h2 class="fw-bold mb-1"><i class="bi bi-cart-plus text-warning me-2"></i>Cross-sell Hệ sinh thái</h2>
        <p class="text-secondary small">Phân tích hành vi mua kèm (Basket Analysis) để đưa ra chiến lược Combo/Upsell hợp lý.</p>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
            <div class="text-dark small fw-medium">
                Top 20 cặp sản phẩm/dịch vụ có tỷ lệ mua chung cao nhất hệ thống.
            </div>
            <input type="text" id="search-cross-sell" class="form-control form-control-sm w-auto bg-light border-0 shadow-sm" placeholder="Tìm kiếm dịch vụ...">
        </div>
        
        <div class="table-responsive custom-scrollbar" style="max-height: 600px; overflow-y: auto;">
            <table class="table table-hover align-middle mb-0" id="cross-sell-table">
                <thead class="bg-light sticky-top small fw-bold text-uppercase text-secondary">
                    <tr>
                        <th class="ps-4 py-3 border-bottom-0">Dịch vụ A</th>
                        <th class="text-center py-3 border-bottom-0" style="width: 80px;"><i class="bi bi-link-45deg fs-5"></i></th>
                        <th class="py-3 border-bottom-0">Dịch vụ B (Mua kèm)</th>
                        <th class="text-end pe-4 py-3 border-bottom-0">Số lượt mua chung</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($crossSell)): ?>
                    <tr><td colspan="4" class="text-center py-5 text-muted">Chưa có đủ dữ liệu hóa đơn để phân tích mua kèm.</td></tr>
                    <?php else: ?>
                    <?php foreach($crossSell as $c): ?>
                    <tr class="searchable-row">
                        <td class="ps-4 py-3 text-dark fw-bold" style="width: 40%;"><?= htmlspecialchars($c['p1']) ?></td>
                        <td class="text-center text-primary opacity-50"><i class="bi bi-plus-lg"></i></td>
                        <td class="py-3 text-dark fw-bold" style="width: 40%;"><?= htmlspecialchars($c['p2']) ?></td>
                        <td class="text-end pe-4">
                            <span class="badge bg-warning text-dark rounded-pill px-3 py-2 fw-bold fs-6">
                                <?= $c['freq'] ?> lượt
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
document.getElementById('search-cross-sell').addEventListener('input', function() {
    let q = this.value.toLowerCase();
    document.querySelectorAll('#cross-sell-table .searchable-row').forEach(row => { row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none'; });
});
</script>
<?php $this->endSection() ?>