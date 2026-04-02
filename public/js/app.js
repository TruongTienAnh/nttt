/**
 * Neo Framework - Core UI & SPA Helpers
 * Phụ thuộc: HTMX, Alpine.js, Bootstrap 5, Lucide Icons
 */

if (window.htmx) {
    window.htmx.config.globalViewTransitions = false;
    window.htmx.config.selfRequestsOnly = false;
}

window.NeoUI = {
    /**
     * 1. XỬ LÝ PHẢN HỒI JSON TOÀN CỤC
     */
    processResponse: function(data) {
        if (!data || typeof data !== 'object') return;

        // Ưu tiên 1: ALERT (Chặn luồng)
        if (data.alert) {
            this.alert(data.alert, data.status || 'info', data.title || 'Thông báo', () => {
                if (data.redirect) this.redirect(data.redirect, data);
            });
            return;
        }

        // Ưu tiên 2: REDIRECT (Chuyển trang + Toast sau đó)
        if (data.redirect) {
            this.redirect(data.redirect, data);
            return;
        }

        // Ưu tiên 3: TOAST (Chỉ hiện thông báo)
        if (data.toast) {
            this.toast(data.toast, data.status || 'success');
        }
    },

    /**
     * 2. ĐIỀU HƯỚNG THÔNG MINH (SPA REDIRECT)
     */
    redirect: function(url, options = {}) {
        if (!url) return;

        // Fallback: Reload cứng nếu không dùng HTMX
        if (options.htmx === false || !window.htmx) {
            window.location.href = url;
            return;
        }

        if (options.close_modal !== false) this.closeAll();

        const appContent = document.querySelector('#app-content');
        const isToLogin = url.includes('/login');
        
        // Logic chọn Target
        const targetSelector = options.target || (appContent && !isToLogin ? '#app-content' : 'body');
        const selectSelector = options.select || (targetSelector !== 'body' ? targetSelector : undefined);

        // Gọi Ajax
        try {
            const promise = window.htmx.ajax('GET', url, {
                target: targetSelector,
                select: selectSelector, 
                swap: options.swap || 'innerHTML transition:true',
                headers: { 
                    'HX-Boosted': (targetSelector === 'body') ? 'true' : 'false' 
                }
            });

            // Xử lý Promise (HTMX 2.0) hoặc dùng Fallback (HTMX 1.9)
            if (promise && typeof promise.then === 'function') {
                promise.then(() => this.afterRedirect(url, options));
            } else {
                // Fallback: Chờ một chút để HTMX hoàn tất swap
                setTimeout(() => this.afterRedirect(url, options), 100);
            }
        } catch (e) {
            window.location.href = url;
        }
    },

    /**
     * Xử lý hậu kỳ sau khi chuyển trang thành công
     */
    afterRedirect: function(url, options) {
        // 1. Cập nhật URL
        if (options.push !== false && window.location.pathname !== url) {
            window.history.pushState({}, '', url);
        }
        
        // 2. Vẽ lại Icon
        if (window.lucide) lucide.createIcons();

        // 3. HIỆN TOAST (Sửa lỗi mất Toast)
        // Hiện toast sau khi giao diện mới đã được nạp xong
        if (options.toast) {
            setTimeout(() => {
                this.toast(options.toast, options.status || 'success');
            }, 100);
        }
    },

    /**
     * 3. QUẢN LÝ POPUP (MODAL & OFFCANVAS)
     */
    open: function(html) {
        if (!html || typeof html !== 'string') return;

        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const target = doc.querySelector('.modal-load, .offcanvas-load');

        if (!target) return;

        if (target.id) {
            const old = document.getElementById(target.id);
            if (old) {
                const oldInst = bootstrap.Modal.getInstance(old) || bootstrap.Offcanvas.getInstance(old);
                oldInst?.hide();
                old.remove();
            }
        }

        const element = document.importNode(target, true);
        document.body.appendChild(element);

        if (window.htmx) window.htmx.process(element);
        if (window.Alpine) window.Alpine.initTree(element);
        if (window.lucide) lucide.createIcons();

        if (element.classList.contains('modal')) {
            const modalInst = new bootstrap.Modal(element);
            modalInst.show();
            element.addEventListener('hidden.bs.modal', () => { modalInst.dispose(); element.remove(); });
        } else if (element.classList.contains('offcanvas')) {
            const offcanvasInst = new bootstrap.Offcanvas(element);
            offcanvasInst.show();
            element.addEventListener('hidden.bs.offcanvas', () => { offcanvasInst.dispose(); element.remove(); });
        }
    },

    /**
     * 4. THÔNG BÁO (TOAST)
     */
    toast: function(message, type = 'success') {
        if (!document.body) return;

        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }

        const colorClass = { 
            'success': 'text-bg-success', 
            'error': 'text-bg-danger', 
            'warning': 'text-bg-warning' 
        }[type] || 'text-bg-info';

        const id = 't-' + Date.now();
        const iconName = type === 'success' ? 'check-circle' : (type === 'error' ? 'alert-circle' : 'info');
        const iconSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-${iconName}"><circle cx="12" cy="12" r="10"/><path d="${type==='success'?'M9 12l2 2 4-4':'M12 8v4m0 4h.01'}"/></svg>`;

        container.insertAdjacentHTML('beforeend', `
            <div id="${id}" class="toast align-items-center ${colorClass} border-0 shadow-lg fade" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                        ${iconSvg}
                        <span>${message}</span>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>`);

        const el = document.getElementById(id);
        if (window.bootstrap && window.bootstrap.Toast) {
            const bToast = new bootstrap.Toast(el, { delay: 4000 });
            bToast.show();
        }
        el.addEventListener('hidden.bs.toast', () => el.remove());
    },

    /**
     * 5. ALERT (SWEETALERT2)
     */
    alert: function(message, type = 'info', title = 'Thông báo', callback = null) {
        if (window.Swal) {
            Swal.fire({
                title, text: message, icon: type === 'error' ? 'error' : type,
                confirmButtonText: 'Đồng ý', heightAuto: false, scrollbarPadding: false, // Thêm scrollbarPadding: false để hạn chế giật
                customClass: { confirmButton: 'btn btn-primary shadow-sm px-4' }, buttonsStyling: false
            }).then(() => { 
                // Khi bấm OK, xóa class ngay lập tức trước khi redirect
                document.body.classList.remove('swal2-shown', 'swal2-height-auto');
                document.body.style.overflow = '';
                
                if (callback) callback(); 
            });
        } else {
            window.alert(message); 
            if (callback) callback();
        }
    },

    confirm: function(message, callback, type = 'question', title = 'Xác nhận') {
        if (window.Swal) {
            Swal.fire({
                title, text: message, icon: type, showCancelButton: true, confirmButtonText: 'Xác nhận', cancelButtonText: 'Hủy',
                heightAuto: false, scrollbarPadding: false,
                customClass: { confirmButton: 'btn btn-danger me-2', cancelButton: 'btn btn-light' }, buttonsStyling: false
            }).then((r) => { if (r.isConfirmed && callback) callback(); });
        } else {
            if (window.confirm(message)) callback();
        }
    },

    closeAll: function() {
        document.querySelectorAll('.modal.show, .offcanvas.show').forEach(el => {
            const inst = (bootstrap.Modal.getInstance(el) || bootstrap.Offcanvas.getInstance(el));
            if (inst) inst.hide();
        });
    }
};

