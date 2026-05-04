<?php $this->extend('layouts/app') ?>

<?php $this->section('content') ?>
<main class="container py-5 mt-5 animate-fade-up" x-data="{ 
    selectedIds: [],
    selectAll: false,
    showFilter: <?= (!empty($filter['from_date']) || !empty($filter['to_date']) || !empty($filter['branch_id'])) ? 'true' : 'false' ?>,
    
    toggleAll() {
        this.selectAll = !this.selectAll;
        this.selectedIds = this.selectAll ? Array.from(document.querySelectorAll('.row-checkbox')).map(el => el.value) : [];
    },
    updateSelectAll() {
        const total = document.querySelectorAll('.row-checkbox').length;
        this.selectAll = (this.selectedIds.length === total && total > 0);
    }
}">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill px-3 py-2 mb-2">
                <i class="bi bi-wallet2 me-1"></i> Quản lý Tài chính
            </span>
            <h1 class="display-6 fw-bolder text-dark mb-1">Nhập liệu Chi phí</h1>
            <p class="text-secondary mb-0">Hệ thống quản lý và cảnh báo biến động chi phí động.</p>
        </div>
        <div class="d-flex gap-2">
            <button 
                x-show="selectedIds.length > 0"
                style="display: none;"
                class="btn btn-outline-danger fw-bold rounded-4 px-4 py-2 shadow-sm d-flex align-items-center gap-2"
                hx-post="/business/expenses/bulk-delete"
                hx-vals='js:{"ids": Array.from(document.querySelectorAll(".row-checkbox:checked")).map(el => el.value).join(",")}'
                hx-confirm="Bạn có chắc chắn muốn xóa các mục đã chọn?"
                hx-swap="none"
                x-data="Form()"
                @htmx:after-request="handleResponse($event)"
            >
                <i class="bi bi-trash"></i> Xóa (<span x-text="selectedIds.length"></span>)
            </button>

            <button @click="showFilter = !showFilter" 
                    class="btn btn-light fw-bold rounded-4 px-3 py-2 shadow-sm d-flex align-items-center gap-2 border position-relative"
                    :class="showFilter ? 'bg-secondary-subtle' : ''">
                <i class="bi bi-funnel"></i> Bộ lọc
                <?php if(!empty($filter['from_date']) || !empty($filter['to_date']) || !empty($filter['branch_id'])): ?>
                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                <?php endif; ?>
            </button>

            <button onclick="modal('/business/expenses/create')" class="btn btn-primary fw-bold rounded-4 px-4 py-2 shadow-sm d-flex align-items-center gap-2">
                <i class="bi bi-plus-lg"></i> Thêm khoản chi
            </button>
        </div>
    </div>

    <div x-show="showFilter" x-transition.opacity.duration.300ms style="display: none; position: relative; z-index: 10;">
        <div class="glass-card p-4 rounded-5 mb-4 border shadow-sm bg-white" style="overflow: visible !important;">
            <form hx-get="/business/expenses" hx-target="#app-content" hx-push-url="true" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-secondary">Từ ngày</label>
                    <input type="date" name="from_date" class="form-control rounded-3 border-0 bg-light px-3" value="<?= $filter['from_date'] ?? '' ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-secondary">Đến ngày</label>
                    <input type="date" name="to_date" class="form-control rounded-3 border-0 bg-light px-3" value="<?= $filter['to_date'] ?? '' ?>">
                </div>
                
                <div class="col-md-3" x-data="{ 
                    open: false, 
                    branches: [
                        <?php foreach($branches as $b): ?>
                            { id: '<?= $b['id'] ?>', name: '<?= htmlspecialchars($b['name']) ?>' },
                        <?php endforeach; ?>
                    ],
                    selected: '<?= $filter['branch_id'] ?? '' ?>'.split(',').filter(Boolean),
                    get displayText() {
                        if (this.selected.length === 0) return '-- Tất cả chi nhánh --';
                        if (this.selected.length === 1) return this.branches.find(b => b.id === this.selected[0])?.name || '';
                        return 'Đã chọn ' + this.selected.length + ' chi nhánh';
                    },
                    toggleBranch(id) {
                        const index = this.selected.indexOf(id);
                        if (index > -1) this.selected.splice(index, 1);
                        else this.selected.push(id);
                    }
                }" @click.outside="open = false">
                    <label class="form-label small fw-bold text-secondary">So sánh chi nhánh</label>
                    <div class="position-relative">
                        <button type="button" @click="open = !open" class="form-select rounded-3 border-0 bg-light text-start d-flex justify-content-between align-items-center">
                            <span x-text="displayText" class="text-truncate"></span>
                        </button>
                        
                        <div x-show="open" class="position-absolute top-100 start-0 w-100 mt-1 bg-white border rounded-3 shadow-lg p-2" style="display: none; max-height: 200px; overflow-y: auto; z-index: 9999;">
                            <template x-for="b in branches" :key="b.id">
                                <label class="d-flex align-items-center px-2 py-1 cursor-pointer hover-bg rounded">
                                    <input type="checkbox" class="form-check-input me-2 mt-0" :value="b.id" :checked="selected.includes(b.id)" @change="toggleBranch(b.id)">
                                    <span class="fs-7 text-dark" x-text="b.name"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                    <input type="hidden" name="filter_branch_id" :value="selected.join(',')">
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-dark fw-bold rounded-3 px-4 flex-grow-1">Áp dụng</button>
                    <a href="/business/expenses" hx-get="/business/expenses" hx-target="#app-content" class="btn btn-light rounded-3 px-3 border shadow-none" title="Xóa bộ lọc"><i class="bi bi-x-lg"></i></a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4 text-center">
        <div class="col-md-3">
            <div class="glass-card p-3 rounded-4 border-start border-4 border-danger shadow-sm h-100 bg-white">
                <div class="text-secondary small fw-bold">TỔNG CHI TIÊU</div>
                <div class="h4 mb-0 fw-bold text-danger"><?= number_format($totalSum) ?> ₫</div>
            </div>
        </div>
        <?php $colors = ['info', 'warning', 'success']; ?>
        <?php foreach($topCategories as $index => $cat): ?>
        <div class="col-md-3">
            <div class="glass-card p-3 rounded-4 border-start border-4 border-<?= $colors[$index] ?? 'secondary' ?> shadow-sm h-100 bg-white">
                <div class="text-secondary small fw-bold text-uppercase text-truncate" title="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></div>
                <div class="h4 mb-0 fw-bold"><?= number_format($cat['total']) ?> ₫</div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="glass-card rounded-5 overflow-hidden shadow-sm border bg-white">
        <div class="d-flex align-items-center justify-content-between px-4 py-3 bg-light border-bottom">
            <span class="fw-bold text-dark">Lịch sử chi phí</span>
            <div class="input-group" style="max-width: 260px;">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-secondary"></i></span>
                <input type="text" id="searchInput" class="form-control border-start-0 shadow-none" placeholder="Tìm nhanh...">
            </div>
        </div>

        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light sticky-top" style="top:0; z-index:1;">
                    <tr class="text-secondary small fw-bold text-uppercase">
                        <th class="ps-4 py-3" style="width: 50px;">
                            <div class="form-check">
                                <input class="form-check-input cursor-pointer" type="checkbox" @click="toggleAll()" :checked="selectAll">
                            </div>
                        </th>
                        <th class="px-3 py-3">Ngày chi</th>
                        <th class="px-3 py-3">Hạng mục</th>
                        <th class="px-3 py-3">Tên khoản chi</th>
                        <th class="px-3 py-3 text-end">Số tiền</th>
                        <th class="px-3 py-3">Chi nhánh</th>
                        <th class="px-3 py-3 text-end pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($expenses)): ?>
                    <tr><td colspan="7" class="text-center py-5 text-secondary italic">Không có dữ liệu phù hợp.</td></tr>
                    <?php else: ?>
                    <?php foreach ($expenses as $e): ?>
                    <tr class="searchable-row transition-all" :class="selectedIds.includes('<?= $e['id'] ?>') ? 'bg-primary-subtle' : ''">
                        <td class="ps-4 py-3">
                            <div class="form-check">
                                <input class="form-check-input row-checkbox" type="checkbox" value="<?= $e['id'] ?>" x-model="selectedIds" @change="updateSelectAll()">
                            </div>
                        </td>
                        <td class="px-3 py-3 fw-bold text-dark"><?= date('d/m/Y', strtotime($e['expense_date'])) ?></td>
                        <td class="px-3 py-3">
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill px-3">
                                <?= htmlspecialchars($e['category_name'] ?? 'Khác') ?>
                            </span>
                        </td>
                        <td class="px-3 py-3 fw-semibold text-dark"><?= htmlspecialchars($e['title']) ?></td>
                        <td class="px-3 py-3 text-end fw-bold text-danger">-<?= number_format($e['amount']) ?> ₫</td>
                        <td class="px-3 py-3 small text-secondary"><?= $e['branch_name'] ? htmlspecialchars($e['branch_name']) : 'Chi phí chung' ?></td>
                        <td class="pe-4 py-3 text-end" x-data="Form()">
                            <div class="d-flex gap-2 justify-content-end">
                                <button onclick="modal('/business/expenses/<?= $e['id'] ?>/edit')" class="btn btn-sm btn-light border rounded-3 px-3 text-primary shadow-xs"><i class="bi bi-pencil-square"></i></button>
                                <button hx-post="/business/expenses/<?= $e['id'] ?>/delete" hx-confirm="Xóa khoản chi này?" hx-swap="none" @htmx:after-request="handleResponse($event)" class="btn btn-sm btn-light border rounded-3 px-3 text-danger shadow-xs"><i class="bi bi-trash3"></i></button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
// Search nhanh tại trình duyệt
var searchInput = document.getElementById('searchInput');
if (searchInput) {
    searchInput.addEventListener('input', function () {
        var q = this.value.toLowerCase().trim();
        document.querySelectorAll('.searchable-row').forEach(function(row) {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
}
</script>
<?php $this->endSection() ?>