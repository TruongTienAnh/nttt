<div class="mb-3">
    <label class="form-label fw-bold small text-secondary">Tên khoản chi</label>
    <input type="text" id="edit-title" class="form-control rounded-3" value="<?= htmlspecialchars($expense['title']) ?>">
</div>
<div class="row g-3 mb-3">
    <div class="col-6">
        <label class="form-label fw-bold small text-secondary">Danh mục</label>
        <select id="edit-category" class="form-select rounded-3">
            <option value="salary" <?= $expense['category'] == 'salary' ? 'selected' : '' ?>>Lương / Thưởng</option>
            <option value="rent" <?= $expense['category'] == 'rent' ? 'selected' : '' ?>>Thuê mặt bằng</option>
            <option value="ads" <?= $expense['category'] == 'ads' ? 'selected' : '' ?>>Quảng cáo (Ads)</option>
            <option value="other" <?= $expense['category'] == 'other' ? 'selected' : '' ?>>Chi phí khác</option>
        </select>
    </div>
    <div class="col-6">
        <label class="form-label fw-bold small text-secondary">Ngày chi</label>
        <input type="date" id="edit-date" class="form-control rounded-3" value="<?= $expense['expense_date'] ?>">
    </div>
</div>
<div class="mb-3">
    <label class="form-label fw-bold small text-secondary">Số tiền (VNĐ)</label>
    <input type="number" id="edit-amount" class="form-control rounded-3 fw-bold text-danger" value="<?= round($expense['amount']) ?>">
</div>
<div class="mb-3">
    <label class="form-label fw-bold small text-secondary">Ghi chú</label>
    <textarea id="edit-note" class="form-control rounded-3" rows="2"><?= htmlspecialchars($expense['note']) ?></textarea>
</div>
<div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
    <button class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Hủy</button>
    <button class="btn btn-primary rounded-3 px-4 fw-bold" onclick="submitEdit(<?= $expense['id'] ?>)">Cập nhật</button>
</div>

<script>
function submitEdit(id) {
    fetch(`/business/expenses/${id}/update`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            title: document.getElementById('edit-title').value,
            category: document.getElementById('edit-category').value,
            amount: document.getElementById('edit-amount').value,
            expense_date: document.getElementById('edit-date').value,
            note: document.getElementById('edit-note').value,
        })
    }).then(r => r.json()).then(data => {
        if (data.status === 'success') location.reload();
        else alert(data.alert);
    });
}
</script>