/**
 * GLOBAL HELPERS
 */
window.modal = (url) => fetchUI(url);
window.offcanvas = (url) => fetchUI(url);
window.toast = (msg, type) => NeoUI.toast(msg, type);
window.alert = (msg, type, title, callback) => NeoUI.alert(msg, type, title, callback);
window.confirmAction = (msg, callback, type, title) => NeoUI.confirm(msg, callback, type, title);
window.AIChecker = function() {
    return {
        params: {
            content: '', title: 'Bản quét', aiModelVersion: 'turbo',
            check_ai: true, check_plagiarism: true, check_facts: true, check_grammar: true, check_contentOptimizer: true
        },
        isLoading: false, isAnalyzed: false, results: null, modalData: null,

        // 1. TÍNH SỐ TỪ (Sửa lỗi undefined nếu rỗng)
        get wordCount() { 
            return this.params.content.trim() ? this.params.content.trim().split(/\s+/).length : 0; 
        },

        // ==========================================================
        // 2. CÁC HÀM GETTER AN TOÀN (CHỐNG LỖI NAN% VÀ UNDEFINED)
        // ==========================================================
        get humanScore() {
            return this.results ? (this.results.ai.confidence.Original * 100).toFixed(0) : 0;
        },
        get aiScore() {
            return this.results ? (this.results.ai.confidence.AI * 100).toFixed(0) : 0;
        },
        get plagScore() {
            return this.results?.plagiarism ? this.results.plagiarism.score : 0;
        },
        get grammarErrors() {
            return this.results?.grammarSpelling?.matches?.length || 0;
        },
        get factErrors() {
            return this.results?.facts?.length || 0;
        },
        get seoScore() {
            return this.results?.contentOptimizer ? this.results.contentOptimizer.content_score : 0;
        },

        // ==========================================================
        // 3. LOGIC CHÍNH
        // ==========================================================
        async runAnalysis() {
            if (this.wordCount < 10) return NeoUI.toast('Vui lòng nhập ít nhất 10 từ!', 'warning');
            this.isLoading = true;
            try {
                const response = await fetch('/mock/originality/scan', { method: 'POST', body: JSON.stringify(this.params) });
                const data = await response.json();
                if (response.ok) {
                    this.results = this.forceMapErrors(data.results); 
                    this.isAnalyzed = true;
                    this.$nextTick(() => {
                        lucide.createIcons();
                        [...document.querySelectorAll('[data-bs-toggle="tooltip"]')].map(el => new bootstrap.Tooltip(el));
                    });
                }
            } catch (e) { NeoUI.toast('Lỗi API', 'error'); } finally { this.isLoading = false; }
        },

        editAgain() { this.isAnalyzed = false; this.$nextTick(() => lucide.createIcons()); },

        exportPDF() { NeoUI.toast('Đang khởi tạo tệp PDF...', 'info'); },

        forceMapErrors(data) {
            if (data.ai.blocks.length > 0) {
                if (this.params.check_grammar && data.grammarSpelling?.matches) {
                    data.ai.blocks[0].grammar = data.grammarSpelling.matches[0];
                }
                if (this.params.check_plagiarism && data.plagiarism?.results) {
                    const target = data.ai.blocks.length > 1 ? 1 : 0;
                    data.ai.blocks[target].plagiarism = data.plagiarism.results[0];
                }
            }
            return data;
        },

        // UI Helpers
        getBlockClass(block) {
            let classes = ['hl-block', 'p-1', 'mx-0.5', 'rounded'];
            if (block.result.fake > 0.8) classes.push('bg-ai-high');
            else if (block.result.fake > 0.4) classes.push('bg-ai-med');
            else classes.push('bg-ai-low'); 
            if (block.plagiarism && this.params.check_plagiarism) classes.push('err-plagiarism');
            if (block.grammar && this.params.check_grammar) classes.push('err-grammar');
            return classes.join(' ');
        },

        getTooltip(block) {
            let html = `<strong>Điểm AI: ${(block.result.fake * 100).toFixed(1)}%</strong>`;
            if (block.grammar && this.params.check_grammar) html += `<br><span class="text-danger">❌ Lỗi: Sai chính tả</span>`;
            if (block.plagiarism && this.params.check_plagiarism) html += `<br><span class="text-warning">⚠️ Lỗi: Đạo văn</span>`;
            return html;
        },

        openDetailModal(block) {
            if (!block.plagiarism && !block.grammar) return NeoUI.toast('Câu này không có lỗi chi tiết.', 'info');
            this.modalData = block;
            new bootstrap.Modal(document.getElementById('detailModal')).show();
            this.$nextTick(() => lucide.createIcons());
        }
    }
}
async function fetchUI(url) {
    try {
        const r = await fetch(url, { headers: { 'HX-Request': 'true' } });
        NeoUI.open(await r.text());
    } catch (e) { console.error(e); }
}

