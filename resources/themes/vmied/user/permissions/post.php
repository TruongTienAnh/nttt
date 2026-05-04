<div class="modal fade modal-load" id="modal-permission" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header p-4 border-bottom-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-shield-check me-2 text-primary"></i><?= $role ? 'Cập nhật nhóm quyền' : 'Tạo nhóm quyền mới' ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 pt-0">
                <form hx-post="<?= $role ? '/user/permissions/'.$role['active'].'/update' : '/user/permissions/store' ?>" 
                      hx-swap="none" @htmx:after-request="handleResponse">
                    
                    <div class="bg-light p-3 rounded-3 mb-4 border">
                        <label class="form-label fw-bold small text-uppercase text-secondary">Tên hiển thị</label>
                        <input type="text" name="name" class="form-control form-control-lg border-0 shadow-none bg-transparent fw-bold" 
                               placeholder="Ví dụ: Kế toán trưởng, Nhân viên bán hàng..." 
                               value="<?= $role['name'] ?? '' ?>" required>
                    </div>

                    <div class="row g-3" style="max-height: 55vh; overflow-y: auto; overflow-x: hidden;">
                        <?php foreach($allPermissions as $groupName => $perms): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 bg-light rounded-3 h-100">
                                <div class="card-header border-0 bg-transparent fw-bold text-primary pt-3 pb-1">
                                    <i class="bi bi-folder2-open me-2"></i><?= $groupName ?>
                                </div>
                                <div class="card-body">
                                    <?php foreach($perms as $key => $label): ?>
                                    <div class="form-check mb-1">
                                        <input class="form-check-input cursor-pointer" type="checkbox" name="perms[]" value="<?= $key ?>" 
                                               id="p_<?= str_replace('.', '_', $key) ?>" 
                                               <?= (isset($role['permission_array'][$key])) ? 'checked' : '' ?>>
                                        <label class="form-check-label small cursor-pointer" for="p_<?= str_replace('.', '_', $key) ?>">
                                            <?= $label ?>
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-4 pt-3 border-top text-end">
                        <button type="button" class="btn btn-link text-secondary text-decoration-none me-3" data-bs-dismiss="modal">Bỏ qua</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm fw-bold">Lưu thông tin</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>