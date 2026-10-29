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

            <!-- Card QRIS Content -->
            <div class="flex justify-center mb-3">
                <div class="bg-white rounded-3xl p-3 shadow-2xl border-2 border-accent/40 inline-flex justify-center items-center">
                    <img id="qris-image" src="" alt="Loading QR..." class="w-56 h-56 sm:w-60 sm:h-60 rounded-xl block object-cover">
                </div>
            </div>

            <!-- Tombol Aksi QRIS: Tampilkan di Chat Bot & Salin Link QRIS -->
            <div class="space-y-2 mb-4">
                <div class="grid grid-cols-2 gap-2">
                    <!-- Tombol Tampilkan di Chat Bot untuk Simpan/Download -->
                    <button type="button" id="btn-send-to-chat" onclick="sendQrisToChat()" class="w-full py-2.5 px-3 rounded-xl bg-sky-500/15 hover:bg-sky-500/25 border border-sky-500/40 text-sky-400 hover:text-white transition-all font-semibold text-xs flex items-center justify-center gap-1.5 shadow-sm active:scale-95">
                        <i id="send-chat-icon" class="fa-brands fa-telegram text-sm"></i>
                        <span id="send-chat-text">Tampilkan di Chat</span>
                    </button>

                    <!-- Tombol Salin Link QRIS (Menggantikan Salin Kode TRX) -->
                    <button type="button" onclick="copyQrisLink()" class="w-full py-2.5 px-3 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-gray-200 hover:text-white transition-all font-semibold text-xs flex items-center justify-center gap-1.5 shadow-sm active:scale-95">
                        <i id="copy-link-icon" class="fa-regular fa-copy text-xs"></i>
                        <span id="copy-link-text">Salin Link QRIS</span>
                    </button>
                </div>

                <!-- Info ID Transaksi (Statis untuk referensi) -->
                <div class="text-center">
                    <span class="text-[11px] font-mono text-textmuted">
                        ID Transaksi: <strong id="modal-trx-code" class="text-gray-300 font-medium select-all">-</strong>
                    </span>
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
                    <span>Pilih menu <strong>Scan / Bayar QRIS</strong> di e-wallet/bank, lalu scan QR di atas atau gunakan tombol <strong>Tampilkan di Chat</strong> untuk menyimpan gambar QRIS ke galeri HP.</span>
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
let qrisPollingInterval = null;
let currentTrxCode = '';
let currentQrisUrl = '';
let currentPlanName = '';
let currentPlanPrice = 0;
let currentDurationDays = 30;

function processPayment(planId, amount) {
    const plans = {
        1: { name: 'VIP 1 Hari', days: 1 },
        2: { name: 'VIP 3 Hari', days: 3 },
        3: { name: 'VIP 7 Hari', days: 7 },
        4: { name: 'VIP 15 Hari', days: 15 },
        5: { name: 'VIP 30 Hari', days: 30 },
        6: { name: 'VIP 90 Hari', days: 90 },
        7: { name: 'VIP 365 Hari', days: 365 }
    };
    const p = plans[planId] || { name: 'VIP Plan', days: 30 };
    
    const user = getTelegramUser();
    if (!user || !user.id) {
        openTelegramRequiredModal();
        return;
    }

    const url = `../payment/request_qris.php?tg_user_id=${user.id}&username=${encodeURIComponent(user.username || '')}&first_name=${encodeURIComponent(user.first_name || 'User')}&plan_id=${planId}`;
    
    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                openQrisModal(p.name, data.amount, planId, p.days, data.order_id, data.qr_url);
            } else {
                alert('Gagal membuat pesanan QRIS: ' + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kesalahan jaringan.');
        });
}

