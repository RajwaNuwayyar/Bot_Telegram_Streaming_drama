<?php
session_start();
// Cegah browser & Telegram WebView menyimpan cache tampilan lama
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

require_once __DIR__ . '/../database/koneksi.php';
require_once __DIR__ . '/includes/db_queries.php';

// Routing sederhana
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
$allowed_pages = ['home', 'history', 'vip', 'profile', 'affiliate', 'request'];

if (!in_array($page, $allowed_pages)) {
    $page = 'home';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>DramaStream Mini App</title>
    <!-- Telegram Web App SDK -->
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <!-- i18n Translation Script -->
    <script src="js/i18n.js"></script>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome (Icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Script Tema Awal (Mencegah Layar Kedip/FOIC) -->
    <script>
        (function() {
            try {
                const savedTheme = localStorage.getItem('dramastream_theme') || 'dark';
                if (savedTheme === 'light') {
                    document.documentElement.classList.add('light-theme');
                    document.documentElement.setAttribute('data-theme', 'light');
                } else {
                    document.documentElement.classList.remove('light-theme');
                    document.documentElement.setAttribute('data-theme', 'dark');
                }
            } catch (e) {}
        })();
    </script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        darkbg: 'var(--bg-main)',
                        cardbg: 'var(--bg-card)',
                        cardbghover: 'var(--bg-card-hover)',
                        accent: 'var(--accent)',
                        accentdark: 'var(--accent-dark)',
                        textmuted: 'var(--text-secondary)'
                    },
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        :root {
            --bg-main: #0A0E17;
            --bg-card: #141C2B;
            --bg-card-hover: #1A2438;
            --bg-input: #141C2B;
            --text-primary: #FFFFFF;
            --text-secondary: #8A99AF;
            --accent: #00D0B6;
            --accent-dark: #00A38F;
            --border-subtle: rgba(255, 255, 255, 0.06);
            --border-card: rgba(255, 255, 255, 0.08);
            --glass-nav: rgba(20, 28, 43, 0.88);
            --glass-border: rgba(255, 255, 255, 0.05);
            --popup-bg: #141C2B;
            --modal-bg: #141C2B;
            --shadow-card: 0 4px 20px -2px rgba(0, 0, 0, 0.4);
        }

        html.light-theme, body.light-theme {
            --bg-main: #F4F6F9;
            --bg-card: #FFFFFF;
            --bg-card-hover: #F8FAFC;
            --bg-input: #FFFFFF;
            --text-primary: #0F172A;
            --text-secondary: #64748B;
            --accent: #00A38F;
            --accent-dark: #008775;
            --border-subtle: rgba(15, 23, 42, 0.06);
            --border-card: rgba(15, 23, 42, 0.09);
            --glass-nav: rgba(255, 255, 255, 0.94);
            --glass-border: rgba(226, 232, 240, 0.9);
            --popup-bg: #FFFFFF;
            --modal-bg: #FFFFFF;
            --shadow-card: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
        }

        body {
            background-color: var(--bg-main);
            color: var(--text-primary);
            font-family: 'Outfit', sans-serif;
            -ms-overflow-style: none;  
            scrollbar-width: none;  
            transition: background-color 0.25s ease, color 0.2s ease;
        }
        body::-webkit-scrollbar { 
            display: none; 
        }
        .main-content {
            padding-bottom: 80px; /* Space for bottom nav */
        }
        .hide-scroll::-webkit-scrollbar {
            display: none;
        }
        .glass-nav {
            background: var(--glass-nav);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border-top: 1px solid var(--glass-border);
            transition: background-color 0.25s ease, border-color 0.25s ease;
        }

        /* Adaptasi elemen yang memakai utilitas Tailwind saat mode Light */
        .light-theme .text-white:not(.keep-white) {
            color: var(--text-primary) !important;
        }
        .light-theme .text-gray-200,
        .light-theme .text-gray-300 {
            color: var(--text-secondary) !important;
        }
        .light-theme .border-white\/5,
        .light-theme .border-white\/10,
        .light-theme .border-white\/20,
        .light-theme .border-white\/30 {
            border-color: var(--border-card) !important;
        }
        .light-theme .bg-white\/5 {
            background-color: rgba(15, 23, 42, 0.04) !important;
        }
        .light-theme .bg-white\/10 {
            background-color: rgba(15, 23, 42, 0.07) !important;
        }
        .light-theme .bg-white\/20 {
            background-color: rgba(15, 23, 42, 0.12) !important;
        }
        .light-theme .hover\:bg-white\/5:hover {
            background-color: rgba(15, 23, 42, 0.04) !important;
        }
        .light-theme .hover\:bg-white\/10:hover {
            background-color: rgba(15, 23, 42, 0.08) !important;
        }

        /* Form Controls saat Light Mode */
        .light-theme input,
        .light-theme textarea {
            background-color: var(--bg-input) !important;
            color: var(--text-primary) !important;
            border-color: var(--border-card) !important;
        }
        .light-theme input::placeholder,
        .light-theme textarea::placeholder {
            color: var(--text-secondary) !important;
            opacity: 0.8;
        }

        /* Drama Cards & Overlays */
        .light-theme .drama-card .aspect-\[3\/4\] {
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.06);
        }
        .light-theme .drama-card .aspect-\[3\/4\] .text-white\/90 {
            color: rgba(255, 255, 255, 0.95) !important;
        }

        /* Pertahankan teks putih pada elemen banner berwarna */
        .light-theme .keep-white {
            color: #FFFFFF !important;
        }
        .light-theme .vip-banner-card {
            background: linear-gradient(135deg, #0e7490 0%, #0891b2 100%) !important;
            box-shadow: 0 10px 25px -5px rgba(14, 116, 144, 0.3) !important;
        }
        .light-theme .vip-banner-card .text-white,
        .light-theme .vip-banner-card .text-gray-200 {
            color: #FFFFFF !important;
        }
        .light-theme .affiliate-balance-card {
            box-shadow: 0 10px 25px -5px rgba(5, 150, 105, 0.25) !important;
        }
        .light-theme .affiliate-balance-card .text-white,
        .light-theme .affiliate-balance-card .text-emerald-200 {
            color: #FFFFFF !important;
        }
        .light-theme .shadow-inner {
            box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.04) !important;
        }
    </style>
