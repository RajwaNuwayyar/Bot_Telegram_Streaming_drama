<!-- Modal QRIS Pembayaran -->
<div id="qris-modal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/80 backdrop-blur-md hidden transition-all duration-300 px-0 sm:px-4 py-0 sm:py-6">
    
    <!-- Modal Card -->
    <div class="w-full max-w-md bg-cardbg border border-white/10 sm:rounded-3xl rounded-t-3xl p-5 shadow-2xl relative max-h-[90vh] overflow-y-auto hide-scroll animate-slide-up">
        
        <!-- Drag Handle for Mobile -->
        <div class="w-12 h-1 bg-white/20 rounded-full mx-auto mb-4 sm:hidden"></div>

        <!-- View 1: Tampilan QRIS Checkout -->
        <div id="qris-checkout-view">
            <!-- Header Modal -->
            <div class="flex justify-between items-start mb-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-accent/20 text-accent uppercase tracking-wider">Checkout QRIS</span>
                        <span id="modal-plan-badge" class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-400 uppercase tracking-wider">VIP</span>
                    </div>
                    <h2 id="modal-plan-name" class="text-lg font-bold text-white leading-tight">VIP 30 Hari</h2>
                </div>
                <button onclick="closeQrisModal()" class="w-8 h-8 rounded-full bg-white/5 hover:bg-white/10 flex items-center justify-center text-textmuted hover:text-white transition-all">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Total Price Header -->
            <div class="bg-darkbg/60 border border-white/5 rounded-2xl p-3 mb-4 flex justify-between items-center">
                <span class="text-xs text-textmuted">Total Pembayaran:</span>
                <span id="modal-plan-price" class="text-xl font-bold text-accent">Rp 35.000</span>
            </div>

            <!-- Card QRIS Content (Kotak Putih Presisi 1:1 Mengikuti Ukuran QR) -->
            <div class="flex justify-center mb-3">
                <div class="bg-white rounded-3xl p-3 shadow-2xl border-2 border-accent/40 inline-flex justify-center items-center">
                    <canvas id="qris-canvas" class="w-56 h-56 sm:w-60 sm:h-60 rounded-xl block"></canvas>
                    <div id="qris-qrcode-js" class="hidden"></div>
                </div>
            </div>

            <!-- Kode TRX Badge (Di luar kotak QRIS) -->
            <div class="flex justify-center mb-4">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-darkbg/80 border border-white/10 rounded-full text-xs font-mono text-gray-200 font-semibold shadow-inner">
                    <span class="text-textmuted text-[10px]">TRX:</span>
                    <span id="modal-trx-code">TRX-849201</span>
                    <button onclick="copyTrxCode()" class="text-accent hover:text-white transition-colors ml-1" title="Salin Kode">
                        <i class="fa-regular fa-copy"></i>
                    </button>
                </div>
            </div>

            <!-- E-Wallet Supported Logos -->
            <div class="flex justify-center items-center gap-2.5 mb-4 px-2 py-1.5 bg-darkbg/40 rounded-xl border border-white/5">
                <span class="text-[10px] text-textmuted font-medium">Bisa pakai:</span>
                <span class="text-[10px] font-bold text-blue-400">GoPay</span>
                <span class="text-white/20">•</span>
                <span class="text-[10px] font-bold text-purple-400">OVO</span>
                <span class="text-white/20">•</span>
                <span class="text-[10px] font-bold text-blue-500">DANA</span>
                <span class="text-white/20">•</span>
                <span class="text-[10px] font-bold text-orange-400">ShopeePay</span>
                <span class="text-white/20">•</span>
                <span class="text-[10px] font-bold text-red-400">BCA/Bank</span>
            </div>

            <!-- Countdown Timer -->
            <div class="bg-amber-500/10 border border-amber-500/20 rounded-xl p-3 mb-4 flex items-center justify-between">
                <div class="flex items-center gap-2 text-amber-400">
                    <i class="fa-regular fa-clock text-sm animate-pulse"></i>
                    <span class="text-xs font-medium">Batas Waktu Bayar:</span>
                </div>
                <span id="modal-timer" class="text-sm font-bold font-mono text-amber-400">14:59</span>
            </div>

            <!-- Step by Step Instructions -->
            <div class="bg-darkbg/40 rounded-xl p-3 text-xs mb-5 text-gray-300 space-y-1.5 border border-white/5">
                <p class="font-semibold text-white mb-1">Cara Pembayaran:</p>
                <div class="flex items-start gap-2 text-[11px] text-textmuted">
                    <span class="w-4 h-4 rounded-full bg-accent/20 text-accent font-bold text-[10px] flex items-center justify-center shrink-0">1</span>
                    <span>Buka aplikasi GoPay, OVO, DANA, ShopeePay, atau Mobile Banking.</span>
                </div>
                <div class="flex items-start gap-2 text-[11px] text-textmuted">
                    <span class="w-4 h-4 rounded-full bg-accent/20 text-accent font-bold text-[10px] flex items-center justify-center shrink-0">2</span>
                    <span>Pilih menu <strong>Scan / Bayar QRIS</strong> dan arahkan kamera ke gambar QR di atas.</span>
                </div>
                <div class="flex items-start gap-2 text-[11px] text-textmuted">
                    <span class="w-4 h-4 rounded-full bg-accent/20 text-accent font-bold text-[10px] flex items-center justify-center shrink-0">3</span>
                    <span>Periksa nama merchant (<strong>DRAMABOT VIP</strong>) dan tekan <strong>Bayar</strong>.</span>
                </div>
            </div>

            <!-- Buttons Action -->
            <div class="flex flex-col gap-2">
                <button id="btn-check-status" onclick="checkQrisStatus()" class="w-full py-3 bg-accent hover:bg-accentdark text-darkbg font-bold rounded-xl text-sm transition-all shadow-[0_0_15px_rgba(0,208,182,0.3)] flex items-center justify-center gap-2 active:scale-[0.98]">
                    <i id="icon-check-status" class="fa-solid fa-arrows-rotate"></i>
                    <span>Cek Status Pembayaran</span>
                </button>
                
                <!-- Dev Helper Simulation Button -->
                <button onclick="simulateQrisPayment()" class="w-full py-2.5 bg-purple-600/30 hover:bg-purple-600/50 text-purple-200 border border-purple-500/30 font-semibold rounded-xl text-xs transition-all flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-wand-magic-sparkles text-purple-400"></i>
                    <span>Simulasi Bayar Berhasil (Test Mode)</span>
                </button>
            </div>
        </div>

        <!-- View 2: Tampilan Sukses Pembayaran VIP -->
        <div id="qris-success-view" class="hidden text-center py-4">
            <!-- Animated Success Icon -->
            <div class="w-20 h-20 bg-emerald-500/20 text-emerald-400 border-2 border-emerald-500/40 rounded-full flex items-center justify-center mx-auto mb-4 animate-bounce">
                <i class="fa-solid fa-check text-4xl"></i>
            </div>

            <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-400 uppercase tracking-widest">Pembayaran Diterima</span>
            <h2 class="text-2xl font-bold text-white mt-2 mb-1">VIP Berhasil Aktif! 🎉</h2>
            <p class="text-xs text-textmuted mb-6">Terima kasih, pembayaran paket VIP Anda telah terverifikasi secara otomatis.</p>

            <div class="bg-darkbg/80 border border-white/10 rounded-2xl p-4 text-left mb-6 space-y-2.5">
                <div class="flex justify-between text-xs">
                    <span class="text-textmuted">Paket VIP:</span>
                    <span id="success-plan-name" class="font-bold text-white">VIP 30 Hari</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-textmuted">Kode Transaksi:</span>
                    <span id="success-trx-code" class="font-mono text-accent font-semibold">TRX-849201</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-textmuted">Total Bayar:</span>
                    <span id="success-amount" class="font-bold text-white">Rp 35.000</span>
                </div>
                <div class="border-t border-white/5 pt-2 flex justify-between text-xs">
                    <span class="text-textmuted">Status VIP Aktif Hingga:</span>
                    <span id="success-vip-until" class="font-bold text-emerald-400">01 Nov 2026</span>
                </div>
            </div>

            <button onclick="finishVipSuccess()" class="w-full py-3.5 bg-gradient-to-r from-accent to-emerald-400 text-darkbg font-bold rounded-xl text-sm transition-all shadow-[0_0_20px_rgba(0,208,182,0.4)] hover:opacity-95">
                🎬 Mulai Nonton Drama VIP
            </button>
        </div>

    </div>
