<div class="modal-body p-0">
    <div class="bg-primary bg-opacity-10 px-4 py-4 border-bottom text-center">
        <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle mb-3 shadow-sm" style="width: 60px; height: 60px; font-size: 24px;">
            <i class="bi bi-person"></i>
        </div>
        <h4 class="fw-bolder text-dark mb-1"><?= htmlspecialchars($customer['full_name'] ?? 'Chưa cập nhật') ?></h4>
        <div class="text-secondary small font-monospace"><i class="bi bi-telephone-fill me-1"></i> <?= htmlspecialchars($customer['phone'] ?? 'Trống') ?></div>
    </div>

    <div class="p-4">
        <h6 class="fw-bold text-primary text-uppercase small mb-3 border-bottom pb-2">1. Thông tin liên hệ</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="text-secondary small mb-1">Email</div>
                <div class="fw-medium text-dark"><?= htmlspecialchars($customer['email'] ?? 'Không có') ?></div>
            </div>
            <div class="col-md-6">
                <div class="text-secondary small mb-1">Giới tính / Ngày sinh</div>
                <div class="fw-medium text-dark">
                    <?= htmlspecialchars($customer['gender'] ?? 'Khác') ?> 
                    <?= !empty($customer['birthday']) ? ' - ' . date('d/m/Y', strtotime($customer['birthday'])) : '' ?>
                </div>
            </div>
            <div class="col-12">
                <div class="text-secondary small mb-1">Địa chỉ chi tiết</div>
                <div class="fw-medium text-dark">
                    <?= htmlspecialchars($customer['address'] ?? 'Không có địa chỉ') ?>
                    <?php if(!empty($customer['ward'])): ?>, <?= htmlspecialchars($customer['ward']) ?><?php endif; ?>
                    <?php if(!empty($customer['district'])): ?>, <?= htmlspecialchars($customer['district']) ?><?php endif; ?>
                    <?php if(!empty($customer['province'])): ?>, <?= htmlspecialchars($customer['province']) ?><?php endif; ?>
                </div>
            </div>
        </div>

        <h6 class="fw-bold text-primary text-uppercase small mb-3 border-bottom pb-2">2. Dữ liệu Đồng bộ (Haravan)</h6>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="text-secondary small mb-1">Mã KH (Haravan ID)</div>
                <div class="fw-medium font-monospace text-dark"><?= htmlspecialchars($customer['haravan_id'] ?? $customer['id']) ?></div>
            </div>
            <div class="col-md-6">
                <div class="text-secondary small mb-1">Tổng chi tiêu (Lịch sử)</div>
                <div class="fw-bold text-success"><?= number_format($customer['total_spent'] ?? 0) ?> ₫</div>
            </div>
            <div class="col-md-6">
                <div class="text-secondary small mb-1">Nhãn / Tags</div>
                <div class="fw-medium text-dark"><?= htmlspecialchars($customer['tags'] ?? 'Không có') ?></div>
            </div>
            <div class="col-md-6">
                <div class="text-secondary small mb-1">Ngày đồng bộ</div>
                <div class="fw-medium text-dark"><?= date('d/m/Y H:i', strtotime($customer['created_at'])) ?></div>
            </div>
            <div class="col-12">
                <div class="text-secondary small mb-1">Ghi chú (Note)</div>
                <div class="bg-light p-2 rounded-3 text-dark small border">
                    <?= nl2br(htmlspecialchars($customer['note'] ?? 'Không có ghi chú')) ?>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer border-0 px-4 pb-4">
    <button class="btn btn-secondary rounded-3 px-4 fw-bold w-100" data-bs-dismiss="modal">Đóng hồ sơ</button>
</div>