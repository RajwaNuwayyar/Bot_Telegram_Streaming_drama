<!-- Request Drama View -->
<div class="px-5 pt-6 pb-4">
    <!-- Header -->
    <div class="flex justify-start items-center gap-3 mb-6">
        <a href="?page=home" class="w-8 h-8 rounded-full bg-cardbg border border-white/5 flex items-center justify-center text-textmuted hover:text-white transition-colors">
            <i class="fa-solid fa-chevron-left text-sm"></i>
        </a>
        <h1 data-i18n="request_title" class="text-xl font-bold">Request Drama</h1>
    </div>

    <!-- Info Banner -->
    <div class="bg-cardbg border border-accent/20 rounded-2xl p-4 mb-8 flex gap-4 items-start">
        <div class="w-10 h-10 rounded-full bg-accent/10 flex items-center justify-center shrink-0 text-accent">
            <i class="fa-solid fa-circle-info"></i>
        </div>
        <div>
            <h3 data-i18n="request_info_title" class="text-sm font-bold text-white mb-1">Can't find what you're looking for?</h3>
            <p data-i18n="request_info_desc" class="text-xs text-textmuted mb-2 leading-relaxed">Submit a request and our admin will try to add it to the catalog as soon as possible. VIP members get priority processing!</p>
            <div class="flex items-center gap-2">
                <span data-i18n="max_requests" class="bg-darkbg px-2 py-1 rounded border border-white/5 text-[9px] uppercase tracking-wider text-textmuted font-semibold">Max 2 requests/day</span>
            </div>
        </div>
    </div>

    <!-- Request Form -->
    <h3 data-i18n="submit_request_heading" class="text-sm font-bold mb-4 text-textmuted uppercase tracking-wider">Submit Request</h3>
    <form id="requestForm" class="flex flex-col gap-4 mb-8" onsubmit="submitRequest(event)">
        
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-white ml-1"><span data-i18n="label_drama_title">Drama Title</span> <span class="text-red-500">*</span></label>
            <input type="text" id="req_title" required data-i18n-placeholder="placeholder_drama_title" class="w-full bg-cardbg border border-white/5 rounded-xl py-3 px-4 text-sm text-white placeholder-textmuted/50 focus:outline-none focus:border-accent/50 transition-colors" placeholder="e.g. Queen of Tears">
        </div>

        <div class="flex flex-col gap-1.5">
            <label data-i18n="label_source_app" class="text-xs font-semibold text-white ml-1">Source / App (Optional)</label>
            <input type="text" id="req_source" data-i18n-placeholder="placeholder_source_app" class="w-full bg-cardbg border border-white/5 rounded-xl py-3 px-4 text-sm text-white placeholder-textmuted/50 focus:outline-none focus:border-accent/50 transition-colors" placeholder="e.g. Netflix, Viu, Iqiyi">
        </div>

        <div class="flex flex-col gap-1.5 mb-2">
            <label data-i18n="label_notes" class="text-xs font-semibold text-white ml-1">Additional Notes (Optional)</label>
            <textarea id="req_notes" rows="3" data-i18n-placeholder="placeholder_notes" class="w-full bg-cardbg border border-white/5 rounded-xl py-3 px-4 text-sm text-white placeholder-textmuted/50 focus:outline-none focus:border-accent/50 transition-colors resize-none" placeholder="Any specific year or version?"></textarea>
        </div>

        <button type="submit" data-i18n="btn_submit_request" class="w-full bg-accent hover:bg-accentdark text-darkbg font-bold py-3.5 rounded-xl shadow-[0_4px_14px_rgba(0,208,182,0.3)] transition-colors">
            Submit Request
        </button>
    </form>

    <!-- My Requests History -->
    <h3 data-i18n="recent_requests" class="text-sm font-bold mb-4 text-textmuted uppercase tracking-wider">My Recent Requests</h3>
    <div class="flex flex-col gap-3 pb-8">
        <?php
        $user_id_db = null;
        if (isset($_SESSION['telegram_user_id'])) {
            $userRow = getUserByTelegramId($pdo, $_SESSION['telegram_user_id']);
            if ($userRow) {
                $user_id_db = $userRow['id'];
            }
        }
        
        if ($user_id_db) {
            $stmt = $pdo->prepare("SELECT * FROM film_requests WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
            $stmt->execute([$user_id_db]);
            $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($requests)) {
                echo '<div class="text-center py-6 text-textmuted text-xs">Belum ada request.</div>';
            } else {
                foreach ($requests as $req) {
                    $statusClass = '';
                    $statusLabel = '';
                    if ($req['status'] == 'pending') {
                        $statusClass = 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20';
                        $statusLabel = 'Pending';
                    } elseif ($req['status'] == 'completed') {
                        $statusClass = 'bg-accent/10 text-accent border-accent/20';
                        $statusLabel = 'Completed';
                    } elseif ($req['status'] == 'rejected') {
                        $statusClass = 'bg-red-500/10 text-red-500 border-red-500/20';
                        $statusLabel = 'Rejected';
                    } else {
                        $statusClass = 'bg-blue-500/10 text-blue-500 border-blue-500/20';
                        $statusLabel = 'Processing';
                    }
                    
                    $timeAgo = timeAgoIndo($req['created_at']);
                    
                    echo '<div class="bg-cardbg border border-white/5 rounded-2xl p-4 flex flex-col gap-3">';
                    echo '    <div class="flex justify-between items-start">';
                    echo '        <div>';
                    echo '            <h4 class="font-bold text-sm text-white mb-0.5' . ($req['status'] == 'completed' ? ' line-through decoration-white/20' : '') . '">' . htmlspecialchars($req['requested_title']) . '</h4>';
                    echo '            <p class="text-[10px] text-textmuted">' . $timeAgo . '</p>';
                    echo '        </div>';
                    echo '        <span class="' . $statusClass . ' border px-2 py-0.5 rounded text-[9px] uppercase tracking-wider font-bold">' . $statusLabel . '</span>';
                    echo '    </div>';
                    
                    if ($req['status'] == 'completed') {
                        echo '    <div class="bg-darkbg rounded-lg p-2.5 flex items-center justify-between border border-white/5">';
                        echo '        <span class="text-xs text-white"><i class="fa-solid fa-check text-accent mr-1"></i> <span data-i18n="already_available">Telah tersedia di katalog</span></span>';
                        echo '        <a href="?page=home" data-i18n="watch_now" class="text-accent text-[10px] font-bold hover:underline">WATCH NOW</a>';
                        echo '    </div>';
                    }
                    echo '</div>';
                }
            }
        } else {
            echo '<div class="text-center py-6 text-textmuted text-xs">Silakan buka dari Telegram.</div>';
        }
        ?>
    </div>
</div>

<script>
function submitRequest(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button[type="submit"]');
    const oriText = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengirim...';
    btn.disabled = true;

    const payload = {
        title: document.getElementById('req_title').value,
        source: document.getElementById('req_source').value,
        notes: document.getElementById('req_notes').value
    };

    fetch('api/submit_request.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        btn.innerHTML = oriText;
        btn.disabled = false;
        if (data.success) {
            alert('Request berhasil dikirim! Admin akan segera memproses.');
            window.location.reload();
        } else {
            alert('Gagal mengirim request: ' + data.error);
        }
    })
    .catch(err => {
        btn.innerHTML = oriText;
        btn.disabled = false;
        alert('Terjadi kesalahan jaringan.');
    });
}
</script>
