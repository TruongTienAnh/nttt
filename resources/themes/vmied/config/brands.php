<?php $this->extend('layouts/app') ?>
<?php $this->section('content') ?>
<div class="container-fluid py-4 animate-fade-up">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
        <div>
            <h1 class="h3 fw-bold mb-1">Chi Nhánh</h1>
            <p class="text-secondary mb-0 small">Quản lý các cơ sở và địa điểm kinh doanh</p>
        </div>
        
        <div class="d-flex align-items-center gap-2">
            <div class="input-group shadow-sm rounded-pill overflow-hidden" style="width: 280px; border: 1px solid var(--bs-border-color-translucent);">
                <span class="input-group-text bg-white border-0 text-secondary"><i class="bi bi-search"></i></span>
                <input type="text" id="brandSearchInput" class="form-control border-0 bg-white shadow-none ps-0" placeholder="Tìm tên, SĐT, loại...">
            </div>
            
            <button onclick="modal('/config/brands/create')" class="btn btn-primary rounded-pill px-4 shadow-sm text-nowrap">
                <i class="bi bi-plus-lg me-1"></i> Thêm chi nhánh
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive custom-scrollbar" style="max-height: 65vh; overflow-y: auto;">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light position-sticky top-0 shadow-sm" style="z-index: 1;">
                    <tr class="small text-secondary fw-bold text-uppercase">
                        <th class="ps-4 py-3 border-bottom-0">Tên chi nhánh</th>
                        <th class="py-3 border-bottom-0">Địa chỉ / Hotline</th>
                        <th class="py-3 text-center border-bottom-0">Trạng thái</th>
                        <th class="pe-4 py-3 text-end border-bottom-0">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($brands as $b): ?>
                    <tr class="searchable-row">
                        <td class="ps-4 py-3">
                            <div class="fw-bold text-dark"><?= htmlspecialchars($b['name']) ?></div>
                            <span class="badge bg-light text-secondary border px-2"><?= strtoupper($b['type'] ?? 'SPA') ?></span>
                        </td>
                        <td class="py-3">
                            <div class="small text-dark text-truncate" style="max-width: 250px;" title="<?= htmlspecialchars($b['address'] ?? '') ?>">
                                <?= htmlspecialchars($b['address'] ?? '') ?: '<span class="text-muted fst-italic">Chưa có địa chỉ</span>' ?>
                            </div>
                            <div class="small text-secondary fw-medium"><?= htmlspecialchars($b['phone'] ?? '') ?></div>
                        </td>
                        <td class="py-3 text-center">
                            <div class="form-check form-switch d-flex justify-content-center m-0">
                                <input class="form-check-input cursor-pointer shadow-none" type="checkbox" role="switch" 
                                       <?= $b['is_active'] == 1 ? 'checked' : '' ?>
                                       hx-post="/config/brands/<?= $b['active'] ?>/toggle" hx-swap="none">
                            </div>
                        </td>
                        <td class="pe-4 py-3 text-end">
                            <button onclick="modal('/config/brands/<?= $b['active'] ?>/edit')" class="btn btn-light btn-sm text-primary rounded-3">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button hx-post="/config/brands/<?= $b['active'] ?>/delete" 
                                    hx-confirm="Bạn có chắc chắn muốn xóa chi nhánh này?" 
                                    hx-swap="none" @htmx:after-request="handleResponse" 
                                    class="btn btn-light btn-sm text-danger rounded-3 ms-1">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Script Tìm kiếm chuẩn xác (Dùng TextContent & ClassList)
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('brandSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            var q = this.value.toLowerCase().trim();
            document.querySelectorAll('.searchable-row').forEach(function(row) {
                var text = row.textContent.toLowerCase();
                if (text.includes(q)) { 
                    row.classList.remove('d-none'); 
                } else { 
                    row.classList.add('d-none'); 
                }
            });
        });
    }
});
</script>
<?php $this->endSection() ?>