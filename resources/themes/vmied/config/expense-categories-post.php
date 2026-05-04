<div class="modal fade modal-load" id="expenseCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            
            <div class="modal-header bg-light border-bottom-0 p-4 pb-3">
                <h5 class="modal-title fw-bold text-dark">
                    <?= isset($category) ? 'Cập nhật Danh mục' : 'Thêm Danh mục Mới' ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 pt-2">
                <div x-data="Form()">
                    <form 
                        hx-post="/config/expense-categories-post" 
                        hx-swap="none"
                        @htmx:before-request="startRequest()"
                        @htmx:after-request="handleResponse($event)"
                    >
                        <input type="hidden" name="id" value="<?= $category['id'] ?? '' ?>">

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small mb-2">Tên danh mục <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control bg-light border-0 rounded-3 py-2 px-3" 
                                   value="<?= htmlspecialchars($category['name'] ?? '') ?>" placeholder="Ví dụ: Tiền điện..." required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small mb-2">Mô tả thêm</label>
                            <textarea name="description" class="form-control bg-light border-0 rounded-3 py-2 px-3" rows="3"><?= htmlspecialchars($category['description'] ?? '') ?></textarea>
                        </div>

                        <div class="form-check form-switch p-3 bg-light rounded-3 border-0 mt-3">
                            <input class="form-check-input ms-0 me-3 cursor-pointer" type="checkbox" name="is_active" 
                                   id="statusSwitch" style="width: 2.5rem; height: 1.25rem;"
                                   <?= (!isset($category) || $category['is_active'] == 1) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold text-dark small cursor-pointer" for="statusSwitch">
                                Kích hoạt danh mục này
                            </label>
                        </div>

                        <div class="mt-4 d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-light fw-medium rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" :disabled="isLoading" class="btn btn-primary fw-bold rounded-pill px-4 shadow-sm d-flex align-items-center gap-2">
                                <span x-show="isLoading" class="spinner-border spinner-border-sm text-light" style="display: none;"></span>
                                <span x-text="isLoading ? 'Đang lưu...' : 'Lưu dữ liệu'">Lưu dữ liệu</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>