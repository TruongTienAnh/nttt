<?php $this->extend('layouts/app') ?>
<?php $this->section('content') ?>

<div class="container-fluid py-4 animate-fade-up" x-data="{ 
    selectedIds: [],
    selectAll: false,
    toggleAll() {
        this.selectAll = !this.selectAll;
        this.selectedIds = this.selectAll ? Array.from(document.querySelectorAll('.row-checkbox')).map(el => el.value) : [];
    },
    updateSelectAll() {
        const total = document.querySelectorAll('.row-checkbox').length;
        this.selectAll = this.selectedIds.length === total && total > 0;
    }
}">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark">Danh mục Chi phí</h1>
            <p class="text-secondary mb-0">Quản lý các loại chi phí hệ thống.</p>
        </div>
        
        <div class="d-flex gap-2">
            <button 
                x-show="selectedIds.length > 0"
                style="display: none;"
                class="btn btn-outline-danger fw-medium rounded-pill px-4 shadow-sm d-flex align-items-center gap-2"
                hx-post="/config/expense-categories-bulk-delete"
                hx-vals='js:{"ids": Array.from(document.querySelectorAll(".row-checkbox:checked")).map(el => el.value).join(",")}'
                hx-confirm="Bạn có chắc chắn muốn xóa các mục đã chọn?"
                hx-swap="none"
                x-data="Form()"
                @htmx:after-request="handleResponse($event)"
            >
                <i class="bi bi-trash"></i> Xóa đã chọn (<span x-text="selectedIds.length"></span>)
            </button>

            <button onclick="modal('/config/expense-categories-post')" class="btn btn-primary fw-medium rounded-pill px-4 shadow-sm hover-lift d-flex align-items-center gap-2">
                <i class="bi bi-plus-lg"></i> Thêm danh mục
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3" style="width: 40px;">
                            <div class="form-check">
                                <input class="form-check-input cursor-pointer" type="checkbox" @click="toggleAll()" :checked="selectAll">
                            </div>
                        </th>
                        <th class="text-secondary small fw-bold text-uppercase py-3">Tên danh mục</th>
                        <th class="text-secondary small fw-bold text-uppercase py-3">Mô tả</th>
                        <th class="text-secondary small fw-bold text-uppercase py-3">Trạng thái</th>
                        <th class="text-secondary small fw-bold text-uppercase py-3 text-end pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-5">Chưa có dữ liệu.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($categories as $item): ?>
                        <tr class="hover-bg-light transition-all" :class="selectedIds.includes('<?= $item['id'] ?>') ? 'bg-primary-subtle' : ''">
                            <td class="ps-4 py-3">
                                <div class="form-check">
                                    <input class="form-check-input cursor-pointer row-checkbox" type="checkbox" 
                                           value="<?= $item['id'] ?>" 
                                           x-model="selectedIds"
                                           @change="updateSelectAll()">
                                </div>
                            </td>
                            <td class="py-3 fw-bold text-dark"><?= htmlspecialchars($item['name']) ?></td>
                            <td class="py-3 text-secondary small"><?= htmlspecialchars($item['description'] ?? '---') ?></td>
                            <td class="py-3" x-data="Form()">
                                <div class="form-check form-switch">
                                    <input class="form-check-input cursor-pointer" type="checkbox" role="switch"
                                           <?= $item['is_active'] ? 'checked' : '' ?>
                                           hx-post="/config/expense-categories-toggle"
                                           hx-vals='{"id": "<?= $item['id'] ?>", "status": "<?= $item['is_active'] ? 0 : 1 ?>"}'
                                           hx-swap="none"
                                           @htmx:after-request="handleResponse($event)">
                                </div>
                            </td>
                            <td class="pe-4 py-3 text-end" x-data="Form()">
                                <div class="d-flex gap-2 justify-content-end">
                                    <button onclick="modal('/config/expense-categories-post?id=<?= $item['id'] ?>')" class="btn btn-light btn-sm text-primary bg-primary-subtle border-0 rounded-3">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button 
                                        hx-post="/config/expense-categories-delete" 
                                        hx-vals='{"id": "<?= $item['id'] ?>"}'
                                        hx-confirm="Bạn có chắc chắn muốn xóa?"
                                        hx-swap="none"
                                        @htmx:after-request="handleResponse($event)"
                                        class="btn btn-light btn-sm text-danger bg-danger-subtle border-0 rounded-3">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
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