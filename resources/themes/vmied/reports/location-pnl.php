<?php $this->extend('layouts/app') ?>
<?php $this->section('content') ?>
<div class="container-fluid py-4 animate-fade-up">
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-dark text-white">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="fw-bold mb-1"><i class="bi bi-buildings me-2 text-warning"></i>So sánh P&L Chi nhánh</h2>
                    <p class="opacity-75 mb-0">Đánh giá hiệu suất và biên lợi nhuận của từng cơ sở.</p>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <form class="d-flex gap-2 justify-content-md-end">
                        <input type="date" name="from_date" class="form-control form-control-sm border-0" value="<?= $filter['from_date'] ?>">
                        <input type="date" name="to_date" class="form-control form-control-sm border-0" value="<?= $filter['to_date'] ?>">
                        <button type="submit" class="btn btn-warning btn-sm fw-bold px-3">Lọc</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4 bg-light">
            <h6 class="fw-bold mb-3 text-secondary text-uppercase"><i class="bi bi-arrows-collapse me-2"></i>Chế độ đối đầu (Head-to-Head)</h6>
            <form action="" method="GET" class="row g-2 align-items-center">
                <input type="hidden" name="from_date" value="<?= $filter['from_date'] ?>">
                <input type="hidden" name="to_date" value="<?= $filter['to_date'] ?>">
                <div class="col-md-4">
                    <select name="branch_a" class="form-select border-0 shadow-sm fw-bold">
                        <option value="">-- Chọn chi nhánh A --</option>
                        <?php foreach($branches as $b): ?><option value="<?= $b['id'] ?>" <?= $filter['branch_a'] == $b['id'] ? 'selected' : '' ?>><?= $b['name'] ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-1 text-center fw-bold text-danger fs-5">VS</div>
                <div class="col-md-4">
                    <select name="branch_b" class="form-select border-0 shadow-sm fw-bold">
                        <option value="">-- Chọn chi nhánh B --</option>
                        <?php foreach($branches as $b): ?><option value="<?= $b['id'] ?>" <?= $filter['branch_b'] == $b['id'] ? 'selected' : '' ?>><?= $b['name'] ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3"><button type="submit" class="btn btn-dark w-100 fw-bold">So sánh ngay</button></div>
            </form>
        </div>
    </div>

    <?php if (!empty($comparison)): ?>
    <div class="row g-3 mb-4">
        <?php 
        $metrics = [
            ['key' => 'revenue', 'label' => 'Doanh thu', 'color' => 'primary'],
            ['key' => 'expenses', 'label' => 'Chi phí', 'color' => 'danger'],
            ['key' => 'profit', 'label' => 'Lợi nhuận ròng', 'color' => 'success'],
            ['key' => 'margin', 'label' => 'Biên lợi nhuận (%)', 'color' => 'info'],
        ];
        
        foreach($metrics as $m): 
            // Fix: Ép kiểu float (số thực) để tránh lỗi Unsupported operand types
            $valA = (float)($comparison['A'][$m['key']] ?? 0); 
            $valB = (float)($comparison['B'][$m['key']] ?? 0);
            
            $winA = $valA > $valB; 
            $winB = $valB > $valA;
            
            // Với chi phí, số bé hơn thì "chiến thắng"
            if($m['key'] == 'expenses') { 
                $winA = $valA < $valB; 
                $winB = $valB < $valA; 
            }
        ?>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 text-center">
                <div class="card-header bg-white border-0 pt-3 pb-0">
                    <span class="small fw-bold text-secondary text-uppercase"><?= $m['label'] ?></span>
                </div>
                <div class="card-body d-flex p-0 mt-2">
                    <div class="col-6 p-3 border-end <?= $winA ? 'bg-'.$m['color'].'-subtle' : '' ?>">
                        <div class="fw-bold fs-5 <?= $winA ? 'text-'.$m['color'] : 'text-dark' ?>"><?= number_format($valA) ?></div>
                        <div class="small text-muted text-truncate px-1"><?= htmlspecialchars($comparison['A']['name'] ?? '') ?></div>
                    </div>
                    <div class="col-6 p-3 <?= $winB ? 'bg-'.$m['color'].'-subtle' : '' ?>">
                        <div class="fw-bold fs-5 <?= $winB ? 'text-'.$m['color'] : 'text-dark' ?>"><?= number_format($valB) ?></div>
                        <div class="small text-muted text-truncate px-1"><?= htmlspecialchars($comparison['B']['name'] ?? '') ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-bottom p-3">
            <h6 class="fw-bold mb-0">Bảng Xếp Hạng Lợi Nhuận Toàn Hệ Thống</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light small fw-bold text-secondary text-uppercase">
                    <tr>
                        <th class="ps-4 py-3">Chi nhánh</th>
                        <th class="text-end">Doanh thu</th>
                        <th class="text-end">Đóng góp</th>
                        <th class="text-end">Chi phí</th>
                        <th class="text-end pe-4">Lợi nhuận ròng</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pnlData)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">Không có dữ liệu trong khoảng thời gian này.</td></tr>
                    <?php else: ?>
                        <?php foreach($pnlData as $r): ?>
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="fw-bold text-dark"><?= htmlspecialchars($r['name']) ?></div>
                                <div class="small text-muted">Biên lợi nhuận: <span class="fw-bold text-info"><?= $r['margin'] ?>%</span></div>
                            </td>
                            <td class="text-end fw-bold text-dark"><?= number_format((float)$r['revenue']) ?> ₫</td>
                            <td class="text-end">
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <span class="small font-monospace"><?= $r['contribution'] ?>%</span>
                                    <div class="progress" style="width: 50px; height: 6px;">
                                        <div class="progress-bar bg-primary" style="width: <?= $r['contribution'] ?>%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end fw-bold text-danger"><?= number_format((float)$r['expenses']) ?> ₫</td>
                            <td class="text-end pe-4">
                                <span class="badge <?= $r['profit'] >= 0 ? 'bg-success' : 'bg-danger' ?> rounded-pill px-3 py-2 fs-6">
                                    <?= number_format((float)$r['profit']) ?> ₫
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $this->endSection() ?>