function openQrisModal(planName, price, planId, durationDays, orderId, qrUrl) {
    currentPlanName = planName;
    currentPlanPrice = price;
    currentDurationDays = durationDays;
    currentTrxCode = orderId;
    currentQrisUrl = qrUrl;

    // Set UI Texts
    document.getElementById('modal-plan-name').textContent = planName;
    document.getElementById('modal-plan-price').textContent = 'Rp ' + Number(price).toLocaleString('id-ID');
    document.getElementById('modal-trx-code').textContent = currentTrxCode;
    
    // Reset Copy Link & Send Chat Button State
    resetCopyLinkButton();
    resetSendChatButton();

    // Set Image QR
    document.getElementById('qris-image').src = qrUrl;

    // Reset Views
    document.getElementById('qris-checkout-view').classList.remove('hidden');
    document.getElementById('qris-success-view').classList.add('hidden');

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
    if (qrisPollingInterval) clearInterval(qrisPollingInterval);
}

function copyQrisLink() {
    if (!currentQrisUrl) {
        alert('Tautan QRIS belum tersedia.');
        return;
    }

    const textToCopy = currentQrisUrl;
    
    // Fungsi fallback clipboard untuk browser webview / mobile
    const copyFallback = (text) => {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        let successful = false;
        try {
            successful = document.execCommand('copy');
        } catch (err) {
            successful = false;
        }
        document.body.removeChild(textArea);
        return successful;
    };

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(textToCopy).then(() => {
            showCopyLinkSuccess();
        }).catch(() => {
            if (copyFallback(textToCopy)) {
                showCopyLinkSuccess();
            } else {
                prompt('Salin link QRIS berikut:', textToCopy);
            }
        });
    } else {
        if (copyFallback(textToCopy)) {
            showCopyLinkSuccess();
        } else {
            prompt('Salin link QRIS berikut:', textToCopy);
        }
    }
}

function showCopyLinkSuccess() {
    const textElem = document.getElementById('copy-link-text');
    const iconElem = document.getElementById('copy-link-icon');
    if (textElem && iconElem) {
        textElem.textContent = 'Link Tersalin!';
        iconElem.className = 'fa-solid fa-check text-xs text-accent';
        setTimeout(resetCopyLinkButton, 2500);
    }
}

function resetCopyLinkButton() {
    const textElem = document.getElementById('copy-link-text');
    const iconElem = document.getElementById('copy-link-icon');
    if (textElem && iconElem) {
        textElem.textContent = 'Salin Link QRIS';
        iconElem.className = 'fa-regular fa-copy text-xs';
    }
}

function sendQrisToChat() {
    if (!currentTrxCode) {
        alert('Data transaksi belum tersedia.');
        return;
    }

    const user = getTelegramUser();
    const userId = (user && user.id) ? user.id : '';

    const btn = document.getElementById('btn-send-to-chat');
    const txt = document.getElementById('send-chat-text');
    const ico = document.getElementById('send-chat-icon');

    if (btn) btn.disabled = true;
    if (txt) txt.textContent = 'Mengirim...';
    if (ico) ico.className = 'fa-solid fa-spinner fa-spin text-sm';

    const url = `../payment/send_to_chat.php?order_id=${encodeURIComponent(currentTrxCode)}&tg_user_id=${encodeURIComponent(userId)}`;

    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (txt) txt.textContent = 'Terkirim ke Chat!';
                if (ico) ico.className = 'fa-solid fa-check text-emerald-400 text-sm';
                
                setTimeout(() => {
                    const openBot = confirm('✅ Transaksi & Gambar QRIS berhasil dikirim ke ruang chat bot Telegram Anda!\n\nApakah Anda ingin membuka ruang chat bot sekarang untuk menyimpan/mengunduh gambar QRIS?');
                    if (openBot) {
                        const targetUrl = 'https://t.me/' + (data.bot_username || 'TreadLessBot');
                        if (window.Telegram && window.Telegram.WebApp && typeof window.Telegram.WebApp.openTelegramLink === 'function') {
                            window.Telegram.WebApp.openTelegramLink(targetUrl);
                        } else {
                            window.open(targetUrl, '_blank');
                        }
                    }
                }, 300);

                setTimeout(resetSendChatButton, 5000);
            } else {
                alert('Gagal mengirim ke chat: ' + (data.message || 'Error'));
                resetSendChatButton();
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kesalahan jaringan saat mengirim ke chat bot.');
            resetSendChatButton();
        })
        .finally(() => {
            if (btn) btn.disabled = false;
        });
}