/**
 * ALPINE COMPONENTS
 */
window.Form = function() {
    return {
        errorMessage: null, isLoading: false,
        startRequest() { this.errorMessage = null; this.isLoading = true; },
        handleResponse(event) {
            this.isLoading = false;
            // Lưu ý: Không xử lý redirect ở đây nữa vì beforeSwap sẽ lo việc đó
            // Chỉ xử lý lỗi nội tuyến (validation)
            try {
                const data = JSON.parse(event.detail.xhr.responseText);
                if (data.status === 'error' && !data.alert && !data.redirect && !data.toast) {
                    this.errorMessage = data.content;
                }
            } catch (e) { }
        },
        handleError() { this.isLoading = false; NeoUI.toast('Lỗi kết nối máy chủ', 'error'); }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide) lucide.createIcons();
    // 1. SỰ KIỆN BEFORE SWAP: Chặn HTMX in JSON ra màn hình
    document.body.addEventListener('htmx:beforeSwap', (e) => {
        const xhr = e.detail.xhr;
        const contentType = xhr.getResponseHeader('Content-Type') || '';
        const text = xhr.responseText.trim();

        // Nếu server trả về JSON, ta tự xử lý và KHÔNG cho HTMX swap
        if (contentType.includes('application/json') || text.startsWith('{')) {
            e.detail.shouldSwap = false; // Chặn swap (Sửa lỗi vỡ trang)
            try {
                const data = JSON.parse(text);
                NeoUI.processResponse(data); // Gọi hàm xử lý trung tâm
            } catch (err) {}
        }
    });

    // 2. SỰ KIỆN AFTER REQUEST: Chỉ xử lý mở Popup HTML
    document.body.addEventListener('htmx:afterRequest', (e) => {
        const xhr = e.detail.xhr;
        if (xhr.status >= 200 && xhr.status < 300) {
            const contentType = xhr.getResponseHeader('Content-Type') || '';
            const text = xhr.responseText.trim();

            // Nếu không phải JSON mà có class modal/offcanvas -> Mở Popup
            if (!contentType.includes('application/json') && !text.startsWith('{')) {
                if (text.includes('class="modal') || text.includes('class="offcanvas') || 
                    text.includes("class='modal") || text.includes("class='offcanvas")) {
                    NeoUI.open(text);
                }
            }
        }
    });
    
    // 1. Xử lý TOAST khi gặp lỗi (Code của bạn)
    document.body.addEventListener('htmx:responseError', function(evt) {
        const xhr = evt.detail.xhr;
        const status = xhr.status;

        if (status === 404) {
            NeoUI.toast('Không tìm thấy dữ liệu hoặc trang yêu cầu!', 'error');
        } else if (status === 500) {
            NeoUI.toast('Lỗi hệ thống! Vui lòng thử lại sau.', 'error');
        } else if (status === 403) {
            NeoUI.toast('Bạn không có quyền thực hiện hành động này.', 'warning');
        }
    });
    document.body.addEventListener('htmx:load', function() {
        if (window.lucide) lucide.createIcons();
    });
});