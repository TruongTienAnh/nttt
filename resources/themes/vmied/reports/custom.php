<?php $this->extend('layouts/app') ?>
<?php $this->section('content') ?>


<div class="container-fluid py-4 animate-fade-up" x-data="{ module: '<?= $module ?>', groupBy: '<?= $groupBy ?>' }">
    <div class="mb-4">
        <h2 class="fw-bold mb-1"><i class="bi bi-funnel text-primary me-2"></i>Báo Cáo Tùy Biến (Custom Builder)</h2>
        <p class="text-secondary small">Trích xuất mọi dữ liệu bạn cần từ Khách hàng, Hóa đơn đến Chi phí theo đúng trường dữ liệu.</p>
    </div>

    <form action="" method="GET" class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-4">
            
            <h6 class="fw-bold mb-3 border-bottom pb-2 text-dark"><i class="bi bi-1-circle-fill me-2 text-primary"></i>1. Chọn Nguồn dữ liệu & Thời gian</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Nguồn dữ liệu</label>
                    <select name="module" x-model="module" class="form-select border-0 bg-light fw-bold shadow-sm">
                        <option value="invoices">Hóa đơn & Doanh thu</option>
                        <option value="customers">Danh sách Khách hàng</option>
                        <option value="expenses">Danh sách Chi phí</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label small fw-bold">Phạm vi Chi nhánh</label>
                    <div wire:ignore>
                        <select name="branch_ids[]" multiple class="form-select shadow-sm use-select2" placeholder="Tất cả chi nhánh (Gõ để tìm)...">
                            <?php foreach($branches as $b): ?>
                                <option value="<?= $b['id'] ?>" <?= in_array($b['id'], $branchIds) ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Từ ngày</label>
                    <input type="date" name="from_date" class="form-control border-0 bg-light shadow-sm" value="<?= $fromDate ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Đến ngày</label>
                    <input type="date" name="to_date" class="form-control border-0 bg-light shadow-sm" value="<?= $toDate ?>">
                </div>
            </div>

            <h6 class="fw-bold mb-3 border-bottom pb-2 text-dark"><i class="bi bi-2-circle-fill me-2 text-primary"></i>2. Bộ Lọc Tìm Kiếm Nâng Cao</h6>
            <div class="bg-light p-3 rounded-4 mb-4 border">
                
                <div class="row g-3" x-show="module === 'customers'" style="display: none;">
                    <div class="col-md-3">
                        <input type="text" name="filters[full_name]" class="form-control" placeholder="Tìm Tên khách hàng..." value="<?= htmlspecialchars($filters['full_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="filters[phone]" class="form-control" placeholder="Tìm SĐT..." value="<?= htmlspecialchars($filters['phone'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="filters[gender]" class="form-select">
                            <option value="">Giới tính</option>
                            <option value="male" <?= isset($filters['gender']) && $filters['gender']=='male'?'selected':'' ?>>Nam</option>
                            <option value="female" <?= isset($filters['gender']) && $filters['gender']=='female'?'selected':'' ?>>Nữ</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="filters[tier]" class="form-select">
                            <option value="">Hạng khách</option>
                            <option value="new" <?= isset($filters['tier']) && $filters['tier']=='new'?'selected':'' ?>>Khách mới (New)</option>
                            <option value="regular" <?= isset($filters['tier']) && $filters['tier']=='regular'?'selected':'' ?>>Thường xuyên</option>
                            <option value="vip" <?= isset($filters['tier']) && $filters['tier']=='vip'?'selected':'' ?>>Khách VIP</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="filters[source]" class="form-select">
                            <option value="">Nguồn khách</option>
                            <option value="walk_in" <?= isset($filters['source']) && $filters['source']=='walk_in'?'selected':'' ?>>Vãng lai</option>
                            <option value="social" <?= isset($filters['source']) && $filters['source']=='social'?'selected':'' ?>>Mạng xã hội</option>
                            <option value="ads" <?= isset($filters['source']) && $filters['source']=='ads'?'selected':'' ?>>Quảng cáo</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3" x-show="module === 'invoices'" style="display: none;">
                    <div class="col-md-3">
                        <input type="text" name="filters[invoice_no]" class="form-control" placeholder="Tìm Mã HĐ (VD: INV-)..." value="<?= htmlspecialchars($filters['invoice_no'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="filters[status]" class="form-select">
                            <option value="">Trạng thái đơn</option>
                            <option value="draft" <?= isset($filters['status']) && $filters['status']=='draft'?'selected':'' ?>>Bản nháp</option>
                            <option value="paid" <?= isset($filters['status']) && $filters['status']=='paid'?'selected':'' ?>>Đã thanh toán</option>
                            <option value="cancelled" <?= isset($filters['status']) && $filters['status']=='cancelled'?'selected':'' ?>>Đã hủy</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="filters[payment_method]" class="form-select">
                            <option value="">P.Thức thanh toán</option>
                            <option value="cash" <?= isset($filters['payment_method']) && $filters['payment_method']=='cash'?'selected':'' ?>>Tiền mặt</option>
                            <option value="transfer" <?= isset($filters['payment_method']) && $filters['payment_method']=='transfer'?'selected':'' ?>>Chuyển khoản</option>
                            <option value="card" <?= isset($filters['payment_method']) && $filters['payment_method']=='card'?'selected':'' ?>>Cà thẻ</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3" x-show="module === 'expenses'" style="display: none;">
                    <div class="col-md-6">
                        <input type="text" name="filters[title]" class="form-control" placeholder="Tìm tên/lý do chi..." value="<?= htmlspecialchars($filters['title'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="filters[note]" class="form-control" placeholder="Ghi chú chi phí..." value="<?= htmlspecialchars($filters['note'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <h6 class="fw-bold mb-3 border-bottom pb-2 text-dark"><i class="bi bi-3-circle-fill me-2 text-primary"></i>3. Cấu hình Đầu ra</h6>
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Chế độ hiển thị</label>
                    <select name="group_by" x-model="groupBy" class="form-select shadow-sm border-0 bg-light fw-bold">
                        <option value="none">Danh sách Chi tiết (Lọc cột)</option>
                        <option value="branch">Thống kê theo Chi nhánh</option>
                        <option value="month">Thống kê theo Tháng</option>
                        <option value="day">Thống kê theo Ngày</option>
                    </select>
                </div>
                
                <div class="col-md-6" x-show="groupBy === 'none'">
                    <label class="form-label small fw-bold">Tùy biến các cột dữ liệu hiển thị</label>
                    <div wire:ignore>
                        <select name="columns[]" multiple class="form-select shadow-sm use-select2" placeholder="Để trống để hiển thị tất cả các cột...">
                            <optgroup label="Bảng Hóa Đơn">
                                <option value="invoice_no" <?= in_array('invoice_no', $columns)?'selected':'' ?>>Mã Hóa Đơn</option>
                                <option value="subtotal" <?= in_array('subtotal', $columns)?'selected':'' ?>>Tiền hàng</option>
                                <option value="discount" <?= in_array('discount', $columns)?'selected':'' ?>>Giảm giá</option>
                                <option value="total" <?= in_array('total', $columns)?'selected':'' ?>>Tổng thanh toán</option>
                                <option value="payment_method" <?= in_array('payment_method', $columns)?'selected':'' ?>>Hình thức TT</option>
                                <option value="status" <?= in_array('status', $columns)?'selected':'' ?>>Trạng thái</option>
                            </optgroup>
                            <optgroup label="Bảng Khách Hàng">
                                <option value="full_name" <?= in_array('full_name', $columns)?'selected':'' ?>>Họ và tên</option>
                                <option value="phone" <?= in_array('phone', $columns)?'selected':'' ?>>Số điện thoại</option>
                                <option value="email" <?= in_array('email', $columns)?'selected':'' ?>>Email</option>
                                <option value="tier" <?= in_array('tier', $columns)?'selected':'' ?>>Hạng khách</option>
                                <option value="source" <?= in_array('source', $columns)?'selected':'' ?>>Nguồn</option>
                                <option value="gender" <?= in_array('gender', $columns)?'selected':'' ?>>Giới tính</option>
                            </optgroup>
                            <optgroup label="Bảng Chi Phí">
                                <option value="title" <?= in_array('title', $columns)?'selected':'' ?>>Tên khoản chi</option>
                                <option value="amount" <?= in_array('amount', $columns)?'selected':'' ?>>Số tiền</option>
                            </optgroup>
                            <optgroup label="Dùng Chung">
                                <option value="note" <?= in_array('note', $columns)?'selected':'' ?>>Ghi chú</option>
                            </optgroup>
                        </select>
                    </div>
                </div>

                <div class="col-md-6" x-show="groupBy !== 'none'" style="display:none;">
                    <label class="form-label small fw-bold">Phép tính (Metric)</label>
                    <select name="metric" class="form-select border-0 bg-light shadow-sm fw-bold">
                        <option value="sum" <?= $metric=='sum'?'selected':'' ?> x-show="module !== 'customers'">Tính TỔNG Doanh thu/Chi phí (VNĐ)</option>
                        <option value="count" <?= $metric=='count'?'selected':'' ?>>ĐẾM Số lượng (Khách mới, Hóa đơn...)</option>
                    </select>
                </div>

                <div class="col-md-3 text-end">
                    <button type="submit" class="btn btn-dark w-100 fw-bold shadow-sm py-2 rounded-3">
                        <i class="bi bi-search me-2"></i> KẾT XUẤT DATA
                    </button>
                </div>
            </div>
        </div>
    </form>

    <?php if(isset($_GET['module'])): ?>
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden animate-fade-up">
        <div class="card-header bg-dark text-white p-3 d-flex justify-content-between align-items-center border-bottom-0">
            <h6 class="mb-0 fw-bold">Dữ liệu kết xuất (<?= count($results) ?> dòng)</h6>
            <button class="btn btn-sm btn-light fw-bold px-3 rounded-pill" onclick="window.print()"><i class="bi bi-printer me-1"></i> In</button>
        </div>
        <div class="table-responsive custom-scrollbar" style="max-height: 60vh;">
            <table class="table table-hover table-striped align-middle mb-0 fs-7">
                <?php if(empty($results)): ?>
                    <tbody><tr><td class="text-center py-5 text-muted">Không có dữ liệu thỏa mãn bộ lọc.</td></tr></tbody>
                <?php elseif($isDetail): ?>
                    <thead class="bg-light sticky-top small fw-bold text-uppercase text-secondary">
                        <tr>
                            <?php foreach (array_keys($results[0]) as $colName): 
                                // Bỏ qua các cột hệ thống không cần thiết
                                if (in_array($colName, ['id', 'organization_id', 'branch_id', 'deleted'])) continue;
                            ?>
                                <th class="py-3 px-3 bg-light"><?= htmlspecialchars($colName) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($results as $row): ?>
                        <tr>
                            <?php foreach ($row as $key => $value): 
                                if (in_array($key, ['id', 'organization_id', 'branch_id', 'deleted'])) continue;
                            ?>
                                <td class="px-3 py-2 <?= in_array($key, ['total', 'subtotal', 'discount', 'amount']) ? 'text-success fw-bold font-monospace' : '' ?>">
                                    <?php 
                                        if (in_array($key, ['total', 'subtotal', 'discount', 'amount']) && is_numeric($value)) {
                                            echo number_format((float)$value) . ' ₫';
                                        } elseif ($key == 'status') {
                                            $b = ['paid'=>'success','draft'=>'secondary','cancelled'=>'danger','refunded'=>'warning'];
                                            echo '<span class="badge bg-'.($b[$value] ?? 'secondary').'">'.$value.'</span>';
                                        } elseif ($key == 'tier') {
                                            $b = ['new'=>'info','regular'=>'primary','vip'=>'warning','sleeping'=>'secondary'];
                                            echo '<span class="badge bg-'.($b[$value] ?? 'secondary').'">'.$value.'</span>';
                                        } elseif ($key == 'gender') {
                                            echo $value === 'male' ? 'Nam' : ($value === 'female' ? 'Nữ' : 'Khác');
                                        } else {
                                            echo htmlspecialchars((string)$value);
                                        }
                                    ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                <?php else: ?>
                    <thead class="bg-primary text-white sticky-top small fw-bold text-uppercase">
                        <tr>
                            <th class="ps-4 py-3 bg-primary text-white"><?= $groupBy == 'branch' ? 'Chi nhánh' : 'Thời gian' ?></th>
                            <th class="text-end pe-4 py-3 bg-primary text-white">Kết quả</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($results as $r): ?>
                        <tr>
                            <td class="ps-4 py-3 fw-bold text-dark"><?= htmlspecialchars($r['label'] ?? '') ?></td>
                            <td class="text-end pe-4 fw-bold <?= $metric=='sum' ? 'text-success font-monospace' : 'text-primary font-monospace' ?>">
                                <?= number_format((float)$r['value']) ?> <?= $metric=='sum' ? '₫' : 'đơn vị' ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                <?php endif; ?>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php $this->endSection() ?>