</head>
<body class="bg-darkbg text-white antialiased transition-colors duration-200">

    <!-- Container for dynamic content -->
    <div class="main-content min-h-screen">
        <?php include "pages/{$page}.php"; ?>
    </div>

    <!-- Bottom Navigation -->
    <?php include "includes/nav.php"; ?>

    <!-- QRIS Checkout Modal -->
    <?php include "includes/qris_modal.php"; ?>

    <!-- Application Script: Telegram, Avatar & Theme Toggle -->
    <script>
        // Inisialisasi Telegram Web App
        const tg = window.Telegram?.WebApp;
        if (tg) {
            tg.expand(); // Full height
        }
        
        // Fungsi untuk mengambil data user Telegram
        function getTelegramUser() {
            if (tg && tg.initDataUnsafe && tg.initDataUnsafe.user) {
                return tg.initDataUnsafe.user;
            }
            return null;
        }

        // ==========================================
        // FITUR THEME TOGGLE (DARK / LIGHT MODE)
        // ==========================================
        function toggleTheme() {
            const isLight = document.documentElement.classList.contains('light-theme');
            const targetTheme = isLight ? 'dark' : 'light';
            setTheme(targetTheme);
        }

        function setTheme(theme) {
            const isLight = (theme === 'light');
            const html = document.documentElement;
            const body = document.body;

            if (isLight) {
                html.classList.add('light-theme');
                body.classList.add('light-theme');
                html.setAttribute('data-theme', 'light');
            } else {
                html.classList.remove('light-theme');
                body.classList.remove('light-theme');
                html.setAttribute('data-theme', 'dark');
            }

            try {
                localStorage.setItem('dramastream_theme', theme);
            } catch (e) {}

            // Update Ikon Tombol Tema di Header Home
            const sunIcon = document.getElementById('theme-icon-sun');
            const moonIcon = document.getElementById('theme-icon-moon');
            if (sunIcon && moonIcon) {
                if (isLight) {
                    sunIcon.classList.add('hidden');
                    moonIcon.classList.remove('hidden');
                } else {
                    sunIcon.classList.remove('hidden');
                    moonIcon.classList.add('hidden');
                }
            }

            // Update Switch di Halaman Profil jika sedang aktif
            const profileThemeIcon = document.getElementById('profile-theme-icon');
            const profileThemeLabel = document.getElementById('profile-theme-label');
            const profileThemeKnob = document.getElementById('profile-theme-knob');
            if (profileThemeIcon) {
                profileThemeIcon.className = isLight ? 'fa-solid fa-sun text-sm text-amber-500' : 'fa-solid fa-moon text-sm text-cyan-400';
            }
            if (profileThemeLabel) {
                profileThemeLabel.textContent = isLight ? 'Light Mode' : 'Dark Mode';
            }
            if (profileThemeKnob) {
                if (isLight) {
                    profileThemeKnob.classList.add('translate-x-5');
                } else {
                    profileThemeKnob.classList.remove('translate-x-5');
                }
            }

            // Sinkronisasi Warna Header Telegram WebApp
            if (tg) {
                try {
                    const headerHex = isLight ? '#F4F6F9' : '#0A0E17';
                    if (typeof tg.setHeaderColor === 'function') {
                        tg.setHeaderColor(headerHex);
                    }
                    if (typeof tg.setBackgroundColor === 'function') {
                        tg.setBackgroundColor(headerHex);
                    }
                } catch (e) {}
            }
        }

        // ==========================================
        // FITUR AVATAR & PROFIL USER
        // ==========================================
        function applyUserAvatar(photoUrl, name) {
            const initial = (name && name.trim().length > 0) ? name.trim().charAt(0).toUpperCase() : 'U';

            // 1. Avatar di Tombol Header Home
            const homeAvatar = document.getElementById('home-profile-avatar');
            const homeInitial = document.getElementById('home-profile-initial');
            const homeInitialChar = document.getElementById('home-profile-initial-char');
            const homeIcon = document.getElementById('home-profile-icon');

            // 2. Avatar di Mini Popup Home
            const popupAvatar = document.getElementById('popup-profile-avatar');
            const popupInitial = document.getElementById('popup-profile-initial');
            const popupInitialChar = document.getElementById('popup-profile-initial-char');
            const popupIcon = document.getElementById('popup-profile-icon');

            // 3. Avatar di Halaman Profile
            const profileAvatar = document.getElementById('profile-avatar');
            const profileInitial = document.getElementById('profile-initial');
            const profileInitialChar = document.getElementById('profile-initial-char');
            const profileIcon = document.getElementById('profile-default-icon');

            if (photoUrl && photoUrl.trim() !== '') {
                if (homeAvatar) {
                    homeAvatar.src = photoUrl;
                    homeAvatar.classList.remove('hidden');
                    if (homeInitial) homeInitial.classList.add('hidden');
                    if (homeIcon) homeIcon.classList.add('hidden');
                }
                if (popupAvatar) {
                    popupAvatar.src = photoUrl;
                    popupAvatar.classList.remove('hidden');
                    if (popupInitial) popupInitial.classList.add('hidden');
                    if (popupIcon) popupIcon.classList.add('hidden');
                }
                if (profileAvatar) {
                    profileAvatar.src = photoUrl;
                    profileAvatar.classList.remove('hidden');
                    if (profileInitial) profileInitial.classList.add('hidden');
                    if (profileIcon) profileIcon.classList.add('hidden');
                }
            } else if (name && name.trim() !== '') {
                if (homeInitial && homeInitialChar) {
                    homeInitialChar.textContent = initial;
                    homeInitial.classList.remove('hidden');
                    if (homeAvatar) homeAvatar.classList.add('hidden');
                    if (homeIcon) homeIcon.classList.add('hidden');
                }
                if (popupInitial && popupInitialChar) {
                    popupInitialChar.textContent = initial;
                    popupInitial.classList.remove('hidden');
                    if (popupAvatar) popupAvatar.classList.add('hidden');
                    if (popupIcon) popupIcon.classList.add('hidden');
                }
                if (profileInitial && profileInitialChar) {
                    profileInitialChar.textContent = initial;
                    profileInitial.classList.remove('hidden');
                    if (profileAvatar) profileAvatar.classList.add('hidden');
                    if (profileIcon) profileIcon.classList.add('hidden');
                }
            }
        }

        function handleHomeAvatarError() {
            const homeAvatar = document.getElementById('home-profile-avatar');
            const homeInitial = document.getElementById('home-profile-initial');
            const homeIcon = document.getElementById('home-profile-icon');
            if (homeAvatar) homeAvatar.classList.add('hidden');
            if (homeInitial) {
                homeInitial.classList.remove('hidden');
            } else if (homeIcon) {
                homeIcon.classList.remove('hidden');
            }
        }

        // Toggle mini popup profil
        function toggleProfilePopup() {
            const popup = document.getElementById('profile-popup');
            if (popup) popup.classList.toggle('hidden');
        }

        // Tutup popup jika klik di luar
        document.addEventListener('click', function(e) {
            const wrapper = document.getElementById('profile-btn-wrapper');
            if (wrapper && !wrapper.contains(e.target)) {
                const popup = document.getElementById('profile-popup');
                if (popup) popup.classList.add('hidden');
            }
        });

        // ==========================================
        // INIT ON DOM READY
        // ==========================================
        document.addEventListener('DOMContentLoaded', () => {
            // 1. Terapkan state Tema ke icon
            const currentTheme = localStorage.getItem('dramastream_theme') || 'dark';
            setTheme(currentTheme);

            // 2. Hydrate cache avatar dan nama dari localStorage untuk instant display
            const cachedPhoto = localStorage.getItem('tg_user_photo');
            const cachedName = localStorage.getItem('tg_user_name');
            if (cachedPhoto || cachedName) {
                applyUserAvatar(cachedPhoto, cachedName);
            }

            // 3. Ambil data User dari Telegram SDK
            const user = getTelegramUser();
            const usernameElem = document.getElementById('tg-username');
            const handleElem = document.getElementById('tg-handle');
            const popupUsername = document.getElementById('popup-username');
            const popupHandle = document.getElementById('popup-handle');

            if (user) {
                const displayName = user.first_name || user.username || 'Pengguna';
                if (usernameElem) usernameElem.textContent = displayName;
                if (handleElem && user.username) handleElem.textContent = '@' + user.username;
                if (popupUsername) popupUsername.textContent = displayName;
                if (popupHandle && user.username) popupHandle.textContent = '@' + user.username;

                // Update Avatar
                applyUserAvatar(user.photo_url || '', displayName);

                // Cache ke localStorage
                try {
                    if (user.photo_url) localStorage.setItem('tg_user_photo', user.photo_url);
                    localStorage.setItem('tg_user_name', displayName);
                    if (user.username) localStorage.setItem('tg_user_handle', user.username);
                } catch (e) {}

                // Sync user data to DB
                fetch('api/user_sync.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(user)
                })
                .then(response => response.json())
                .catch(error => console.error('Error syncing user:', error));
            } else {
                if (popupUsername && !cachedName) popupUsername.textContent = 'Pengguna Drama';
            }
        });

        // Global function untuk memutar episode dan mencatat history
        function playEpisode(episodeId, playLink) {
            fetch('api/add_history.php?episode_id=' + episodeId).catch(e => console.log(e));
            
            if (window.Telegram && window.Telegram.WebApp) {
                window.Telegram.WebApp.openTelegramLink(playLink);
                window.Telegram.WebApp.close();
            } else {
                window.location.href = playLink;
            }
        }

    </script>
</body>
</html>
