<?php $this->extend('layouts/app') ?>
<?php $this->section('content') ?>
<div class="container-fluid py-3 animate-fade-up">
    <div class="mb-3 d-flex align-items-center gap-2">
        <i class="bi bi-cart-plus fs-4 text-warning"></i> <h4 class="mb-0 fw-bold">Báo cáo Cross-sell Hệ sinh thái</h4>
    </div>

    <div class="clean-card shadow-sm border-0">
        <div class="p-4 border-bottom">
            Dưới đây là top 20 cặp sản phẩm thường được khách hàng mua chung với nhau. Bạn có thể dựa vào đây để lên kế hoạch khuyến mãi mua kèm hoặc sắp xếp sản phẩm trên kệ cho hợp lý hơn.
        </div>
        <div class="p-3 border-bottom">
            <input type="text" id="search-cross-sell" class="form-control" placeholder="Tìm kiếm cặp sản phẩm...">
        </div>
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <table class="table table-hover align-middle mb-0 fs-7" id="cross-sell-table">
                <thead class="table-light sticky-top">
                    <tr class="text-uppercase small fw-bold">
                        <th class="ps-4 py-3">Sản phẩm A</th>
                        <th class="text-center py-3">Mua kèm</th>
                        <th class="py-3">Sản phẩm B</th>
                        <th class="text-center pe-4 py-3">Số khách mua chung</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($crossSell as $c): ?>
                    <tr class="searchable-row">
                        <td class="ps-4 py-3 text-dark"><b><?= htmlspecialchars($c['p1']) ?></b></td>
                        <td class="text-center text-primary"><i class="bi bi-arrow-left-right fs-5"></i></td>
                        <td class="py-3 text-dark"><b><?= htmlspecialchars($c['p2']) ?></b></td>
                        <td class="text-center pe-4">
                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2">
                                <?= $c['freq'] ?> khách
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
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
