<div class="container pt-5 mt-5 py-4 space-y-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-end gap-3">
        <div>
            <h1 class="display-6 fw-bold text-dark mb-1">
                Xin chào, <span class="text-gradient"><?=$user->name?></span> 👋
            </h1>
            <p class="text-secondary small mb-0">Chọn công cụ để bắt đầu phiên làm việc.</p>
        </div>
    </div>
</div>
<div class="container">
    <div class="row g-3 flex-nowrap flex-sm-wrap overflow-auto py-3 mx-0">
        <div class="col-10 col-sm-6 col-lg-3" hx-get="/app/ai" hx-target="#app-content" hx-push-url="true">
            <div id="card-ai" class="card h-100 rounded-4 <?=$active=='ai'?'border-2 border-primary ' :'border-0'?> overflow-hidden shadow-sm position-relative" style="cursor: pointer; transition: transform 0.2s;">
                <div class="position-absolute top-0 end-0 bg-primary-subtle rounded-circle" style="width: 6rem; height: 6rem; margin-top: -3rem; margin-right: -3rem;"></div>
                
                <div class="card-body position-relative z-1 p-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-3 mb-3 p-3">
                        <i data-lucide="bot" style="width: 24px; height: 24px;"></i>
                    </div>
                    <h5 class="card-title fw-bold text-dark mb-1">Check AI Viết</h5>
                    <p class="card-text text-secondary small text-truncate" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; white-space: normal;">
                        Phát hiện ChatGPT, Gemini, Claude.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-10 col-sm-6 col-lg-3" hx-get="/app/plagiarism" hx-target="#app-content" hx-push-url="true">
            <div id="card-plagiarism" class="card h-100 rounded-4 <?=$active=='plagiarism'?'border-2 border-danger ' :'border-0'?>  overflow-hidden shadow-sm position-relative" style="cursor: pointer;">
                <div class="position-absolute top-0 end-0 bg-danger-subtle rounded-circle" style="width: 6rem; height: 6rem; margin-top: -3rem; margin-right: -3rem;"></div>
                
                <div class="card-body position-relative z-1 p-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-danger-subtle text-danger rounded-3 mb-3 p-3">
                        <i data-lucide="scan-search" style="width: 24px; height: 24px;"></i>
                    </div>
                    <h5 class="card-title fw-bold text-dark mb-1">Check Đạo văn</h5>
                    <p class="card-text text-secondary small text-truncate" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; white-space: normal;">
                        Quét trùng lặp từ 10M+ nguồn dữ liệu.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-10 col-sm-6 col-lg-3" hx-get="/app/grammar" hx-target="#app-content" hx-push-url="true">
            <div id="card-grammar" class="card h-100 rounded-4 <?=$active=='grammar'?'border-2 border-success ' :'border-0'?>  overflow-hidden shadow-sm position-relative" style="cursor: pointer;">
                <div class="position-absolute top-0 end-0 bg-success-subtle rounded-circle" style="width: 6rem; height: 6rem; margin-top: -3rem; margin-right: -3rem;"></div>
                
                <div class="card-body position-relative z-1 p-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-success-subtle text-success rounded-3 mb-3 p-3">
                        <i data-lucide="spell-check" style="width: 24px; height: 24px;"></i>
                    </div>
                    <h5 class="card-title fw-bold text-dark mb-1">Check Ngữ pháp</h5>
                    <p class="card-text text-secondary small text-truncate" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; white-space: normal;">
                        Sửa lỗi chính tả & cấu trúc câu.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-10 col-sm-6 col-lg-3" hx-get="/app/humanizer" hx-target="#app-content" hx-push-url="true">
            <div id="card-humanizer" class="card h-100 rounded-4 <?=$active=='humanizer'?'border-2 border-info ' :'border-0'?>  overflow-hidden shadow-sm position-relative" style="cursor: pointer;">
                <div class="position-absolute top-0 end-0 bg-info-subtle rounded-circle" style="width: 6rem; height: 6rem; margin-top: -3rem; margin-right: -3rem;"></div>
                
                <div class="card-body position-relative z-1 p-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-info-subtle text-info-emphasis rounded-3 mb-3 p-3">
                        <i data-lucide="wand-2" style="width: 24px; height: 24px;"></i>
                    </div>
                    <h5 class="card-title fw-bold text-dark mb-1">Humanizer AI</h5>
                    <p class="card-text text-secondary small text-truncate" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; white-space: normal;">
                        Biến văn bản AI thành tự nhiên.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>