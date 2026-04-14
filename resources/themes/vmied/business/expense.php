<?php $this->extend('layouts/app') ?>

<?php $this->section('content') ?>
<main class="container py-5 mt-5 animate-fade-up">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill px-3 py-2 mb-2">
                <i data-lucide="wallet" width="14" class="me-1"></i> Quản lý Tài chính
            </span>
            <h1 class="display-6 fw-bolder text-dark mb-1">Nhập liệu Chi phí</h1>
            <p class="text-secondary mb-0">Quản lý chi phí tiền lương, mặt bằng, quảng cáo.</p>
        </div>
        <button class="btn btn-primary fw-bold rounded-4 px-4 py-2 shadow-sm d-flex align-items-center gap-2"
                data-bs-toggle="modal" data-bs-target="#modalAdd">
            <i data-lucide="plus" width="18"></i> Thêm khoản chi
        </button>
    </div>

    <div class="row g-3 mb-4 text-center">
        <div class="col-md-3">
            <div class="glass-card p-3 rounded-4 border-start border-4 border-danger shadow-sm">
                <div class="text-secondary small fw-bold">TỔNG CHI THÁNG NÀY</div>
                <div class="h4 mb-0 fw-bold text-danger"><?= number_format($summary['total']) ?> ₫</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card p-3 rounded-4 border-start border-4 border-info shadow-sm">
                <div class="text-secondary small fw-bold">LƯƠNG NHÂN SỰ</div>
                <div class="h4 mb-0 fw-bold"><?= number_format($summary['salary']) ?> ₫</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card p-3 rounded-4 border-start border-4 border-warning shadow-sm">
                <div class="text-secondary small fw-bold">THUÊ MẶT BẰNG</div>
                <div class="h4 mb-0 fw-bold"><?= number_format($summary['rent']) ?> ₫</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card p-3 rounded-4 border-start border-4 border-success shadow-sm">
                <div class="text-secondary small fw-bold">QUẢNG CÁO (ADS)</div>
                <div class="h4 mb-0 fw-bold"><?= number_format($summary['ads']) ?> ₫</div>
            </div>
        </div>
    </div>

    <div class="glass-card rounded-5 overflow-hidden shadow-sm border">
        <div class="d-flex align-items-center justify-content-between px-4 py-3 bg-light border-bottom">
            <span class="fw-bold text-dark">Lịch sử chi phí</span>
            <div class="input-group" style="max-width: 260px;">
                <span class="input-group-text bg-white border-end-0">
                    <i data-lucide="search" width="16" class="text-secondary"></i>
                </span>
                <input type="text" id="searchInput" class="form-control border-start-0 shadow-none" placeholder="Tìm kiếm...">
            </div>
        </div>

        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light sticky-top text-secondary small fw-bold text-uppercase" style="top:0; z-index:1;">
                    <tr>
                        <th class="px-4 py-3">Ngày chi</th>
                        <th class="px-4 py-3">Hạng mục</th>
                        <th class="px-4 py-3">Tên khoản chi</th>
                        <th class="px-4 py-3 text-end">Số tiền</th>
                        <th class="px-4 py-3">Ghi chú</th>
                        <th class="px-4 py-3 text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($expenses)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-secondary">
                            <i data-lucide="inbox" width="40" class="d-block mx-auto mb-2 opacity-30"></i> Chưa có dữ liệu
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($expenses as $e): ?>
                    <tr class="searchable-row">
                        <td class="px-4 py-3 fw-bold text-dark"><?= date('d/m/Y', strtotime($e['expense_date'])) ?></td>
                        <td class="px-4 py-3">
                            <?php 
                                $catMap = ['salary' => ['Lương thưởng', 'info'], 'rent' => ['Mặt bằng', 'warning'], 'ads' => ['Quảng cáo', 'success'], 'other' => ['Khác', 'secondary']];
                                $cat = $catMap[$e['category']] ?? ['Khác', 'secondary'];
                            ?>
                            <span class="badge bg-<?= $cat[1] ?> bg-opacity-10 text-<?= $cat[1] ?> border border-<?= $cat[1] ?>-subtle rounded-pill px-3">
                                <?= $cat[0] ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 fw-semibold text-dark"><?= htmlspecialchars($e['title']) ?></td>
                        <td class="px-4 py-3 text-end fw-bold text-danger">-<?= number_format($e['amount']) ?> ₫</td>
                        <td class="px-4 py-3 text-secondary small text-truncate" style="max-width: 150px;"><?= htmlspecialchars($e['note']) ?></td>
                        <td class="px-4 py-3 text-end">
                            <div class="d-flex align-items-center justify-content-end gap-2">
                                <button class="btn btn-sm btn-light border rounded-3 px-3"
                                        hx-get="/business/expenses/<?= $e['id'] ?>/edit"
                                        hx-target="#modalEditBody"
                                        hx-swap="innerHTML"
                                        data-bs-toggle="modal" data-bs-target="#modalEdit" title="Sửa">
                                    <i data-lucide="pencil" width="15"></i>
                                </button>
                                <button class="btn btn-sm btn-light border rounded-3 px-3 text-danger"
                                        onclick="confirmDelete(<?= $e['id'] ?>, '<?= htmlspecialchars($e['title']) ?>')" title="Xóa">
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

