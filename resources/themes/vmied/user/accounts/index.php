<?php $this->extend('layouts/app') ?>
<?php $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Danh sách Tài khoản</h1>
            <p class="text-secondary mb-0 small">Quản lý nhân viên và phân quyền truy cập</p>
        </div>
        <button onclick="modal('/user/accounts/create')" class="btn btn-primary rounded-pill px-4 shadow-sm">
            <i class="bi bi-person-plus me-1"></i> Thêm tài khoản
        </button>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light small fw-bold text-secondary">
                <tr>
                    <th class="ps-4 py-3">Thành viên</th>
                    <th class="py-3">Tên đăng nhập</th>
                    <th class="py-3 text-center">Vai trò</th>
                    <th class="py-3 text-center">Trạng thái</th>
                    <th class="pe-4 py-3 text-end">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($accounts as $acc): ?>
                <tr>
                    <td class="ps-4 py-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm me-3 bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width:38px; height:38px">
                                <?= strtoupper(substr($acc['name'], 0, 1)) ?>
                            </div>
                            <div>
                                <div class="fw-bold text-dark"><?= $acc['name'] ?></div>
                                <div class="small text-secondary"><?= $acc['email'] ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="py-3">
                        <code class="text-primary fw-bold"><?= $acc['account'] ?></code>
                    </td>
                    <td class="py-3 text-center">
                        <span class="badge bg-info bg-opacity-10 text-info px-3 rounded-pill border border-info border-opacity-25">
                            <?= $acc['role_name'] ?>
                        </span>
                    </td>
                    <td class="py-3 text-center">
                        <div class="form-check form-switch d-inline-block">
                            <input class="form-check-input cursor-pointer" type="checkbox" role="switch" 
                                   <?= $acc['status'] == 1 ? 'checked' : '' ?>
                                   hx-post="/user/accounts/<?= $acc['uuid'] ?>/toggle" hx-swap="none">
                        </div>
                    </td>
                    <td class="pe-4 py-3 text-end">
                        <button onclick="modal('/user/accounts/<?= $acc['uuid'] ?>/edit')" class="btn btn-light btn-sm text-primary rounded-3">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <button hx-post="/user/accounts/<?= $acc['uuid'] ?>/delete" hx-confirm="Bạn có chắc muốn xóa tài khoản này?" 
                                hx-swap="none" @htmx:after-request="handleResponse" class="btn btn-light btn-sm text-danger rounded-3">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $this->endSection() ?>