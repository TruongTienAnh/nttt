<?php $this->extend('layouts/app') ?>

<?php $this->section('content') ?>
<main class="container py-5 mt-5">

    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-3 py-2 mb-2">
                <i data-lucide="git-branch" width="14" class="me-1"></i> Quản lý hệ thống
            </span>
            <h1 class="display-6 fw-bolder text-dark mb-1">Chi nhánh</h1>
            <p class="text-secondary mb-0">Quản lý danh sách chi nhánh trong hệ thống.</p>
        </div>
        <button class="btn btn-primary fw-bold rounded-4 px-4 py-2 shadow-sm d-flex align-items-center gap-2"
                data-bs-toggle="modal" data-bs-target="#modalAdd">
            <i data-lucide="plus" width="18"></i> Thêm chi nhánh
        </button>
    </div>

    <!-- Table Card -->
    <div class="glass-card rounded-5 overflow-hidden shadow-sm border">
        <div class="d-flex align-items-center justify-content-between px-4 py-3 bg-light border-bottom">
            <span class="fw-bold text-dark">
                Tổng: <span class="text-primary"><?= count($brands) ?></span> chi nhánh
            </span>
            <div class="input-group" style="max-width: 260px;">
                <span class="input-group-text bg-white border-end-0">
                    <i data-lucide="search" width="16" class="text-secondary"></i>
                </span>
                <input type="text" id="searchInput" class="form-control border-start-0 shadow-none"
                       placeholder="Tìm kiếm...">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="branchTable">
                <thead class="bg-light text-secondary small fw-bold text-uppercase">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Tên chi nhánh</th>
                        <th class="px-4 py-3">Địa chỉ</th>
                        <th class="px-4 py-3">Số điện thoại</th>
                        <th class="px-4 py-3">Loại</th>
                        <th class="px-4 py-3 text-center">Trạng thái</th>
                        <th class="px-4 py-3 text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($brands)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-secondary">
                            <i data-lucide="inbox" width="40" class="d-block mx-auto mb-2 opacity-30"></i>
                            Chưa có chi nhánh nào
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($brands as $i => $branch): ?>
                    <tr class="branch-row">
                        <td class="px-4 py-3 text-secondary font-monospace small"><?= $i + 1 ?></td>
                        <td class="px-4 py-3">
                            <div class="fw-bold text-dark"><?= htmlspecialchars($branch['name']) ?></div>
                        </td>
                        <td class="px-4 py-3 text-secondary small"><?= htmlspecialchars($branch['address'] ?? '-') ?></td>  
                        <td class="px-4 py-3 text-secondary small"><?= htmlspecialchars($branch['phone'] ?? '-') ?></td>
                        <td class="px-4 py-3">
                            <?php
                                $typeMap = ['spa' => 'primary', 'retail' => 'warning', 'hybrid' => 'info'];
                                $typeColor = $typeMap[$branch['type']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $typeColor ?> bg-opacity-10 text-<?= $typeColor ?> border border-<?= $typeColor ?>-subtle rounded-pill px-3">
                                <?= ucfirst($branch['type'] ?? '-') ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button class="btn btn-sm rounded-pill px-3 fw-bold border-0
                                <?= $branch['is_active'] ? 'bg-success bg-opacity-10 text-success' : 'bg-secondary bg-opacity-10 text-secondary' ?>"
                                hx-post="/config/brands/<?= $branch['id'] ?>/toggle-status"
                                hx-target="closest tr"
                                hx-swap="outerHTML"
                                title="Nhấn để đổi trạng thái">
                                <?= $branch['is_active'] ? 'Hoạt động' : 'Tắt' ?>
                            </button>
                        </td>
                        <td class="px-4 py-3 text-end">
                            <div class="d-flex align-items-center justify-content-end gap-2">
                                <button class="btn btn-sm btn-light border rounded-3 px-3"
                                        hx-get="/config/brands/<?= $branch['id'] ?>/edit"
                                        hx-target="#modalEditBody"
                                        hx-swap="innerHTML"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalEdit"
                                        title="Sửa">
                                    <i data-lucide="pencil" width="15"></i>
                                </button>
                                <button class="btn btn-sm btn-light border rounded-3 px-3 text-danger"
                                        onclick="confirmDelete(<?= $branch['id'] ?>, '<?= htmlspecialchars($branch['name']) ?>')"
                                        title="Xóa">
                                    <i data-lucide="trash-2" width="15"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Modal: Thêm chi nhánh -->
<div class="modal fade" id="modalAdd" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-5 border-0 shadow-lg">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <h5 class="modal-title fw-bolder text-dark">Thêm chi nhánh</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-3">
                <div class="mb-3">
                    <label class="form-label fw-bold small text-uppercase text-secondary">Tên chi nhánh <span class="text-danger">*</span></label>
                    <input type="text" id="add-name" class="form-control rounded-3" placeholder="Nhập tên chi nhánh...">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small text-uppercase text-secondary">Địa chỉ</label>
                    <input type="text" id="add-address" class="form-control rounded-3" placeholder="Nhập địa chỉ...">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-bold small text-uppercase text-secondary">Số điện thoại</label>
                        <input type="text" id="add-phone" class="form-control rounded-3" placeholder="0909...">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold small text-uppercase text-secondary">Loại</label>
                        <select id="add-type" class="form-select rounded-3">
                            <option value="spa">Spa</option>
                            <option value="retail">Retail</option>
                            <option value="hybrid">Hybrid</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small text-uppercase text-secondary">Trạng thái</label>
                    <select id="add-status" class="form-select rounded-3">
                        <option value="1">Hoạt động</option>
                        <option value="0">Tắt</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 pt-0 gap-2">
                <button class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Hủy</button>
                <button class="btn btn-primary rounded-3 px-4 fw-bold" onclick="submitAdd()">
                    <i data-lucide="save" width="16" class="me-1"></i> Lưu
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Sửa chi nhánh -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-5 border-0 shadow-lg">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <h5 class="modal-title fw-bolder text-dark">Sửa chi nhánh</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div id="modalEditBody" class="modal-body px-4 py-3">
                <div class="text-center py-4 text-secondary">
                    <div class="spinner-border spinner-border-sm me-2"></div> Đang tải...
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Xác nhận xóa -->
<div class="modal fade" id="modalDelete" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-5 border-0 shadow-lg">
            <div class="modal-body text-center px-4 py-4">
                <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:60px;height:60px;">
                    <i data-lucide="trash-2" width="28"></i>
                </div>
                <h5 class="fw-bolder text-dark mb-1">Xóa chi nhánh?</h5>
                <p class="text-secondary small mb-4">Bạn sắp xóa <strong id="deleteName"></strong>. Hành động này không thể hoàn tác.</p>
                <div class="d-flex gap-2 justify-content-center">
                    <button class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Hủy</button>
                    <button class="btn btn-danger rounded-3 px-4 fw-bold" id="btnConfirmDelete">Xóa</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Search
document.getElementById('searchInput').addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.branch-row').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});

// Thêm chi nhánh
function submitAdd() {
    const name = document.getElementById('add-name').value.trim();
    if (!name) { alert('Vui lòng nhập tên chi nhánh'); return; }

    fetch('/config/brands/store', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            name:      name,
            address:   document.getElementById('add-address').value,
            phone:     document.getElementById('add-phone').value,
            type:      document.getElementById('add-type').value,
            is_active: document.getElementById('add-status').value,
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            location.reload();
        } else {
            alert(data.alert);
        }
    });
}

// Xóa chi nhánh
function confirmDelete(id, name) {
    document.getElementById('deleteName').textContent = name;
    document.getElementById('btnConfirmDelete').onclick = function () {
        fetch(`/config/brands/${id}/delete`, { method: 'POST' })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') location.reload();
            else alert(data.alert);
        });
    };
    new bootstrap.Modal(document.getElementById('modalDelete')).show();
}
</script>
<?php $this->endSection() ?>