function resetSendChatButton() {
    const txt = document.getElementById('send-chat-text');
    const ico = document.getElementById('send-chat-icon');
    if (txt && ico) {
        txt.textContent = 'Tampilkan di Chat';
        ico.className = 'fa-brands fa-telegram text-sm';
    }
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

    // Mulai polling status tiap 5 detik
    if (qrisPollingInterval) clearInterval(qrisPollingInterval);
    qrisPollingInterval = setInterval(() => {
        checkQrisStatus(true); // true = mode auto-polling
    }, 5000);
}

function checkQrisStatus(isAuto = false) {
    const btn = document.getElementById('btn-check-status');
    const icon = document.getElementById('icon-check-status');
    
    icon.classList.add('fa-spin');
    if (!isAuto) btn.disabled = true;

    fetch('../payment/check_status.php?order_id=' + encodeURIComponent(currentTrxCode))
        .then(res => res.json())
        .then(data => {
            if (data.success && (data.status === 'paid' || data.status === 'success')) {
                if (qrisPollingInterval) clearInterval(qrisPollingInterval);
                showSuccessPayment();
            } else {
                if (!isAuto) alert('Status: Belum Dibayar. Silakan selesaikan pembayaran lalu cek kembali.');
            }
        })
        .catch(() => {
            if (!isAuto) alert('Gagal mengecek status. Coba lagi.');
        })
        .finally(() => {
            icon.classList.remove('fa-spin');
            if (!isAuto) btn.disabled = false;
        });
}

function showSuccessPayment() {
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

function openTelegramRequiredModal() {
    const modal = document.getElementById('telegram-required-modal');
    if (modal) modal.classList.remove('hidden');
}

function closeTelegramRequiredModal() {
    const modal = document.getElementById('telegram-required-modal');
    if (modal) modal.classList.add('hidden');
}
</script>

<!-- Modal Peringatan Buka di Telegram -->
<div id="telegram-required-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-md hidden transition-all duration-300 p-4">
    <div class="w-full max-w-sm bg-cardbg border border-white/10 rounded-3xl p-6 shadow-2xl relative text-center animate-in zoom-in-95 duration-200">
        <div class="w-16 h-16 rounded-2xl bg-sky-500/10 border border-sky-500/20 text-sky-400 flex items-center justify-center text-3xl mx-auto mb-4">
            <i class="fa-brands fa-telegram"></i>
        </div>
        <h3 class="text-base font-bold text-white mb-2">Buka di Aplikasi Telegram</h3>
        <p class="text-xs text-textmuted leading-relaxed mb-6">
            Demi keamanan transaksi dan aktivasi otomatis paket VIP ke akun Anda, pembayaran QRIS wajib dilakukan melalui bot Telegram resmi <strong>@TreadLessBot</strong>.
        </p>
        <div class="space-y-2.5">
            <a href="https://t.me/TreadLessBot/myapp" target="_blank" class="w-full py-3.5 rounded-xl bg-accent hover:bg-accentdark text-darkbg font-bold text-xs shadow-lg shadow-accent/20 transition-all flex items-center justify-center gap-2 active:scale-95">
                <i class="fa-solid fa-paper-plane text-[11px]"></i>
                Buka di Bot Telegram
            </a>
            <button type="button" onclick="closeTelegramRequiredModal()" class="w-full py-2.5 rounded-xl bg-white/5 hover:bg-white/10 text-textmuted hover:text-white text-xs font-semibold transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>
