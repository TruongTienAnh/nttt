<div class="mb-3">
    <label class="form-label fw-bold small text-uppercase text-secondary">Tên chi nhánh <span class="text-danger">*</span></label>
    <input type="text" id="edit-name" class="form-control rounded-3" value="<?= htmlspecialchars($brand->name) ?>">
</div>
<div class="mb-3">
    <label class="form-label fw-bold small text-uppercase text-secondary">Địa chỉ</label>
    <input type="text" id="edit-address" class="form-control rounded-3" value="<?= htmlspecialchars($brand->address ?? '') ?>">
</div>
<div class="row g-3 mb-3">
    <div class="col-6">
        <label class="form-label fw-bold small text-uppercase text-secondary">Số điện thoại</label>
        <input type="text" id="edit-phone" class="form-control rounded-3" value="<?= htmlspecialchars($brand->phone ?? '') ?>">
    </div>
    <div class="col-6">
        <label class="form-label fw-bold small text-uppercase text-secondary">Loại</label>
        <select id="edit-type" class="form-select rounded-3">
            <option value="spa"    <?= ($brand->type ?? '') === 'spa'    ? 'selected' : '' ?>>Spa</option>
            <option value="retail" <?= ($brand->type ?? '') === 'gun' ? 'selected' : '' ?>>Gun</option>
        </select>
    </div>
</div>
<div class="mb-3">
    <label class="form-label fw-bold small text-uppercase text-secondary">Trạng thái</label>
    <select id="edit-status" class="form-select rounded-3">
        <option value="1" <?= ($brand->is_active ?? 0) == 1 ? 'selected' : '' ?>>Hoạt động</option>
        <option value="0" <?= ($brand->is_active ?? 0) == 0 ? 'selected' : '' ?>>Tắt</option>
    </select>
</div>
<div class="modal-footer border-0 px-0 pb-0 gap-2">
    <button class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Hủy</button>
    <button class="btn btn-primary rounded-3 px-4 fw-bold" onclick="submitEdit(<?= $brand->id ?>)">
        <i data-lucide="save" width="16" class="me-1"></i> Cập nhật
    </button>
</div>

<script>
function submitEdit(id) {
    const name = document.getElementById('edit-name').value.trim();
    if (!name) { alert('Vui lòng nhập tên chi nhánh'); return; }

    fetch(`/config/brands/${id}/update`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            name:      name,
            address:   document.getElementById('edit-address').value,
            phone:     document.getElementById('edit-phone').value,
            type:      document.getElementById('edit-type').value,
            is_active: document.getElementById('edit-status').value,
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') location.reload();
        else alert(data.alert);
    });
}
</script>