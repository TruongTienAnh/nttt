<div class="modal fade modal-load" id="modal-account" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header p-4 border-bottom-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-gear me-2 text-primary"></i><?= $account ? 'Thiết lập tài khoản' : 'Tạo mới tài khoản' ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 pt-0">
                <form hx-post="<?= $account ? '/user/accounts/'.$account['uuid'].'/update' : '/user/accounts/store' ?>" 
                      hx-swap="none" @htmx:after-request="handleResponse">
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Họ và tên</label>
                            <input type="text" name="name" class="form-control rounded-3" value="<?= $account['name'] ?? '' ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Email liên hệ</label>
                            <input type="email" name="email" class="form-control rounded-3" value="<?= $account['email'] ?? '' ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Tên đăng nhập (Username)</label>
                            <input type="text" name="account" class="form-control rounded-3 bg-light" value="<?= $account['account'] ?? '' ?>" <?= $account ? 'readonly' : 'required' ?>>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Mật khẩu <?= $account ? '(Để trống nếu không đổi)' : '' ?></label>
                            <input type="password" name="password" class="form-control rounded-3" <?= $account ? '' : 'required' ?>>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-primary"><i class="bi bi-shield-check me-1 text-primary"></i>Gán nhóm quyền</label>
                        <select name="permission_id" class="form-select rounded-3 border-primary border-opacity-25" required>
                            <option value="">-- Chọn một vai trò --</option>
                            <?php foreach($roles as $r): ?>
                            <option value="<?= $r['id'] ?>" <?= (isset($account['permission_id']) && $account['permission_id'] == $r['id']) ? 'selected' : '' ?>>
                                <?= $r['name'] ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-bold text-primary"><i class="bi bi-geo-alt me-1 text-primary"></i>Phạm vi quản lý (Chi nhánh)</label>
                        <div class="bg-light p-3 rounded-3 border overflow-auto" style="max-height: 200px;">
                            <div class="row g-2">
                                <?php foreach($branches as $b): ?>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="branch_ids[]" value="<?= $b['id'] ?>" 
                                               id="br_<?= $b['id'] ?>" 
                                               <?= (isset($account['branch_ids']) && in_array($b['id'], $account['branch_ids'])) ? 'checked' : '' ?>>
                                        <label class="form-check-label small cursor-pointer" for="br_<?= $b['id'] ?>">
                                            <?= $b['name'] ?>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top text-end">
                        <button type="button" class="btn btn-light px-4 me-2 rounded-pill" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary px-5 rounded-pill shadow-sm fw-bold">Xác nhận lưu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>