<div class="modal fade" id="modalAdd" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-5 border-0 shadow-lg">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <h5 class="modal-title fw-bolder text-dark">Thêm khoản chi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-3">
                <div class="mb-3">
                    <label class="form-label fw-bold small text-secondary">Tên khoản chi <span class="text-danger">*</span></label>
                    <input type="text" id="add-title" class="form-control rounded-3" placeholder="VD: Tiền điện tháng 10...">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-bold small text-secondary">Danh mục</label>
                        <select id="add-category" class="form-select rounded-3">
                            <option value="salary">Lương / Thưởng</option>
                            <option value="rent">Thuê mặt bằng</option>
                            <option value="ads">Quảng cáo (Ads)</option>
                            <option value="other" selected>Chi phí khác</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold small text-secondary">Ngày chi <span class="text-danger">*</span></label>
                        <input type="date" id="add-date" class="form-control rounded-3" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small text-secondary">Số tiền (VNĐ) <span class="text-danger">*</span></label>
                    <input type="number" id="add-amount" class="form-control rounded-3 fw-bold text-danger" placeholder="0">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small text-secondary">Ghi chú</label>
                    <textarea id="add-note" class="form-control rounded-3" rows="2" placeholder="Ghi chú thêm..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 pt-0 gap-2">
                <button class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Hủy</button>
                <button class="btn btn-primary rounded-3 px-4 fw-bold" onclick="submitAdd()">Lưu</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-5 border-0 shadow-lg">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <h5 class="modal-title fw-bolder text-dark">Sửa khoản chi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div id="modalEditBody" class="modal-body px-4 py-3">
                <div class="text-center py-4 text-secondary"><div class="spinner-border spinner-border-sm me-2"></div> Đang tải...</div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDelete" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-5 border-0 shadow-lg">
            <div class="modal-body text-center px-4 py-4">
                <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:60px;height:60px;">
                    <i data-lucide="trash-2" width="28"></i>
                </div>
                <h5 class="fw-bolder text-dark mb-1">Xóa khoản chi?</h5>
                <p class="text-secondary small mb-4">Bạn sắp xóa <strong id="deleteTitle"></strong>.</p>
                <div class="d-flex gap-2 justify-content-center">
                    <button class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Hủy</button>
                    <button class="btn btn-danger rounded-3 px-4 fw-bold" id="btnConfirmDelete">Xóa</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Logic Search chuẩn
var searchInput = document.getElementById('searchInput');
if (searchInput) {
    searchInput.addEventListener('input', function () {
        var q = this.value.toLowerCase().trim();
        document.querySelectorAll('.searchable-row').forEach(function(row) {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
}

// Thêm
function submitAdd() {
    const title = document.getElementById('add-title').value.trim();
    const amount = document.getElementById('add-amount').value;
    const date = document.getElementById('add-date').value;

    if (!title || !amount || !date) { alert('Vui lòng nhập đủ các trường bắt buộc'); return; }

    fetch('/business/expenses/store', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            title: title,
            category: document.getElementById('add-category').value,
            amount: amount,
            expense_date: date,
            note: document.getElementById('add-note').value,
        })
    }).then(r => r.json()).then(data => {
        if (data.status === 'success') location.reload();
        else alert(data.alert);
    });
}

// Xóa
function confirmDelete(id, title) {
    document.getElementById('deleteTitle').textContent = title;
    document.getElementById('btnConfirmDelete').onclick = function () {
        fetch(`/business/expenses/${id}/delete`, { method: 'POST' })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') location.reload();
            else alert(data.alert);
        });
    };
    new bootstrap.Modal(document.getElementById('modalDelete')).show();
}

// Re-render Lucide icons sau khi load xong hoặc dùng HTMX
if (typeof lucide !== 'undefined') { lucide.createIcons(); }
</script>
<?php $this->endSection() ?>