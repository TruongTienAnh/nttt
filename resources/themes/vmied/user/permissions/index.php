<?php $this->extend('layouts/app') ?>
<?php $this->section('content') ?>
<div class="container-fluid py-4 animate-fade-up">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Nhóm Quyền</h1>
            <p class="text-secondary mb-0 small">Quản lý các vai trò và quyền hạn trong hệ thống</p>
        </div>
        <button onclick="modal('/user/permissions/create')" class="btn btn-primary rounded-pill px-4 shadow-sm">
            <i class="bi bi-shield-plus me-1"></i> Thêm nhóm mới
        </button>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="small text-secondary fw-bold text-uppercase">
                        <th class="ps-4 py-3">Tên nhóm</th>
                        <th class="py-3 text-center">Trạng thái</th>
                        <th class="pe-4 py-3 text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($roles as $r): ?>
                    <tr>
                        <td class="ps-4 py-3">
                            <span class="fw-bold text-dark"><?= htmlspecialchars($r['name']) ?></span>
                        </td>
                        <td class="py-3 text-center">
                            <div class="form-check form-switch d-flex justify-content-center">
                                <input class="form-check-input cursor-pointer" type="checkbox" role="switch" 
                                       <?= $r['status'] == 'A' ? 'checked' : '' ?>
                                       hx-post="/user/permissions/<?= $r['active'] ?>/toggle" hx-swap="none">
                            </div>
                        </td>
                        <td class="pe-4 py-3 text-end">
                            <button onclick="modal('/user/permissions/<?= $r['active'] ?>/edit')" class="btn btn-light btn-sm text-primary rounded-3 me-1">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button hx-post="/user/permissions/<?= $r['active'] ?>/delete" 
                                    hx-confirm="Xóa nhóm quyền này sẽ ảnh hưởng đến các tài khoản liên quan. Xác nhận?" 
                                    hx-swap="none" @htmx:after-request="handleResponse" 
                                    class="btn btn-light btn-sm text-danger rounded-3">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $this->endSection() ?>