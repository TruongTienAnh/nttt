<div class="modal fade modal-load" id="expenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            
            <div class="modal-header bg-light border-bottom-0 p-4 pb-3">
                <h5 class="modal-title fw-bold text-dark">
                    <?= isset($expense) ? 'Sửa khoản chi' : 'Thêm khoản chi' ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 pt-2">
                <div x-data="Form()">
                    <form 
                        hx-post="<?= isset($expense) ? '/business/expenses/'.$expense['id'].'/update' : '/business/expenses/store' ?>" 
                        hx-swap="none"
                        @htmx:before-request="startRequest()"
                        @htmx:after-request="handleResponse($event)"
                    >
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Tên khoản chi <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control rounded-3" value="<?= isset($expense) ? htmlspecialchars($expense['title']) : '' ?>" placeholder="VD: Tiền điện tháng 10..." required>
                        </div>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold small text-secondary">Danh mục</label>
                                <select name="category" class="form-select rounded-3" required>
                                    <option value="" disabled <?= !isset($expense) ? 'selected' : '' ?>>Chọn danh mục</option>
                                    <?php foreach($expenseCategories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" <?= (isset($expense) && $expense['category'] == $cat['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold small text-secondary">Ngày chi <span class="text-danger">*</span></label>
                                <input type="date" name="expense_date" class="form-control rounded-3" value="<?= isset($expense) ? $expense['expense_date'] : date('Y-m-d') ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Số tiền (VNĐ) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control rounded-3 fw-bold text-danger" placeholder="0" value="<?= isset($expense) ? round($expense['amount']) : '' ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Ghi chú</label>
                            <textarea name="note" class="form-control rounded-3" rows="2" placeholder="Ghi chú thêm..."><?= isset($expense) ? htmlspecialchars($expense['note']) : '' ?></textarea>
                        </div>
                        
                        <?php if (isset($currentBranchId) && $currentBranchId === 'all'): ?>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Thuộc chi nhánh</label>
                            <select name="branch_id" class="form-select rounded-3 border-primary shadow-sm bg-primary bg-opacity-10">
                                <option value="">-- Chi phí chung Tổng công ty --</option>
                                <?php foreach($branches as $b): ?>
                                    <option value="<?= $b['id'] ?>" <?= (isset($expense) && $expense['branch_id'] == $b['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($b['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>

                        <div class="mt-4 pt-3 border-top d-flex gap-2 justify-content-end">
                            <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" :disabled="isLoading" class="btn btn-primary fw-bold rounded-3 px-4 shadow-sm d-flex align-items-center gap-2">
                                <span x-show="isLoading" class="spinner-border spinner-border-sm text-light" style="display: none;"></span>
                                <span x-text="isLoading ? 'Đang lưu...' : '<?= isset($expense) ? 'Cập nhật' : 'Lưu dữ liệu' ?>'"><?= isset($expense) ? 'Cập nhật' : 'Lưu dữ liệu' ?></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>