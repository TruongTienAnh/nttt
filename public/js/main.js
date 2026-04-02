
// Init Icons
lucide.createIcons();

// --- Demo Logic ---
const textarea = document.getElementById('demo-input');
const countDisplay = document.getElementById('word-count');
const overlay = document.getElementById('loading-overlay');
const resultEmpty = document.getElementById('result-empty');
const resultFilled = document.getElementById('result-filled');

// Word Count
textarea.addEventListener('input', function() {
    const text = this.value.trim();
    const words = text ? text.split(/\s+/).length : 0;
    countDisplay.innerText = words;
    if (words > 200) countDisplay.classList.add('text-danger');
    else countDisplay.classList.remove('text-danger');
});

// Clear Text
function clearText() {
    textarea.value = '';
    countDisplay.innerText = '0';
    textarea.focus();
    // Reset UI
    resultFilled.classList.remove('d-flex');
    resultFilled.classList.add('d-none');
    resultEmpty.classList.remove('d-none');
}

// Simulate Process
function simulateProcess(type) {
    const text = textarea.value.trim();
    if (!text) {
        alert("Vui lòng nhập văn bản để kiểm tra!");
        textarea.focus();
        return;
    }

    // Show loading (remove d-none)
    overlay.classList.remove('d-none');
    
    // Hide filled result temporarily
    resultFilled.classList.remove('d-flex');
    resultFilled.classList.add('d-none');

    // Elements to update
    const chartRing = document.getElementById('chart-ring');
    const chartScore = document.getElementById('chart-score');
    const chartLabel = document.getElementById('chart-label');
    const humanPercent = document.getElementById('human-percent');
    const aiPercent = document.getElementById('ai-percent');
    const resultMsg = document.getElementById('result-message');

    setTimeout(() => {
        overlay.classList.add('d-none'); // Hide loading
        resultEmpty.classList.add('d-none'); // Hide empty state
        
        // Show filled state (add d-flex, remove d-none)
        resultFilled.classList.remove('d-none');
        resultFilled.classList.add('d-flex');

        if(type === 'ai') {
            // AI Detected Simulation
            chartRing.style.background = 'conic-gradient(#dc3545 85%, #f1f5f9 0)';
            chartScore.innerText = '85%';
            chartScore.className = 'h3 fw-bold mb-0 text-danger';
            chartLabel.innerText = 'AI PROB';
            humanPercent.innerText = '15%';
            aiPercent.innerText = '85%';
            resultMsg.innerHTML = '<i data-lucide="alert-triangle" style="width:16px;"></i> Khả năng cao do AI viết!';
            resultMsg.className = 'text-danger fw-bold mb-0 d-flex align-items-center justify-content-center gap-2';
        } else {
            // Good Result
            chartRing.style.background = 'conic-gradient(#22c55e 100%, #f1f5f9 0)';
            chartScore.innerText = '100%';
            chartScore.className = 'h3 fw-bold mb-0 text-success';
            chartLabel.innerText = 'UNIQUE';
            humanPercent.innerText = '100%';
            aiPercent.innerText = '0%';
            resultMsg.innerHTML = '<i data-lucide="check-circle-2" style="width:16px;"></i> Văn bản hoàn toàn sạch!';
            resultMsg.className = 'text-success fw-bold mb-0 d-flex align-items-center justify-content-center gap-2';
        }
        
        lucide.createIcons();
    }, 1500);
}

// --- Deposit Logic ---
function selectAmount(amount) {
    const input = document.getElementById('deposit-amount');
    input.value = amount;
    
    // Toggle active styles for buttons
    document.querySelectorAll('.deposit-btn').forEach(btn => {
        btn.classList.remove('btn-primary', 'text-white');
        btn.classList.add('btn-outline-secondary');
    });
    event.currentTarget.classList.remove('btn-outline-secondary');
    event.currentTarget.classList.add('btn-primary', 'text-white');
}

function processDeposit() {
    const input = document.getElementById('deposit-amount');
    const amount = parseInt(input.value);

    if (!amount || amount < 10000) {
        alert('Vui lòng nhập số tiền nạp tối thiểu là 10.000 VNĐ');
        return;
    }
    
    const formattedAmount = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
    if(confirm(`Bạn đang thực hiện nạp ${formattedAmount} vào ví VMIED.\n\nChuyển đến cổng thanh toán?`)) {
        alert('Đang chuyển hướng...');
    }
}