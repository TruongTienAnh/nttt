<div class="modal-body px-4 py-4">
    <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-4">
        <div>
            <h4 class="fw-bolder text-dark mb-1">Mã đơn: <span class="text-primary">#<?= htmlspecialchars($invoice['invoice_no'] ?? $invoice['code'] ?? $invoice['id']) ?></span></h4>
            <div class="text-secondary small">Ngày tạo: <?= date('d/m/Y H:i', strtotime($invoice['invoice_date'] ?? $invoice['created_at'])) ?></div>
        </div>
        <div class="text-end">
            <?php 
                $paymentStatus = $invoice['status'] ?? $invoice['payment_status'] ?? 'pending';
                $isPaid = in_array(strtolower($paymentStatus), ['paid', 'hoàn tất', 'thành công', 'completed', 'đã thanh toán']);
                $statusColor = $isPaid ? 'success' : 'warning';
            ?>
            <span class="badge bg-<?= $statusColor ?> bg-opacity-10 text-<?= $statusColor ?> border border-<?= $statusColor ?>-subtle px-3 py-2 rounded-pill mb-2 d-block">
                <i class="bi bi-credit-card me-1"></i> TT: <?= strtoupper($paymentStatus) ?>
            </span>
            <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle px-3 py-1 rounded-pill">
                Nguồn: <?= htmlspecialchars(strtoupper($invoice['source'] ?? 'HARAVAN')) ?>
            </span>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <h6 class="fw-bold text-secondary text-uppercase small mb-2"><i class="bi bi-person-lines-fill me-1"></i> Người mua</h6>
            <div class="fw-bold text-dark"><?= htmlspecialchars($customer['full_name'] ?? 'Khách lẻ') ?></div>
            <div class="text-secondary small font-monospace"><?= htmlspecialchars($customer['phone'] ?? '') ?></div>
            <div class="text-secondary small"><?= htmlspecialchars($customer['email'] ?? '') ?></div>
        </div>
        <div class="col-md-6">
            <h6 class="fw-bold text-secondary text-uppercase small mb-2"><i class="bi bi-geo-alt-fill me-1"></i> Địa chỉ</h6>
            <div class="text-dark small">
                <?= nl2br(htmlspecialchars($customer['address'] ?? 'Mua tại quầy / Không có địa chỉ giao hàng')) ?>
            </div>
        </div>
    </div>

    <h6 class="fw-bold text-secondary text-uppercase small mb-2"><i class="bi bi-box-seam me-1"></i> Chi tiết đơn hàng</h6>
    <div class="table-responsive border rounded-3 mb-4">
        <table class="table table-hover align-middle mb-0 fs-7">
            <thead class="table-light text-secondary">
                <tr>
                    <th class="ps-3 py-2">Sản phẩm</th>
                    <th class="text-end py-2">Đơn giá</th>
                    <th class="text-center py-2">SL</th>
                    <th class="text-end pe-3 py-2">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                    <tr><td colspan="4" class="text-center py-4 text-muted">Không có dữ liệu sản phẩm.</td></tr>
                <?php else: ?>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td class="ps-3 py-3">
                            <div class="fw-semibold text-dark"><?= htmlspecialchars($item['name'] ?? 'Sản phẩm') ?></div>
                        </td>
                        <td class="text-end text-secondary"><?= number_format($item['unit_price'] ?? $item['price'] ?? 0) ?> ₫</td>
                        <td class="text-center fw-bold text-dark">x<?= $item['qty'] ?? $item['quantity'] ?? 1 ?></td>
                        <td class="text-end pe-3 fw-bold text-dark"><?= number_format($item['total'] ?? (($item['unit_price'] ?? 0) * ($item['qty'] ?? 1))) ?> ₫</td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="row justify-content-end">
        <div class="col-md-6 col-lg-5">
            <div class="d-flex justify-content-between mb-2 small text-secondary">
                <span>Tạm tính (Subtotal):</span>
                <span class="fw-medium text-dark"><?= number_format($invoice['subtotal'] ?? $invoice['total'] ?? 0) ?> ₫</span>
            </div>
            <div class="d-flex justify-content-between mb-3 small text-secondary border-bottom pb-2">
                <span>Giảm giá (Discount):</span>
                <span class="fw-medium text-danger">- <?= number_format($invoice['discount'] ?? $invoice['discount_amount'] ?? 0) ?> ₫</span>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-0">
                <span class="fw-bold text-dark fs-6">TỔNG CỘNG:</span>
                <span class="fw-bolder text-success fs-4"><?= number_format($invoice['total'] ?? 0) ?> ₫</span>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer border-0 px-4 pb-4 pt-0">
    <button class="btn btn-secondary rounded-3 px-4 fw-bold" data-bs-dismiss="modal">Đóng</button>
</div>