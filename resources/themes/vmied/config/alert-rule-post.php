<div class="modal fade modal-load" id="alertModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            
            <div class="modal-header bg-light border-bottom-0 p-4 pb-3">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="bi bi-magic text-warning me-2"></i><?= isset($rule) ? 'Sửa quy tắc cảnh báo' : 'Thiết lập Cảnh báo mới' ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 pt-2" x-data="Form()">
                <form hx-post="<?= isset($rule) ? '/config/alerts/'.$rule['id'].'/update' : '/config/alerts/store' ?>" 
                      hx-swap="none" @htmx:before-request="startRequest()" @htmx:after-request="handleResponse($event)">
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-secondary">Tên cảnh báo <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control rounded-3" value="<?= htmlspecialchars($rule['title'] ?? '') ?>" placeholder="VD: Báo động lạm chi phí mặt bằng..." required>
                    </div>

                    <div class="bg-light p-4 rounded-4 border mb-4">
                        <h6 class="fw-bold mb-3 text-dark">Logic Giám sát</h6>
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Nguồn dữ liệu</label>
                                <select name="module" class="form-select rounded-3 border-0 shadow-sm" required>
                                    <option value="expenses" <?= (isset($rule) && $rule['module'] == 'expenses') ? 'selected' : '' ?>>Chi phí</option>
                                    <option value="invoices" <?= (isset($rule) && $rule['module'] == 'invoices') ? 'selected' : '' ?>>Doanh thu</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Tính toán theo</label>
                                <select name="metric" class="form-select rounded-3 border-0 shadow-sm" required>
                                    <option value="sum_amount" <?= (isset($rule) && $rule['metric'] == 'sum_amount') ? 'selected' : '' ?>>Tổng tiền</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Điều kiện</label>
                                <select name="condition_type" class="form-select rounded-3 border-0 shadow-sm font-monospace fs-5 text-center text-danger fw-bold" required>
                                    <option value=">" <?= (isset($rule) && $rule['condition_type'] == '>') ? 'selected' : '' ?>>&gt; (Lớn hơn)</option>
                                    <option value="<" <?= (isset($rule) && $rule['condition_type'] == '<') ? 'selected' : '' ?>>&lt; (Nhỏ hơn)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Ngưỡng con số <span class="text-danger">*</span></label>
                                <input type="number" name="threshold_value" class="form-control rounded-3 border-0 shadow-sm fw-bold text-danger" value="<?= isset($rule) ? round($rule['threshold_value']) : '' ?>" placeholder="VD: 50000000" required>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">Khung thời gian soi chiếu</label>
                            <select name="time_frame" class="form-select rounded-3">
                                <option value="this_month" <?= (isset($rule) && $rule['time_frame'] == 'this_month') ? 'selected' : '' ?>>Cộng dồn trong Tháng này</option>
                                <option value="today" <?= (isset($rule) && $rule['time_frame'] == 'today') ? 'selected' : '' ?>>Chỉ tính Hôm nay</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">Giới hạn chi nhánh áp dụng</label>
                            
                            <?php if ($currentBranchId !== 'all'): ?>
                                <?php 
                                    // Tìm tên chi nhánh hiện tại từ mảng branches
                                    $bName = 'Chi nhánh hiện tại';
                                    foreach($branches as $b) {
                                        if($b['id'] == $currentBranchId) { $bName = $b['name']; break; }
                                    }
                                ?>
                                <input type="text" class="form-control rounded-3 bg-light text-muted fw-bold" value="<?= htmlspecialchars($bName) ?>" readonly>
                                <input type="hidden" name="target_branches" value="<?= $currentBranchId ?>">
                            <?php else: ?>
                                <div x-data="{ 
                                    open: false, 
                                    branches: [
                                        <?php foreach($branches as $b): ?> { id: '<?= $b['id'] ?>', name: '<?= htmlspecialchars($b['name']) ?>' }, <?php endforeach; ?>
                                    ],
                                    selected: '<?= $rule['target_branches'] ?? '' ?>'.split(',').filter(Boolean),
                                    get displayText() {
                                        if (this.selected.length === 0) return '-- Áp dụng cho mọi Chi nhánh --';
                                        if (this.selected.length === 1) return this.branches.find(b => b.id === this.selected[0])?.name || '';
                                        return 'Đã chọn ' + this.selected.length + ' chi nhánh';
                                    },
                                    toggleBranch(id) {
                                        const index = this.selected.indexOf(id);
                                        if (index > -1) this.selected.splice(index, 1);
                                        else this.selected.push(id);
                                    }
                                }" @click.outside="open = false" class="position-relative">
                                    
                                    <button type="button" @click="open = !open" class="form-select rounded-3 text-start d-flex justify-content-between align-items-center bg-white">
                                        <span x-text="displayText" class="text-truncate"></span>
                                    </button>
                                    
                                    <div x-show="open" class="position-absolute top-100 start-0 w-100 mt-1 bg-white border rounded-3 shadow-lg p-2" style="display: none; max-height: 180px; overflow-y: auto; z-index: 1055;">
                                        <template x-for="b in branches" :key="b.id">
                                            <label class="d-flex align-items-center px-2 py-1 cursor-pointer hover-bg rounded">
                                                <input type="checkbox" class="form-check-input me-2 mt-0" :value="b.id" :checked="selected.includes(b.id)" @change="toggleBranch(b.id)">
                                                <span class="fs-7 text-dark" x-text="b.name"></span>
                                            </label>
                                        </template>
                                    </div>
                                    <input type="hidden" name="target_branches" :value="selected.join(',')">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-check form-switch mb-3 p-3 bg-light rounded-3">
                        <input class="form-check-input ms-0 me-3 cursor-pointer" type="checkbox" name="is_active" id="activeSwitch" <?= (!isset($rule) || $rule['is_active']) ? 'checked' : '' ?> style="width: 2.5rem; height: 1.25rem;">
                        <label class="form-check-label fw-bold text-dark cursor-pointer pt-1" for="activeSwitch">Kích hoạt bộ máy quét cho quy tắc này</label>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light fw-medium rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" :disabled="isLoading" class="btn btn-danger fw-bold rounded-pill px-4 shadow-sm d-flex align-items-center gap-2">
                            <span x-show="isLoading" class="spinner-border spinner-border-sm" style="display:none;"></span>
                            Lưu cấu hình
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>