</div>

<style>
@keyframes slideUp {
    from { transform: translateY(100%); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
.animate-slide-up {
    animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>

<script>
let qrisTimerInterval = null;
let currentTrxCode = '';
let currentPlanName = '';
let currentPlanPrice = 0;
let currentDurationDays = 30;

function processPayment(planId, amount) {
    const plans = {
        1: { name: 'VIP 1 Hari', price: 3000, days: 1 },
        2: { name: 'VIP 3 Hari', price: 6000, days: 3 },
        3: { name: 'VIP 7 Hari', price: 10000, days: 7 },
        4: { name: 'VIP 15 Hari', price: 20000, days: 15 },
        5: { name: 'VIP 30 Hari', price: 35000, days: 30 },
        6: { name: 'VIP 90 Hari', price: 90000, days: 90 },
        7: { name: 'VIP 365 Hari', price: 300000, days: 365 }
    };
    const p = plans[planId] || { name: 'VIP Plan', price: amount || 35000, days: 30 };
    openQrisModal(p.name, p.price, planId, p.days);
}

function openQrisModal(planName, price, planId, durationDays = 30) {
    currentPlanName = planName;
    currentPlanPrice = price;
    currentDurationDays = durationDays;
    
    // Generate Random TRX Code: TRX-XXXXXX
    const randomHex = Math.floor(100000 + Math.random() * 900000);
    currentTrxCode = 'TRX' + randomHex;

    // Set UI Texts
    document.getElementById('modal-plan-name').textContent = planName;
    document.getElementById('modal-plan-price').textContent = 'Rp ' + Number(price).toLocaleString('id-ID');
    document.getElementById('modal-trx-code').textContent = currentTrxCode;

    // Reset Views
    document.getElementById('qris-checkout-view').classList.remove('hidden');
    document.getElementById('qris-success-view').classList.add('hidden');

    // Draw QRIS Canvas
    drawQrisCanvas('qris-canvas', currentTrxCode, price);

    // Start 15 Minute Countdown Timer
    startQrisTimer(15 * 60);

    // Show Modal
    const modal = document.getElementById('qris-modal');
    modal.classList.remove('hidden');
}

function closeQrisModal() {
    const modal = document.getElementById('qris-modal');
    modal.classList.add('hidden');
    if (qrisTimerInterval) clearInterval(qrisTimerInterval);
}

function copyTrxCode() {
    navigator.clipboard.writeText(currentTrxCode).then(() => {
        alert('Kode Transaksi ' + currentTrxCode + ' berhasil disalin!');
    }).catch(() => {
        alert('Kode TRX: ' + currentTrxCode);
    });
}

function startQrisTimer(durationSeconds) {
    if (qrisTimerInterval) clearInterval(qrisTimerInterval);
    let timer = durationSeconds;
    const timerElem = document.getElementById('modal-timer');

    function updateDisplay() {
        const minutes = Math.floor(timer / 60);
        const seconds = timer % 60;
        timerElem.textContent = 
            (minutes < 10 ? '0' : '') + minutes + ':' + 
            (seconds < 10 ? '0' : '') + seconds;

        if (--timer < 0) {
            clearInterval(qrisTimerInterval);
            timerElem.textContent = 'KEDALUWARSA';
        }
    }
    updateDisplay();
    qrisTimerInterval = setInterval(updateDisplay, 1000);
}

function checkQrisStatus() {
    const btn = document.getElementById('btn-check-status');
    const icon = document.getElementById('icon-check-status');
    
    icon.classList.add('fa-spin');
    btn.disabled = true;

    // Simulate checking database/server backend
    fetch('/api/health')
        .then(res => res.json())
        .catch(() => ({}))
        .finally(() => {
            setTimeout(() => {
                icon.classList.remove('fa-spin');
                btn.disabled = false;
                alert('Pengecekan otomatis: Menunggu pembayaran masuk via QRIS...\n(Gunakan tombol "Simulasi Bayar" untuk pengujian instan)');
            }, 800);
        });
}

function simulateQrisPayment() {
    // Show success view inside modal
    document.getElementById('qris-checkout-view').classList.add('hidden');
    document.getElementById('qris-success-view').classList.remove('hidden');

    document.getElementById('success-plan-name').textContent = currentPlanName;
    document.getElementById('success-trx-code').textContent = currentTrxCode;
    document.getElementById('success-amount').textContent = 'Rp ' + Number(currentPlanPrice).toLocaleString('id-ID');

    // Calculate expiry date
    const expiryDate = new Date();
    expiryDate.setDate(expiryDate.getDate() + currentDurationDays);
    const options = { day: '2-digit', month: 'short', year: 'numeric' };
    document.getElementById('success-vip-until').textContent = expiryDate.toLocaleDateString('id-ID', options);

    if (qrisTimerInterval) clearInterval(qrisTimerInterval);
}

function finishVipSuccess() {
    closeQrisModal();
    // Redirect to home page to watch dramas
    window.location.href = '?page=home';
}

/**
 * Custom QR Code Canvas Generator
 * Renders a crisp, realistic QRIS pattern on canvas
 */
function drawQrisCanvas(canvasId, trxCode, price) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    
    const size = 300;
    canvas.width = size;
    canvas.height = size;
    
    // Background White
    ctx.fillStyle = '#FFFFFF';
    ctx.fillRect(0, 0, size, size);

    const matrixSize = 29; // 29x29 modules
    const cellSize = size / matrixSize;

    // Generate pseudo-random deterministic pattern based on TRX string
    function hashSeed(str) {
        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            hash = (hash << 5) - hash + str.charCodeAt(i);
            hash |= 0;
        }
        return Math.abs(hash);
    }

    const seed = hashSeed(trxCode + price);

    // Helper to draw Finder Pattern (the 3 square corners)
    function drawFinder(row, col) {
        const x = col * cellSize;
        const y = row * cellSize;
        const width = 7 * cellSize;

        // Outer black box
        ctx.fillStyle = '#000000';
        ctx.fillRect(x, y, width, width);

        // Inner white box
        ctx.fillStyle = '#FFFFFF';
        ctx.fillRect(x + cellSize, y + cellSize, 5 * cellSize, 5 * cellSize);

        // Center black box
        ctx.fillStyle = '#000000';
        ctx.fillRect(x + 2 * cellSize, y + 2 * cellSize, 3 * cellSize, 3 * cellSize);
    }

    // Draw 3 Finder Patterns
    drawFinder(1, 1);                  // Top-Left
    drawFinder(1, matrixSize - 8);     // Top-Right
    drawFinder(matrixSize - 8, 1);     // Bottom-Left

    // Draw alignment pattern (bottom right)
    function drawAlignment(row, col) {
        const x = col * cellSize;
        const y = row * cellSize;
        ctx.fillStyle = '#000000';
        ctx.fillRect(x, y, 5 * cellSize, 5 * cellSize);
        ctx.fillStyle = '#FFFFFF';
        ctx.fillRect(x + cellSize, y + cellSize, 3 * cellSize, 3 * cellSize);
        ctx.fillStyle = '#000000';
        ctx.fillRect(x + 2 * cellSize, y + 2 * cellSize, cellSize, cellSize);
    }
    drawAlignment(matrixSize - 9, matrixSize - 9);

    // Draw timing patterns
    ctx.fillStyle = '#000000';
    for (let i = 8; i < matrixSize - 8; i++) {
        if (i % 2 === 0) {
            ctx.fillRect(6 * cellSize, i * cellSize, cellSize, cellSize);
            ctx.fillRect(i * cellSize, 6 * cellSize, cellSize, cellSize);
        }
    }

    // Fill data modules
    ctx.fillStyle = '#000000';
    let moduleIndex = 0;
    for (let r = 0; r < matrixSize; r++) {
        for (let c = 0; c < matrixSize; c++) {
            // Skip finder patterns
            if ((r < 9 && c < 9) || (r < 9 && c > matrixSize - 10) || (r > matrixSize - 10 && c < 9)) continue;
            // Skip alignment
            if (r >= matrixSize - 10 && r <= matrixSize - 5 && c >= matrixSize - 10 && c <= matrixSize - 5) continue;
            // Skip timing
            if (r === 6 || c === 6) continue;

            moduleIndex++;
            const bit = (seed ^ (moduleIndex * 1337) ^ (r * 31 + c)) % 3 === 0;
            if (bit) {
                ctx.fillRect(c * cellSize, r * cellSize, cellSize, cellSize);
            }
        }
    }

    // Add Center Logo / QRIS Icon badge
    const logoSize = 60;
    const logoX = (size - logoSize) / 2;
    const logoY = (size - logoSize) / 2;
    ctx.fillStyle = '#FFFFFF';
    ctx.fillRect(logoX - 4, logoY - 4, logoSize + 8, logoSize + 8);
    ctx.fillStyle = '#E11D48'; // Red QRIS accent
    ctx.fillRect(logoX, logoY, logoSize, logoSize);
    
    ctx.fillStyle = '#FFFFFF';
    ctx.font = 'bold 16px sans-serif';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText('QRIS', size / 2, size / 2);
}
</script>
