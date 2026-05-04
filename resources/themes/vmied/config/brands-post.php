<div class="modal fade modal-load" id="modal-brand" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header p-4 border-bottom-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-geo-alt me-2 text-primary"></i>
                    <?= $brand ? 'Cập nhật chi nhánh' : 'Thêm chi nhánh mới' ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 pt-0">
                <form hx-post="<?= $brand ? '/config/brands/'.$brand['active'].'/update' : '/config/brands/store' ?>" 
                      hx-swap="none" @htmx:after-request="handleResponse">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Tên chi nhánh</label>
                        <input type="text" name="name" class="form-control rounded-3" value="<?= $brand['name'] ?? '' ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Địa chỉ</label>
                        <input type="text" name="address" class="form-control rounded-3" value="<?= $brand['address'] ?? '' ?>">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Số điện thoại</label>
                            <input type="text" name="phone" class="form-control rounded-3" value="<?= $brand['phone'] ?? '' ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Loại hình</label>
                            <select name="type" class="form-select rounded-3">
                                <option value="spa" <?= (isset($brand['type']) && $brand['type'] == 'spa') ? 'selected' : '' ?>>Spa / Clinic</option>
                                <option value="retail" <?= (isset($brand['type']) && $brand['type'] == 'retail') ? 'selected' : '' ?>>Retail (Bán lẻ)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top text-end">
                        <button type="button" class="btn btn-light px-4 me-2 rounded-pill" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary px-5 rounded-pill shadow-sm fw-bold">Lưu lại</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>