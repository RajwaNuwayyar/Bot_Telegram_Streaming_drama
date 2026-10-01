// ============================================================
// DramaStream Mini App - i18n Translation System
// Supports: English (en) & Bahasa Indonesia (id)
// Storage : localStorage key = 'dramastream_lang'
// ============================================================

const i18nDict = {
    en: {
        // Navigation
        "nav_home": "Home",
        "nav_history": "History",
        "nav_vip": "VIP",
        "nav_profile": "Profile",

        // Profile & Header
        "view_profile": "View Profile",
        "manage_vip": "Manage VIP",
        "profile_title": "Profile",
        "joined": "Joined",
        "watched": "Watched",
        "completed": "Completed",
        "renew_upgrade": "Renew or upgrade your plan",
        "affiliate_program": "Affiliate Program",
        "earn_commission": "Earn up to 18% commission",
        "language": "Language",
        "current_lang": "English",
        "select_language": "Select Language",
        "lang_en": "English",
        "lang_id": "Bahasa Indonesia",
        "cancel": "Cancel",

        // Home Page
        "search_placeholder": "Search dramas...",
        "cant_find_drama": "Can't find your drama?",
        "admin_upload": "Admin will upload it for you!",
        "request_btn": "Request",
        "genre_all": "All",
        "genre_romance": "Romance",
        "genre_thriller": "Thriller",
        "genre_drama": "Drama",
        "genre_fantasy": "Fantasy",
        "trending_now": "Trending Now",
        "see_all": "See all",

        // VIP Page
        "vip_title": "VIP Member",
        "vip_subtitle": "Full access to all drama content",
        "vip_feat_1": "Unlimited access to all episodes",
        "vip_feat_2": "Ad-free streaming experience",
        "vip_feat_3": "Early access to new drama releases",
        "vip_feat_4": "Exclusive VIP profile badge",
        "choose_plan": "Choose VIP Plan",
        "plan_1d": "VIP 1 Day",
        "plan_1d_desc": "Full access for 1 day",
        "plan_3d": "VIP 3 Days",
        "plan_7d": "VIP 7 Days",
        "plan_15d": "VIP 15 Days",
        "plan_30d": "VIP 30 Days",
        "plan_90d": "VIP 90 Days",
        "plan_365d": "VIP 365 Days",
        "per_1d": "/ 1 day",
        "per_3d": "/ 3 days",
        "per_7d": "/ 7 days",
        "per_15d": "/ 15 days",
        "per_30d": "/ 30 days",
        "per_90d": "/ 90 days",
        "per_1y": "/ 1 year",
        "best_value": "BEST VALUE",
        "save_tag": "SAVINGS",
        "save_33": "SAVE 33%",

        // Request Page
        "request_title": "Request Drama",
        "request_info_title": "Can't find what you're looking for?",
        "request_info_desc": "Submit a request and our admin will try to add it to the catalog as soon as possible. VIP members get priority processing!",
        "max_requests": "Max 2 requests/day",
        "submit_request_heading": "Submit Request",
        "label_drama_title": "Drama Title",
        "label_source_app": "Source / App (Optional)",
        "label_notes": "Additional Notes (Optional)",
        "placeholder_drama_title": "e.g. Queen of Tears",
        "placeholder_source_app": "e.g. Netflix, Viu, Iqiyi",
        "placeholder_notes": "Any specific year or version?",
        "btn_submit_request": "Submit Request",
        "recent_requests": "My Recent Requests",
        "status_pending": "Pending",
        "status_completed": "Completed",
        "already_available": "Already available in catalog",
        "watch_now": "WATCH NOW",

        // History Page
        "history_title": "Watch History",
        "clear_all": "Clear All",
        "filter_all": "All",
        "filter_in_progress": "In Progress",
        "filter_completed": "Completed",
        "watched_percent": "watched",
        "no_history_title": "No history found",
        "no_history_desc": "No watched dramas match this filter category.",
        "confirm_clear_desc": "Are you sure you want to clear your watch history? This action cannot be undone.",

        // Affiliate Page
        "affiliate_title": "Affiliate Program",
        "total_commission": "Total Commission",
        "ready_withdraw": "Ready to withdraw",
        "btn_withdraw": "Withdraw Funds",
        "referral_link": "Your Referral Link",
        "share_link_desc": "Share this link to earn 18% commission",
        "commission_tiers": "Commission Tiers",
        "all_users": "All users",
        "current_tier": "Current",
        "sales_req_1": "> Rp 1M referral sales",
        "sales_req_3": "> Rp 3M referral sales",
        "sales_req_5": "> Rp 5M referral sales"
    },
    id: {
        // Navigation
        "nav_home": "Beranda",
        "nav_history": "Riwayat",
        "nav_vip": "VIP",
        "nav_profile": "Profil",

        // Profile & Header
        "view_profile": "Lihat Profil",
        "manage_vip": "Kelola VIP",
        "profile_title": "Profil",
        "joined": "Bergabung",
        "watched": "Ditonton",
        "completed": "Selesai",
        "renew_upgrade": "Perpanjang atau tingkatkan paket",
        "affiliate_program": "Program Afiliasi",
        "earn_commission": "Dapatkan komisi hingga 18%",
        "language": "Bahasa",
        "current_lang": "Bahasa Indonesia",
        "select_language": "Pilih Bahasa",
        "lang_en": "English",
        "lang_id": "Bahasa Indonesia",
        "cancel": "Batal",

        // Home Page
        "search_placeholder": "Cari drama...",
        "cant_find_drama": "Tidak menemukan dramamu?",
        "admin_upload": "Admin akan menguploadnya untukmu!",
        "request_btn": "Request",
        "genre_all": "Semua",
        "genre_romance": "Romantis",
        "genre_thriller": "Thriller",
        "genre_drama": "Drama",
        "genre_fantasy": "Fantasi",
        "trending_now": "Sedang Tren",
        "see_all": "Lihat semua",

        // VIP Page
        "vip_title": "Anggota VIP",
        "vip_subtitle": "Akses penuh ke semua konten drama",
        "vip_feat_1": "Akses semua episode tanpa batas",
        "vip_feat_2": "Nonton tanpa iklan",
        "vip_feat_3": "Akses awal drama terbaru",
        "vip_feat_4": "Badge VIP eksklusif",
        "choose_plan": "Pilih Paket VIP",
        "plan_1d": "VIP 1 Hari",
        "plan_1d_desc": "Akses penuh selama 1 hari",
        "plan_3d": "VIP 3 Hari",
        "plan_7d": "VIP 7 Hari",
        "plan_15d": "VIP 15 Hari",
        "plan_30d": "VIP 30 Hari",
        "plan_90d": "VIP 90 Hari",
        "plan_365d": "VIP 365 Hari",
        "per_1d": "/ 1 hari",
        "per_3d": "/ 3 hari",
        "per_7d": "/ 7 hari",
        "per_15d": "/ 15 hari",
        "per_30d": "/ 30 hari",
        "per_90d": "/ 90 hari",
        "per_1y": "/ 1 tahun",
        "best_value": "PALING LARIS",
        "save_tag": "HEMAT",
        "save_33": "HEMAT 33%",

        // Request Page
        "request_title": "Request Drama",
        "request_info_title": "Tidak menemukan yang kamu cari?",
        "request_info_desc": "Kirim permintaan drama dan admin kami akan menambahkannya secepat mungkin. Anggota VIP mendapat prioritas!",
        "max_requests": "Maks 2 request/hari",
        "submit_request_heading": "Kirim Request",
        "label_drama_title": "Judul Drama",
        "label_source_app": "Sumber / Aplikasi (Opsional)",
        "label_notes": "Catatan Tambahan (Opsional)",
        "placeholder_drama_title": "contoh: Queen of Tears",
        "placeholder_source_app": "contoh: Netflix, Viu, Iqiyi",
        "placeholder_notes": "Ada tahun atau versi tertentu?",
        "btn_submit_request": "Kirim Request",
        "recent_requests": "Request Terakhir Saya",
        "status_pending": "Menunggu",
        "status_completed": "Selesai",
        "already_available": "Sudah tersedia di katalog",
        "watch_now": "NONTON SEKARANG",

        // History Page
        "history_title": "Riwayat Nonton",
        "clear_all": "Hapus Semua",
        "filter_all": "Semua",
        "filter_in_progress": "Sedang Ditonton",
        "filter_completed": "Selesai",
        "watched_percent": "ditonton",
        "no_history_title": "Tidak ada riwayat",
        "no_history_desc": "Tidak ada drama yang cocok dengan kategori filter ini.",
        "confirm_clear_desc": "Apakah Anda yakin ingin menghapus semua riwayat nonton? Tindakan ini tidak dapat dibatalkan.",

        // Affiliate Page
        "affiliate_title": "Program Afiliasi",
        "total_commission": "Total Komisi",
        "ready_withdraw": "Siap ditarik",
        "btn_withdraw": "Tarik Dana",
        "referral_link": "Link Referral Anda",
        "share_link_desc": "Bagikan link ini untuk mendapat komisi 18%",
        "commission_tiers": "Tingkat Komisi",
        "all_users": "Semua pengguna",
        "current_tier": "Saat Ini",
        "sales_req_1": "> Rp 1Jt penjualan referral",
        "sales_req_3": "> Rp 3Jt penjualan referral",
        "sales_req_5": "> Rp 5Jt penjualan referral"
    }
};

