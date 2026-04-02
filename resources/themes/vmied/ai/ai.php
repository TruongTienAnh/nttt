<?php $this->extend('layouts/app') ?>

<?php $this->section('content') ?>
<?= $this->insert('ai/menu-ai',["user"=>$user,'active'=>'ai']) ?>

<section class="container py-4" x-data="AIChecker()" x-cloak>

    <div class="card  shadow-lg border-0 rounded-4 overflow-hidden position-relative" style="min-height: 800px;">
        
        <div x-show="isLoading" x-cloak 
             class="position-absolute top-0 start-0 w-100 h-100 z-3 bg-white bg-opacity-75" 
             style="backdrop-filter: blur(8px);">
             
            <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center">
                <div class="spinner-border text-primary mb-3" style="width: 4rem; height: 4rem; border-width: 4px;"></div>
                <h3 class="fw-black text-dark tracking-tight">AI Đang Tổng Hợp Báo Cáo...</h3>
                <p class="text-secondary fw-medium">Đạo văn • Ngữ pháp • Sự thật • SEO</p>
            </div>
        </div>

        <div class="row g-0 h-100">
            <div class="col-lg-8 d-flex flex-column bg-white border-end h-100">
                
                <div class="px-4 py-3 border-bottom bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-primary bg-opacity-10 p-2 rounded-3 text-primary"><i data-lucide="layout-template"></i></div>
                            <div>
                                <h5 class="fw-black m-0 text-dark lh-1" x-text="isAnalyzed ? 'Báo cáo Văn bản' : 'Trạm Kiểm duyệt Nội dung'"></h5>
                                <span class="extra-small text-muted fw-bold">V3 API</span>
                            </div>
                        </div>
                        <span class="badge bg-white text-dark shadow-sm border px-3 py-2 rounded-pill fw-bold" x-text="wordCount + ' từ'"></span>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-3 mt-3">
                        <span class="small fw-bold text-muted">MÔ HÌNH:</span>
                        <select class="form-select form-select-sm border-0 bg-white shadow-sm rounded-pill w-auto fw-bold" x-model="params.aiModelVersion" :disabled="isAnalyzed">
                            <option value="turbo">⚡ Turbo Fast</option>
                            <option value="lite">🎯 Standard Lite</option>
                        </select>
                        <span class="small fw-bold text-muted ms-2">PHẠM VI QUÉT:</span>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm rounded-pill fw-bold d-flex align-items-center gap-1" :class="params.check_plagiarism ? 'btn-warning text-dark' : 'btn-light text-muted'" @click="if(!isAnalyzed) params.check_plagiarism = !params.check_plagiarism">
                                <i data-lucide="copy" class="size-3"></i> Đạo văn
                            </button>
                            <button class="btn btn-sm rounded-pill fw-bold d-flex align-items-center gap-1" :class="params.check_grammar ? 'btn-danger text-white' : 'btn-light text-muted'" @click="if(!isAnalyzed) params.check_grammar = !params.check_grammar">
                                <i data-lucide="spell-check" class="size-3"></i> Ngữ pháp
                            </button>
                            <button class="btn btn-sm rounded-pill fw-bold d-flex align-items-center gap-1" :class="params.check_facts ? 'btn-danger text-white' : 'btn-light text-muted'" @click="if(!isAnalyzed) params.check_facts = !params.check_facts">
                                <i data-lucide="alert-triangle" class="size-3"></i> Sự thật
                            </button>
                            <button class="btn btn-sm rounded-pill fw-bold d-flex align-items-center gap-1" :class="params.check_contentOptimizer ? 'btn-info text-white' : 'btn-light text-muted'" @click="if(!isAnalyzed) params.check_contentOptimizer = !params.check_contentOptimizer">
                                <i data-lucide="search" class="size-3"></i> SEO
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex-grow-1 p-4 custom-scroll overflow-auto" style="max-height: 600px;">
                    <textarea x-show="!isAnalyzed" x-model="params.content" class="form-control w-100 border-0 shadow-none fs-5 text-secondary" style="resize: none;min-height:500px;max-height:600px" placeholder="Bắt đầu gõ hoặc dán nội dung của bạn vào đây..."></textarea>
                    
                    <div x-show="isAnalyzed" class="fs-5 lh-lg" style="white-space: pre-wrap;">
                        <template x-for="block in results?.ai?.blocks">
                            <span :class="getBlockClass(block)"
                                  @click="openDetailModal(block)"
                                  data-bs-toggle="tooltip" data-bs-html="true"
                                  :title="getTooltip(block)"
                                  x-html="block.text + ' '"></span>
                        </template>
                    </div>
                </div>

                <div class="p-3 border-top bg-light d-flex justify-content-between align-items-center">
                    <div x-show="!isAnalyzed">
                        <button class="btn btn-link text-muted fw-bold text-decoration-none"><i data-lucide="history" class="size-4 me-1"></i> Lịch sử quét</button>
                    </div>
                    <div x-show="isAnalyzed">
                        <button @click="editAgain()" class="btn btn-outline-dark bg-white rounded-pill fw-bold shadow-sm px-4">
                            <i data-lucide="edit-3" class="size-4 me-1"></i> Chỉnh sửa & Quét lại
                        </button>
                    </div>

                    <button x-show="!isAnalyzed" @click="runAnalysis()" class="btn btn-dark rounded-pill fw-black px-5 py-2 shadow-sm d-flex align-items-center gap-2 fs-6">
                        KIỂM TRA NGAY <i data-lucide="zap" class="text-warning"></i>
                    </button>
                </div>
            </div>

            <div class="col-lg-4 bg-light">
                <div class="p-4 custom-scroll h-100 overflow-auto" style="max-height: 800px;">
                    
                    <template x-if="!isAnalyzed">
                        <div class="h-100 d-flex flex-column align-items-center justify-content-center text-muted">
                            <i data-lucide="bar-chart-3" class="size-16 text-primary mb-3"></i>
                            <h6 class="fw-bold text-dark">Chưa có dữ liệu</h6>
                            <p class="small text-center">Bấm kiểm tra để xem báo cáo chi tiết.</p>
                        </div>  
                    </template>

                    <template x-if="results">
                        <div class="d-flex flex-column gap-3 pb-5">
                            
                            <div class="card border-0 shadow-sm rounded-4 p-4 text-center">
                                <span class="text-secondary small fw-bold">TỶ LỆ CON NGƯỜI VIẾT</span>
                                <div class="display-3 fw-black my-1 text-success" x-text="(results.ai.confidence.Original * 100).toFixed(0) + '%'"></div>
                                <div class="progress mt-2" style="height: 10px; border-radius: 5px;">
                                    <div class="progress-bar bg-success" :style="'width:' + (results.ai.confidence.Original * 100) + '%'"></div>
                                    <div class="progress-bar bg-danger" :style="'width:' + (results.ai.confidence.AI * 100) + '%'"></div>
                                </div>
                            </div>

                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
                                        <span class="small fw-bold text-warning-emphasis mb-1"><i data-lucide="copy" class="size-4 me-1"></i> Đạo văn</span>
                                        <h3 class="fw-black mb-0" x-text="(results.plagiarism ? results.plagiarism.score : 0) + '%'"></h3>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
                                        <span class="small fw-bold text-danger mb-1"><i data-lucide="spell-check" class="size-4 me-1"></i> Ngữ pháp</span>
                                        <h3 class="fw-black mb-0 text-danger" x-text="(results.grammarSpelling ? results.grammarSpelling.matches.length : 0) + ' Lỗi'"></h3>
                                    </div>
                                </div>
                            </div>

                            <template x-if="params.check_facts && results.facts && results.facts.length > 0">
                                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                                    <div class="bg-danger text-white px-3 py-2 small fw-bold d-flex justify-content-between">
                                        <span><i data-lucide="alert-triangle" class="size-4 me-1"></i> CẢNH BÁO SAI SỰ THẬT</span>
                                        <span class="badge bg-white text-danger" x-text="results.facts.length"></span>
                                    </div>
                                    <div class="card-body p-0">
                                        <template x-for="fact in results.facts">
                                            <div class="p-3 border-bottom bg-danger bg-opacity-10">
                                                <div class="fw-bold text-danger mb-1" x-text="fact.fact"></div>
                                                <div class="small text-dark italic" x-text="fact.explanation"></div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <template x-if="params.check_contentOptimizer && results.contentOptimizer">
                                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="small fw-bold m-0">TỐI ƯU SEO</h6>
                                        <span class="badge bg-primary" x-text="'Điểm: ' + results.contentOptimizer.content_score"></span>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <template x-for="kw in results.contentOptimizer.keyword_seeds.slice(0, 5)">
                                            <div class="p-2 bg-light rounded-3">
                                                <div class="d-flex justify-content-between extra-small fw-bold mb-1">
                                                    <span x-text="kw.keyword"></span>
                                                    <span :class="kw.current >= kw.min ? 'text-success' : 'text-danger'" x-text="kw.current + '/' + kw.min"></span>
                                                </div>
                                                <div class="progress" style="height: 4px;">
                                                    <div class="progress-bar" :class="kw.current >= kw.min ? 'bg-success' : 'bg-primary'" :style="'width:' + (kw.current/kw.max * 100) + '%'"></div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <template x-if="results.readability">
                                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                                    <h6 class="small fw-bold border-bottom pb-2 mb-3">ĐỌC HIỂU (READABILITY)</h6>
                                    <div class="row g-2 text-center">
                                        <div class="col-6">
                                            <div class="bg-primary bg-opacity-10 p-2 rounded-3">
                                                <div class="extra-small text-muted">Trình độ (Điểm)</div>
                                                <div class="h5 fw-black text-primary m-0" x-text="results.readability.readability.fleschGradeLevel"></div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="bg-info bg-opacity-10 p-2 rounded-3">
                                                <div class="extra-small text-muted">Thời gian đọc</div>
                                                <div class="h5 fw-black text-info m-0" x-text="results.readability.text_stats.averageReadingTime + 'p'"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                    </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-light border-0">
                    <h5 class="fw-bold m-0 text-dark"><i data-lucide="zoom-in" class="text-primary me-2"></i>Chi tiết Vấn đề</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" x-show="modalData">
                    <div class="p-3 bg-light rounded-3 mb-4 text-dark fst-italic border fs-6">"<span x-text="modalData?.text"></span>"</div>

                    <template x-if="modalData?.plagiarism">
                        <div>
                            <h6 class="fw-black text-warning mb-2"><i data-lucide="copy" class="size-4"></i> TRÙNG LẶP NỘI DUNG</h6>
                            <a :href="modalData?.plagiarism?.results[0]?.link" target="_blank" class="fw-bold text-primary" x-text="modalData?.plagiarism?.results[0]?.title"></a>
                            <span class="badge bg-warning text-dark ms-2" x-text="(modalData?.plagiarism?.results[0]?.scores[0]?.score * 100).toFixed(0) + '% Match'"></span>
                        </div>
                    </template>

                    <template x-if="modalData?.grammar">
                        <div class="mt-3">
                            <h6 class="fw-black text-danger mb-2"><i data-lucide="spell-check" class="size-4"></i> LỖI CHÍNH TẢ</h6>
                            <div class="p-3 bg-danger bg-opacity-10 border border-danger rounded-3">
                                <div class="fw-bold text-danger mb-1" x-text="modalData?.grammar?.message"></div>
                                <div>Sửa từ <span class="text-decoration-line-through text-muted" x-text="modalData?.grammar?.context?.text"></span> thành <span class="fw-bold text-success" x-text="modalData?.grammar?.replacements[0]?.value"></span></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</section>
<?php $this->endSection() ?>