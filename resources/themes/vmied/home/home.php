<?php $this->extend('layouts/app') ?>

<?php $this->section('content') ?>
    <?= $this->insert('ai/menu-ai',["user"=>$user, 'active' => '']) ?>

    <div class="container pt-3">
        <section class="py-4">
            <h3 class="fw-bold text-dark mb-4">Hoạt động gần đây</h3>

            <div class="card border shadow-sm overflow-hidden" style="border-radius: 2rem;">
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light border-bottom">
                            <tr>
                                <th class="px-4 py-3 text-secondary text-uppercase small fw-bold" style="min-width: 200px;">Tên tài liệu</th>
                                <th class="px-4 py-3 text-secondary text-uppercase small fw-bold">Công cụ</th>
                                <th class="px-4 py-3 text-secondary text-uppercase small fw-bold">Kết quả</th>
                                <th class="px-4 py-3 text-secondary text-uppercase small fw-bold">Thời gian</th>
                                <th class="px-4 py-3 text-secondary text-uppercase small fw-bold text-end">Thao tác</th>
                            </tr>
                        </thead>

                        <tbody class="border-top-0">
                            
                            <tr class="transition-hover">
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-primary-subtle text-primary" style="width: 40px; height: 40px;">
                                            <i data-lucide="file-text" style="width: 20px; height: 20px;"></i>
                                        </div>
                                        <span class="fw-semibold text-dark">Luan_Van_Tot_Nghiep_V1.docx</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="badge rounded-pill d-inline-flex align-items-center gap-1 border" 
                                          style="color: #9333ea; background-color: #faf5ff; border-color: #f3e8ff;">
                                        <i data-lucide="bot" style="width: 12px; height: 12px;"></i> Check AI
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="fw-bold text-success">98% Human</span>
                                </td>
                                <td class="px-4 py-3 text-secondary">Vừa xong</td>
                                <td class="px-4 py-3 text-end">
                                    <button class="btn btn-light text-secondary hover-primary btn-sm rounded-3 p-2 border-0 opacity-0 btn-action transition-all">
                                        <i data-lucide="download" style="width: 20px; height: 20px;"></i>
                                    </button>
                                </td>
                            </tr>

                            <tr class="transition-hover">
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-danger-subtle text-danger" style="width: 40px; height: 40px;">
                                            <i data-lucide="file-warning" style="width: 20px; height: 20px;"></i>
                                        </div>
                                        <span class="fw-semibold text-dark">Bao_Cao_Thuc_Tap.pdf</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill d-inline-flex align-items-center gap-1">
                                        <span class="bg-success rounded-circle" style="width: 6px; height: 6px;"></span> Hoàn tất
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="fw-bold text-danger">15% Trùng lặp</span>
                                </td>
                                <td class="px-4 py-3 text-secondary">2 giờ trước</td>
                                <td class="px-4 py-3 text-end">
                                    <button class="btn btn-light text-secondary hover-primary btn-sm rounded-3 p-2 border-0 opacity-0 btn-action transition-all">
                                        <i data-lucide="download" style="width: 20px; height: 20px;"></i>
                                    </button>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
<?php $this->endSection() ?>