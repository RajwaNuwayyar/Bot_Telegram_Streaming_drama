<?php
session_start();
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
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        darkbg: '#0A0E17',
                        cardbg: '#141C2B',
                        accent: '#00D0B6',
                        accentdark: '#00A38F',
                        textmuted: '#8A99AF'
                    },
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #0A0E17;
            color: white;
            font-family: 'Outfit', sans-serif;
            /* Hide scrollbar for neatness */
            -ms-overflow-style: none;  
            scrollbar-width: none;  
        }
        body::-webkit-scrollbar { 
            display: none; 
        }
        .main-content {
            padding-bottom: 80px; /* Space for bottom nav */
        }
        /* Custom scrollbar for horizontal scrolling */
        .hide-scroll::-webkit-scrollbar {
            display: none;
        }
        .glass-nav {
            background: rgba(20, 28, 43, 0.85);
            backdrop-filter: blur(12px);
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
</head>
<body class="bg-darkbg text-white antialiased">

    <!-- Container for dynamic content -->
    <div class="main-content min-h-screen">
        <?php include "pages/{$page}.php"; ?>
    </div>

    <!-- Bottom Navigation -->
    <?php include "includes/nav.php"; ?>

    <!-- Telegram Init Script -->
    <script>
        // Inisialisasi Telegram Web App
        const tg = window.Telegram.WebApp;
        tg.expand(); // Expand to full height
        
        // Setup warna tema menyesuaikan Telegram (opsional)
        // const themeParams = tg.themeParams;
        // document.body.style.backgroundColor = themeParams.bg_color || '#0A0E17';
        
        // Fungsi untuk mengambil data user Telegram
        function getTelegramUser() {
            if (tg.initDataUnsafe && tg.initDataUnsafe.user) {
                return tg.initDataUnsafe.user;
            }
            // Mock data untuk testing di browser biasa
            return {
                id: 12345678,
                first_name: "DramaFan",
                username: "dramafan99"
            };
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

        // Tampilkan User Name di Profile & Popup jika elemennya ada
        document.addEventListener('DOMContentLoaded', () => {
            const user = getTelegramUser();
            const usernameElem = document.getElementById('tg-username');
            const handleElem = document.getElementById('tg-handle');
            const popupUsername = document.getElementById('popup-username');
            const popupHandle = document.getElementById('popup-handle');

            if (usernameElem && user) {
                usernameElem.textContent = user.first_name || user.username;
            }
            if (handleElem && user && user.username) {
                handleElem.textContent = '@' + user.username;
            }
            if (popupUsername && user) {
                popupUsername.textContent = user.first_name || user.username;
            }
            if (popupHandle && user && user.username) {
                popupHandle.textContent = '@' + user.username;
            }

            // Sync user data to DB
            if (user) {
                fetch('api/user_sync.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(user)
                })
                .then(response => response.json())
                .then(data => console.log('User synced:', data))
                .catch(error => console.error('Error syncing user:', error));
            }
        });

        // Fungsi untuk mengarahkan pengguna ke halaman pembayaran (request QRIS)
        function processPayment(planId, amount) {
            const user = getTelegramUser();
            // Buat URL ke script pembayaran di folder payment (amount tidak dikirim via URL demi keamanan)
            const url = `../payment/request_qris.php?tg_user_id=${user.id}&username=${user.username || ''}&first_name=${user.first_name || 'User'}&plan_id=${planId}`;
            window.location.href = url;
        }
    </script>
</body>
</html>
