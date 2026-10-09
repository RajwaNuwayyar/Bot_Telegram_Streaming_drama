<?php
$telegram_user_id = isset($_SESSION['telegram_user_id']) ? $_SESSION['telegram_user_id'] : null;
$user = null;
if ($telegram_user_id) {
    $user = getUserByTelegramId($pdo, $telegram_user_id);
}

// Fallback dummy if not found
if (!$user) {
    $user = ['id' => 0, 'coin_balance' => 0, 'total_referral_value' => 0, 'referral_code' => ''];
}

$bot_username = "TreadLessBot";
$balance = (int)$user['coin_balance'];
$ref_code = !empty($user['referral_code']) ? $user['referral_code'] : 'REF' . $user['id'];
$ref_link = "https://t.me/" . $bot_username . "?start=" . $ref_code;

// Calculate Level (Simplified logic)
$ref_value = (int)$user['total_referral_value'];
$level = 1;
if ($ref_value > 5000000) $level = 4;
elseif ($ref_value > 3000000) $level = 3;
elseif ($ref_value > 1000000) $level = 2;

// Fetch withdrawal history
$withdrawals = [];
if ($user['id'] > 0) {
    $stmt_wd = $pdo->prepare("SELECT * FROM affiliate_withdrawals WHERE user_id = ? ORDER BY requested_at DESC LIMIT 10");
    $stmt_wd->execute([$user['id']]);
    $withdrawals = $stmt_wd->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!-- Affiliate View -->
<div class="px-5 pt-6 pb-4">
    <!-- Header -->
    <div class="flex justify-start items-center gap-3 mb-6">
        <a href="?page=profile" class="w-8 h-8 rounded-full bg-cardbg border border-white/5 flex items-center justify-center text-textmuted hover:text-white transition-colors">
            <i class="fa-solid fa-chevron-left text-sm"></i>
        </a>
        <h1 data-i18n="affiliate_title" class="text-xl font-bold">Affiliate Program</h1>
    </div>

    <!-- Balance Card -->
    <div class="bg-gradient-to-br from-emerald-900 to-emerald-950 border border-emerald-500/30 rounded-3xl p-6 mb-6 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 opacity-10 text-[120px] text-emerald-500">
            <i class="fa-solid fa-users"></i>
        </div>
        
        <div class="relative z-10">
            <p data-i18n="total_commission" class="text-xs text-emerald-200 mb-1 uppercase tracking-widest font-semibold">Total Commission</p>
            <div class="flex items-end gap-2 mb-2">
                <h2 class="text-3xl font-bold text-white leading-none">Rp <?php echo number_format($balance, 0, ',', '.'); ?></h2>
            </div>
            
            <p data-i18n="ready_withdraw" class="text-[11px] text-emerald-200 mb-6">Ready to withdraw</p>
            
            <button onclick="openWithdrawModal()" data-i18n="btn_withdraw" class="w-full bg-emerald-500 hover:bg-emerald-400 text-darkbg font-bold py-3 rounded-xl shadow-lg transition-colors">
                Withdraw Funds
            </button>
        </div>
    </div>

    <!-- Referral Link section -->
    <h3 data-i18n="referral_link" class="text-sm font-bold mb-3 text-textmuted uppercase tracking-wider">Your Referral Link</h3>
    <div class="bg-cardbg border border-white/5 rounded-2xl p-4 flex items-center justify-between mb-8 gap-3">
        <div class="flex-1 min-w-0">
            <p data-i18n="share_link_desc" class="text-xs text-textmuted mb-1">Share this link to earn commission</p>
            <div class="text-sm text-white font-mono bg-darkbg p-2 rounded-lg truncate border border-white/5 select-all" id="ref-link-text">
                <?php echo htmlspecialchars($ref_link); ?>
            </div>
        </div>
        <button onclick="copyRefLink()" class="w-12 h-12 shrink-0 bg-emerald-500/10 text-emerald-500 rounded-xl flex items-center justify-center hover:bg-emerald-500/20 transition-colors">
            <i class="fa-regular fa-copy text-lg"></i>
        </button>
    </div>

    <!-- Commission Levels -->
    <h3 data-i18n="commission_tiers" class="text-sm font-bold mb-3 text-textmuted uppercase tracking-wider">Commission Tiers</h3>
    <div class="flex flex-col gap-3 pb-8">
        <div class="bg-cardbg border <?php echo $level == 1 ? 'border-emerald-500/30' : 'border-white/5'; ?> rounded-2xl p-4 flex items-center justify-between relative overflow-hidden">
            <?php if($level == 1) echo '<div class="absolute top-0 right-0 w-1.5 h-full bg-emerald-500"></div>'; ?>
            <div>
                <h4 class="font-bold text-sm text-white mb-0.5 flex items-center gap-2">Level 1 - Starter <?php if($level == 1) echo '<span data-i18n="current_tier" class="bg-emerald-500/20 text-emerald-500 px-1.5 py-0.5 rounded text-[8px] uppercase tracking-wider">Current</span>'; ?></h4>
                <p data-i18n="all_users" class="text-[11px] text-textmuted">All users</p>
            </div>
            <div class="text-right <?php echo $level == 1 ? 'mr-3' : ''; ?>">
                <span class="text-emerald-500 font-bold <?php echo $level == 1 ? 'text-lg' : ''; ?>">10%</span>
            </div>
        </div>
        
        <div class="bg-cardbg border <?php echo $level == 2 ? 'border-emerald-500/30' : 'border-white/5'; ?> rounded-2xl p-4 flex items-center justify-between relative overflow-hidden">
            <?php if($level == 2) echo '<div class="absolute top-0 right-0 w-1.5 h-full bg-emerald-500"></div>'; ?>
            <div>
                <h4 class="font-bold text-sm text-white mb-0.5 flex items-center gap-2">Level 2 - Pro <?php if($level == 2) echo '<span data-i18n="current_tier" class="bg-emerald-500/20 text-emerald-500 px-1.5 py-0.5 rounded text-[8px] uppercase tracking-wider">Current</span>'; ?></h4>
                <p data-i18n="sales_req_1" class="text-[11px] text-textmuted">> Rp 1M referral sales</p>
            </div>
            <div class="text-right <?php echo $level == 2 ? 'mr-3' : ''; ?>">
                <span class="text-emerald-500 font-bold <?php echo $level == 2 ? 'text-lg' : ''; ?>">12%</span>
            </div>
        </div>

        <div class="bg-cardbg border <?php echo $level == 3 ? 'border-emerald-500/30' : 'border-white/5'; ?> rounded-2xl p-4 flex items-center justify-between relative overflow-hidden">
            <?php if($level == 3) echo '<div class="absolute top-0 right-0 w-1.5 h-full bg-emerald-500"></div>'; ?>
            <div>
                <h4 class="font-bold text-sm text-white mb-0.5 flex items-center gap-2">Level 3 - Elite <?php if($level == 3) echo '<span data-i18n="current_tier" class="bg-emerald-500/20 text-emerald-500 px-1.5 py-0.5 rounded text-[8px] uppercase tracking-wider">Current</span>'; ?></h4>
                <p data-i18n="sales_req_3" class="text-[11px] text-textmuted">> Rp 3M referral sales</p>
            </div>
            <div class="text-right <?php echo $level == 3 ? 'mr-3' : ''; ?>">
                <span class="text-emerald-500 font-bold <?php echo $level == 3 ? 'text-lg' : ''; ?>">15%</span>
            </div>
        </div>

        <div class="bg-cardbg border <?php echo $level == 4 ? 'border-emerald-500/30' : 'border-white/5'; ?> rounded-2xl p-4 flex items-center justify-between <?php echo $level < 4 ? 'opacity-50' : 'relative overflow-hidden'; ?>">
            <?php if($level == 4) echo '<div class="absolute top-0 right-0 w-1.5 h-full bg-emerald-500"></div>'; ?>
            <div>
                <h4 class="font-bold text-sm text-white mb-0.5 flex items-center gap-2">Level 4 - Master <?php if($level == 4) echo '<span data-i18n="current_tier" class="bg-emerald-500/20 text-emerald-500 px-1.5 py-0.5 rounded text-[8px] uppercase tracking-wider">Current</span>'; ?></h4>
                <p data-i18n="sales_req_5" class="text-[11px] text-textmuted">> Rp 5M referral sales</p>
            </div>
            <div class="text-right <?php echo $level == 4 ? 'mr-3' : ''; ?>">
                <span class="text-emerald-500 font-bold <?php echo $level == 4 ? 'text-lg' : ''; ?>">18%</span>
                <?php if($level < 4) echo '<i class="fa-solid fa-lock text-[10px] ml-1 text-textmuted"></i>'; ?>
            </div>
        </div>
    </div>

    <!-- Withdrawal History -->
    <?php if (count($withdrawals) > 0): ?>
    <h3 class="text-sm font-bold mb-3 text-textmuted uppercase tracking-wider">Riwayat Penarikan</h3>
    <div class="flex flex-col gap-3 pb-8">
        <?php foreach ($withdrawals as $wd): 
            $status_color = 'text-yellow-400';
            $status_bg = 'bg-yellow-400/10';
            $icon = 'fa-clock';
            $status_text = 'Diproses';

            if ($wd['status'] == 'terkirim' || $wd['status'] == 'completed') {
                $status_color = 'text-emerald-500';
                $status_bg = 'bg-emerald-500/10';
                $icon = 'fa-check-circle';
                $status_text = 'Terkirim';
            } elseif ($wd['status'] == 'rejected') {
                $status_color = 'text-red-500';
                $status_bg = 'bg-red-500/10';
                $icon = 'fa-xmark-circle';
                $status_text = 'Ditolak';
            }
        ?>
        <div class="bg-cardbg border border-white/5 rounded-2xl p-4">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <span class="text-xs text-textmuted font-mono">#WD_<?php echo $wd['id']; ?></span>
                    <h4 class="font-bold text-white mt-1">Rp <?php echo number_format($wd['amount_after_fee'], 0, ',', '.'); ?></h4>
                </div>
                <div class="<?php echo $status_bg . ' ' . $status_color; ?> px-2 py-1 rounded-lg flex items-center gap-1.5 border border-current/20">
                    <i class="fa-solid <?php echo $icon; ?> text-[10px]"></i>
                    <span class="text-[10px] font-bold uppercase tracking-wider"><?php echo $status_text; ?></span>
                </div>
            </div>
            <div class="flex justify-between items-center text-xs text-textmuted">
                <span>DANA: <?php echo htmlspecialchars($wd['account_number']); ?></span>
                <span><?php echo date('d M Y, H:i', strtotime($wd['requested_at'])); ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>

<script>
function copyRefLink() {
    const text = document.getElementById('ref-link-text').innerText.trim();
    navigator.clipboard.writeText(text).then(() => {
        alert('Link referral berhasil disalin!');
    }).catch(err => {
        console.error('Failed to copy: ', err);
    });
}

function openWithdrawModal() {
    document.getElementById('withdrawModal').classList.remove('hidden');
    document.getElementById('withdrawModal').classList.add('flex');
}

function closeWithdrawModal() {
    document.getElementById('withdrawModal').classList.add('hidden');
    document.getElementById('withdrawModal').classList.remove('flex');
}

let selectedWithdrawAmount = 0;

function selectWithdrawAmount(amount, element) {
    selectedWithdrawAmount = amount;
    
    // Reset all buttons
    const btns = document.querySelectorAll('.wd-btn');
    btns.forEach(btn => {
        btn.classList.remove('border-emerald-500', 'bg-emerald-500/20');
        btn.classList.add('border-white/10', 'bg-darkbg');
    });
    
    // Highlight selected
    element.classList.remove('border-white/10', 'bg-darkbg');
    element.classList.add('border-emerald-500', 'bg-emerald-500/20');
}

function submitWithdrawal() {
    if (selectedWithdrawAmount === 0) {
        alert('Silakan pilih nominal penarikan.');
        return;
    }
    
    const danaNumber = document.getElementById('dana_number').value.trim();
    if (!danaNumber) {
        alert('Silakan masukkan nomor DANA Anda.');
        return;
    }
    
    const btn = document.getElementById('btnSubmitWithdraw');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';
    btn.disabled = true;

    fetch('api/withdraw.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            amount: selectedWithdrawAmount,
            dana_number: danaNumber
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message || 'Permintaan penarikan berhasil diajukan!');
            window.location.reload();
        } else {
            alert(data.message || 'Gagal mengajukan penarikan.');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    })
    .catch(err => {
        console.error(err);
        alert('Terjadi kesalahan sistem.');
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}
</script>

<!-- Withdraw Modal -->
<div id="withdrawModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-black/80 backdrop-blur-sm transition-opacity">
    <div class="bg-cardbg border border-white/10 rounded-3xl w-full max-w-sm overflow-hidden shadow-2xl transform scale-100 transition-transform">
        <div class="p-6">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-xl font-bold text-white">Withdraw Funds</h3>
                <button onclick="closeWithdrawModal()" class="text-textmuted hover:text-white w-8 h-8 flex items-center justify-center rounded-full bg-white/5">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            
            <p class="text-sm text-textmuted mb-4">Pilih nominal penarikan ke DANA:</p>
            
            <div class="grid grid-cols-3 gap-3 mb-6">
                <button onclick="selectWithdrawAmount(50000, this)" class="wd-btn py-3 px-1 rounded-xl border border-white/10 bg-darkbg hover:border-emerald-500/50 transition-colors text-center">
                    <div class="text-white font-bold text-sm">50K</div>
                </button>
                <button onclick="selectWithdrawAmount(75000, this)" class="wd-btn py-3 px-1 rounded-xl border border-white/10 bg-darkbg hover:border-emerald-500/50 transition-colors text-center">
                    <div class="text-white font-bold text-sm">75K</div>
                </button>
                <button onclick="selectWithdrawAmount(100000, this)" class="wd-btn py-3 px-1 rounded-xl border border-white/10 bg-darkbg hover:border-emerald-500/50 transition-colors text-center">
                    <div class="text-white font-bold text-sm">100K</div>
                </button>
            </div>
            
            <div class="mb-6">
                <label class="block text-xs text-emerald-200 mb-2 uppercase tracking-wider font-semibold">Nomor DANA</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-textmuted">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <input type="tel" id="dana_number" placeholder="Contoh: 08123456789" class="w-full bg-darkbg border border-white/10 text-white rounded-xl py-3 pl-10 pr-4 focus:outline-none focus:border-emerald-500 transition-colors placeholder-white/20">
                </div>
            </div>
            
            <button id="btnSubmitWithdraw" onclick="submitWithdrawal()" class="w-full bg-emerald-500 hover:bg-emerald-400 text-darkbg font-bold py-3.5 rounded-xl shadow-[0_0_15px_rgba(16,185,129,0.3)] transition-all flex justify-center items-center gap-2">
                <i class="fa-solid fa-paper-plane"></i> Ajukan Penarikan
            </button>
        </div>
    </div>
</div>