// Functions to manage language
function getCurrentLang() {
    return localStorage.getItem('dramastream_lang') || 'en';
}

function setLanguage(lang) {
    if (lang !== 'en' && lang !== 'id') return;
    localStorage.setItem('dramastream_lang', lang);
    applyTranslations();
}

function applyTranslations() {
    const lang = getCurrentLang();
    const dict = i18nDict[lang] || i18nDict['en'];

    // Update html lang attribute
    document.documentElement.lang = lang;

    // Elements with data-i18n attribute
    document.querySelectorAll('[data-i18n]').forEach(elem => {
        const key = elem.getAttribute('data-i18n');
        if (dict[key]) {
            elem.textContent = dict[key];
        }
    });

    // Elements with data-i18n-placeholder attribute
    document.querySelectorAll('[data-i18n-placeholder]').forEach(elem => {
        const key = elem.getAttribute('data-i18n-placeholder');
        if (dict[key]) {
            elem.setAttribute('placeholder', dict[key]);
        }
    });

    // Update active state in language modal if present
    const checkEn = document.getElementById('lang-check-en');
    const checkId = document.getElementById('lang-check-id');
    const optionEn = document.getElementById('lang-option-en');
    const optionId = document.getElementById('lang-option-id');

    if (checkEn && checkId && optionEn && optionId) {
        if (lang === 'en') {
            checkEn.classList.remove('hidden');
            checkId.classList.add('hidden');
            optionEn.classList.add('border-accent', 'bg-accent/10');
            optionId.classList.remove('border-accent', 'bg-accent/10');
        } else {
            checkId.classList.remove('hidden');
            checkEn.classList.add('hidden');
            optionId.classList.add('border-accent', 'bg-accent/10');
            optionEn.classList.remove('border-accent', 'bg-accent/10');
        }
    }

    // Trigger custom event for dynamic components if needed
    window.dispatchEvent(new CustomEvent('languageChanged', { detail: { lang: lang } }));
}

// Automatically apply translations on page load
document.addEventListener('DOMContentLoaded', () => {
    applyTranslations();
});
