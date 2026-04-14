<?php $this->extend('layouts/app') ?>

<?php $this->section('content') ?>
<main class="container py-5 mt-5 animate-fade-up">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-3 py-2 mb-2">
                <i class="bi bi-people-fill me-1"></i> Quản lý Kinh doanh
            </span>
            <h1 class="display-6 fw-bolder text-dark mb-1">Khách hàng (Từ Haravan)</h1>
            <p class="text-secondary mb-0">Dữ liệu được đồng bộ tự động, chỉ hỗ trợ xem chi tiết.</p>
        </div>
    </div>

    <div class="glass-card rounded-4 overflow-hidden shadow-sm border">
        <div class="d-flex align-items-center justify-content-between px-4 py-3 bg-light border-bottom">
            <span class="fw-bold text-dark">Danh sách Khách hàng</span>
            <div class="input-group" style="max-width: 300px;">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-secondary"></i></span>
                <input type="text" id="customerSearchInput" class="form-control border-start-0 shadow-none" placeholder="Tìm tên, SĐT...">
            </div>
        </div>

        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-white sticky-top text-secondary small fw-bold text-uppercase" style="top:0; z-index:1;">
                    <tr>
                        <th class="px-4 py-3">Khách hàng</th>
                        <th class="px-4 py-3">Số điện thoại</th>
                        <th class="px-4 py-3">Ngày đăng ký</th>
                        <th class="px-4 py-3 text-end">Chi tiết</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($customers)): ?>
                    <tr><td colspan="4" class="text-center py-5 text-secondary">Chưa có dữ liệu.</td></tr>
                    <?php else: ?>
                    <?php foreach ($customers as $c): ?>
                    <tr class="searchable-row">
                        <td class="px-4 py-3 fw-bold text-dark"><?= htmlspecialchars($c['full_name'] ?? 'Khách lẻ') ?></td>
                        <td class="px-4 py-3 text-secondary font-monospace"><?= htmlspecialchars($c['phone'] ?? '') ?></td>
                        <td class="px-4 py-3 text-secondary"><?= date('d/m/Y', strtotime($c['created_at'])) ?></td>
                        <td class="px-4 py-3 text-end">
                            <button class="btn btn-sm btn-light border rounded-3 px-3"
                                    hx-get="/business/customers/<?= $c['id'] ?>/show"
                                    hx-target="#modalShowBody"
                                    hx-swap="innerHTML"
                                    data-bs-toggle="modal" data-bs-target="#modalShow" title="Xem chi tiết">
                                <i class="bi bi-eye"></i> Xem
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<div class="modal fade" id="modalShow" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <h5 class="modal-title fw-bolder text-dark">Hồ sơ Khách hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div id="modalShowBody" class="modal-body px-4 py-3">
                <div class="text-center py-4 text-secondary"><div class="spinner-border spinner-border-sm me-2"></div> Đang tải...</div>
            </div>
        </div>
    </div>
</div>

<script>
// Script Tìm kiếm chuẩn xác (Dùng TextContent & ClassList)
var searchInput = document.getElementById('customerSearchInput');
if (searchInput) {
    searchInput.addEventListener('input', function () {
        var q = this.value.toLowerCase().trim();
        document.querySelectorAll('.searchable-row').forEach(function(row) {
            var text = row.textContent.toLowerCase();
            if (text.includes(q)) { row.classList.remove('d-none'); } 
            else { row.classList.add('d-none'); }
        });
    });
}
</script>
<?php $this->endSection() ?>