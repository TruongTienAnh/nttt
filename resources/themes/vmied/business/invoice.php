<?php $this->extend('layouts/app') ?>

<?php $this->section('content') ?>
<main class="container py-5 mt-5 animate-fade-up">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill px-3 py-2 mb-2">
                <i class="bi bi-receipt me-1"></i> Quản lý Kinh doanh
            </span>
            <h1 class="display-6 fw-bolder text-dark mb-1">Hóa đơn (Từ Haravan)</h1>
            <p class="text-secondary mb-0">Dữ liệu đơn hàng được đồng bộ tự động về hệ thống.</p>
        </div>
    </div>

    <div class="row g-3 mb-4 text-center">
        <div class="col-md-6">
            <div class="glass-card p-3 rounded-4 border-start border-4 border-success shadow-sm h-100 bg-white d-flex flex-column justify-content-center">
                <div class="text-secondary small fw-bold text-uppercase">Tổng doanh thu hiển thị</div>
                <div class="h3 mb-0 fw-bold text-success"><?= number_format($totalRevenue ?? 0) ?> ₫</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="glass-card p-3 rounded-4 border-start border-4 border-primary shadow-sm h-100 bg-white d-flex flex-column justify-content-center">
                <div class="text-secondary small fw-bold text-uppercase">Số lượng hóa đơn</div>
                <div class="h3 mb-0 fw-bold text-primary"><?= number_format($totalInvoices ?? 0) ?> <i class="bi bi-receipt fs-5"></i></div>
            </div>
        </div>
    </div>
    <div class="glass-card rounded-4 overflow-hidden shadow-sm border">
        <div class="d-flex align-items-center justify-content-between px-4 py-3 bg-light border-bottom">
            <span class="fw-bold text-dark">Danh sách Hóa đơn (<span id="visibleCount" class="text-success"><?= count($invoices) ?></span>)</span>
            <div class="input-group" style="max-width: 300px;">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-secondary"></i></span>
                <input type="text" id="invoiceSearchInput" class="form-control border-start-0 shadow-none" placeholder="Tìm tên khách, mã bill...">
            </div>
        </div>

        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-white sticky-top text-secondary small fw-bold text-uppercase" style="top:0; z-index:1;">
                    <tr>
                        <th class="px-4 py-3 border-bottom-0">Mã Đơn / Ngày</th>
                        <th class="px-4 py-3 border-bottom-0">Khách hàng</th>
                        <th class="px-4 py-3 text-end border-bottom-0">Tổng tiền</th>
                        <th class="px-4 py-3 text-end border-bottom-0">Chi tiết</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($invoices)): ?>
                    <tr><td colspan="4" class="text-center py-5 text-secondary">Chưa có dữ liệu.</td></tr>
                    <?php else: ?>
                    <?php foreach ($invoices as $inv): ?>
                    <tr class="searchable-row">
                        <td class="px-4 py-3">
                            <div class="fw-bold text-dark"><?= htmlspecialchars($inv['invoice_no'] ?? '#INV-'.$inv['id']) ?></div>
                            <div class="text-secondary small"><?= date('d/m/Y H:i', strtotime($inv['invoice_date'])) ?></div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="fw-semibold text-dark"><?= htmlspecialchars($inv['full_name'] ?? 'Khách lẻ') ?></div>
                            <div class="text-secondary small font-monospace"><?= htmlspecialchars($inv['phone'] ?? '') ?></div>
                        </td>
                        <td class="px-4 py-3 text-end fw-bold text-success"><?= number_format($inv['total']) ?> ₫</td>
                        <td class="px-4 py-3 text-end">
                            <button class="btn btn-sm btn-light border rounded-3 px-3"
                                    hx-get="/business/invoices/<?= $inv['id'] ?>/show"
                                    hx-target="#modalShowInvoiceBody"
                                    hx-swap="innerHTML"
                                    data-bs-toggle="modal" data-bs-target="#modalShowInvoice" title="Xem hóa đơn">
                                <i class="bi bi-eye"></i> Xem Bill
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<div class="modal fade" id="modalShowInvoice" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <h5 class="modal-title fw-bolder text-dark">Chi tiết Hóa Đơn</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div id="modalShowInvoiceBody" class="modal-body px-4 py-3">
                <div class="text-center py-4 text-secondary"><div class="spinner-border spinner-border-sm me-2"></div> Đang tải...</div>
            </div>
        </div>
    </div>
</div>

<script>
// Script Tìm kiếm chuẩn xác & Cập nhật số lượng
var searchInputInv = document.getElementById('invoiceSearchInput');
var countDisplay = document.getElementById('visibleCount');

if (searchInputInv) {
    searchInputInv.addEventListener('input', function () {
        var q = this.value.toLowerCase().trim();
        var visibleRows = 0;

        document.querySelectorAll('.searchable-row').forEach(function(row) {
            var text = row.textContent.toLowerCase();
            if (text.includes(q)) { 
                row.classList.remove('d-none'); 
                visibleRows++; 
            } 
            else { 
                row.classList.add('d-none'); 
            }
        });

        if (countDisplay) {
            countDisplay.textContent = visibleRows;
        }
    });
}
</script>
<?php $this->endSection() ?>