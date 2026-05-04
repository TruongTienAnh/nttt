<?php $this->extend('layouts/app') ?>
<?php $this->section('content') ?>

<div class="container-fluid py-4 animate-fade-up" x-data="ReportBuilder()">
    <div class="mb-4">
        <h1 class="h3 fw-bold mb-1">Trình tạo Báo cáo Tự chọn</h1>
        <p class="text-secondary">Tùy biến số liệu và so sánh đa chi nhánh theo nhu cầu phân tích.</p>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="clean-card p-4 rounded-4 shadow-sm bg-white border">
                <h6 class="fw-bold mb-4 border-bottom pb-2"><i class="bi bi-sliders2-vertical me-2 text-primary"></i>Cấu hình báo cáo</h6>
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">1. Nguồn dữ liệu</label>
                    <select x-model="config.dataSource" class="form-select border-0 bg-light rounded-3">
                        <option value="invoices">Doanh thu (Hóa đơn)</option>
                        <option value="expenses">Chi phí (Vận hành)</option>
                        <option value="customers">Khách hàng (Tăng trưởng)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">2. Phân tích theo chiều</label>
                    <select x-model="config.dimension" class="form-select border-0 bg-light rounded-3">
                        <option value="month">Thời gian (Theo Tháng)</option>
                        <option value="branch">Địa điểm (Chi nhánh)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">3. Chỉ số đo lường</label>
                    <select x-model="config.metric" class="form-select border-0 bg-light rounded-3">
                        <option value="sum">Tổng cộng (Sum)</option>
                        <option value="count">Số lượng (Count)</option>
                        <option value="avg">Trung bình (Avg)</option>
                    </select>
                </div>

                <div class="mb-4" x-data="{ open: false }" @click.outside="open = false">
                    <label class="form-label small fw-bold text-secondary">4. Chọn chi nhánh so sánh</label>
                    <div class="position-relative">
                        <button type="button" @click="open = !open" class="form-select border-0 bg-light rounded-3 text-start d-flex justify-content-between align-items-center">
                            <span x-text="getBranchLabel()"></span>
                        </button>
                        <div x-show="open" class="position-absolute top-100 start-0 w-100 mt-1 bg-white border rounded-3 shadow-lg z-3 p-2" style="display:none; max-height: 200px; overflow-y: auto;">
                            <?php foreach($branches as $b): ?>
                            <label class="d-flex align-items-center px-2 py-1 cursor-pointer hover-bg rounded">
                                <input type="checkbox" class="form-check-input me-2 mt-0" value="<?= $b['id'] ?>" @change="toggleBranch('<?= $b['id'] ?>', '<?= $b['name'] ?>')">
                                <span class="fs-7 text-dark"><?= htmlspecialchars($b['name']) ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <button @click="generateReport()" :disabled="loading" class="btn btn-primary w-100 fw-bold rounded-3 py-2 shadow-sm">
                    <span x-show="loading" class="spinner-border spinner-border-sm me-2"></span>
                    <i class="bi bi-play-circle me-1" x-show="!loading"></i> XUẤT BÁO CÁO
                </button>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="clean-card p-4 rounded-4 shadow-sm bg-white border h-100">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h6 class="fw-bold mb-0">Biểu đồ phân tích thực tế</h6>
                    <div class="btn-group p-1 bg-light rounded-3">
                        <button @click="config.chartType = 'bar'; updateChart()" class="btn btn-sm" :class="config.chartType == 'bar' ? 'btn-white shadow-sm fw-bold' : 'btn-light border-0'"><i class="bi bi-bar-chart"></i></button>
                        <button @click="config.chartType = 'line'; updateChart()" class="btn btn-sm" :class="config.chartType == 'line' ? 'btn-white shadow-sm fw-bold' : 'btn-light border-0'"><i class="bi bi-graph-up"></i></button>
                        <button @click="config.chartType = 'pie'; updateChart()" class="btn btn-sm" :class="config.chartType == 'pie' ? 'btn-white shadow-sm fw-bold' : 'btn-light border-0'"><i class="bi bi-pie-chart"></i></button>
                    </div>
                </div>

                <div class="chart-container" style="position: relative; height:400px; width:100%">
                    <canvas id="customReportChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let myDynamicChart = null; 

function ReportBuilder() {
    return {
        loading: false,
        
        currentLabels: [],
        currentValues: [],
        
        config: {
            dataSource: 'invoices',
            dimension: 'month',
            metric: 'sum',
            chartType: 'bar',
            selectedBranches: []
        },

        getBranchLabel() {
            if (this.config.selectedBranches.length === 0) return '-- Tất cả chi nhánh --';
            return 'Đã chọn ' + this.config.selectedBranches.length + ' chi nhánh';
        },

        toggleBranch(id) {
            const idx = this.config.selectedBranches.indexOf(id);
            if (idx > -1) this.config.selectedBranches.splice(idx, 1);
            else this.config.selectedBranches.push(id);
        },

        async generateReport() {
            this.loading = true;
            const formData = new FormData();
            formData.append('data_source', this.config.dataSource);
            formData.append('dimension', this.config.dimension);
            formData.append('metric', this.config.metric);
            formData.append('filter_branch_id', this.config.selectedBranches.join(','));

            try {
                const res = await fetch('/api/reports/generate', { method: 'POST', body: formData });
                const data = await res.json();
                
                if (data.status === 'success') {
                    this.currentLabels = data.labels;
                    this.currentValues = data.values;
                    
                    this.renderChart();
                } else {
                    alert(data.message || 'Có lỗi xảy ra');
                }
            } catch (e) {
                console.error("Lỗi vẽ biểu đồ:", e);
            }
            this.loading = false;
        },

        renderChart() {
            const ctx = document.getElementById('customReportChart').getContext('2d');
            
            if (myDynamicChart) {
                myDynamicChart.destroy();
            }

            let bgColors = 'rgba(37, 99, 235, 0.2)';
            let borderColors = 'rgb(37, 99, 235)';
            let showLegend = false;

            if (this.config.chartType === 'pie') {
                bgColors = ['#f25f5c', '#ffe066', '#247ba0', '#70c1b3', '#50514f', '#ffb5a7', '#9b5de5'];
                borderColors = '#ffffff';
                showLegend = true;
            }

            myDynamicChart = new Chart(ctx, {
                type: this.config.chartType,
                data: {
                    labels: this.currentLabels,
                    datasets: [{
                        label: 'Giá trị thống kê',
                        data: this.currentValues,
                        backgroundColor: bgColors,
                        borderColor: borderColors,
                        borderWidth: 2,
                        tension: 0.3 
                    }]
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: showLegend } 
                    }
                }
            });
        },

        updateChart() {
            if (this.currentLabels.length > 0) {
                this.renderChart();
            }
        }
    }
}
</script>

<?php $this->endSection() ?>