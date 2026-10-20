<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Ambil info admin
$adminInfo = getAdminInfo();
$isDbConnected = isDbConnected();

// Halaman aktif
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'overview';
?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin | DramaStream Bot</title>
    
    <!-- Favicon & Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        darkbg: '#0A0E17',
                        cardbg: '#111827',
                        cardhover: '#172236',
                        accent: '#00D0B6',
                        accentdark: '#00A38F',
                        gold: '#F59E0B'
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        heading: ['"Outfit"', 'sans-serif']
                    }
                }
            }
        }
    </script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/custom.css">
</head>
<body class="bg-[#090D16] text-slate-100 antialiased min-h-screen flex">

    <!-- Mobile Sidebar Backdrop Overlay -->
    <div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 hidden lg:hidden"></div>

    <!-- Sidebar Include -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 min-h-screen lg:pl-64 transition-all duration-300">
        
        <!-- Topbar Navbar Header -->
        <header class="sticky top-0 z-30 flex items-center justify-between h-20 px-6 sm:px-8 bg-[#0D1322]/80 backdrop-blur-md border-b border-white/5">
            <!-- Left: Mobile Toggle & Page Title -->
            <div class="flex items-center gap-4">
                <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-xl bg-slate-800/80 text-slate-300 hover:text-white hover:bg-slate-700 transition">
                    <i class="fas fa-bars text-lg"></i>
                </button>
                <div>
                    <h1 class="text-xl font-bold font-heading text-white tracking-wide">
                        <?php 
                            switch($currentPage) {
                                case 'users': echo 'Data Pelanggan Masuk'; break;
                                case 'vip': echo 'Pendapatan & Transaksi VIP'; break;
                                case 'dramas': echo 'Katalog Drama & Episode'; break;
                                case 'affiliate': echo 'Program Affiliate & Penarikan'; break;
                                case 'requests': echo 'Request Film Masuk'; break;
                                case 'settings': echo 'Pengaturan Sistem'; break;
                                default: echo 'Dashboard Utama'; break;
                            }
                        ?>
                    </h1>
                    <p class="text-xs text-slate-400 hidden sm:block">Kelola dan pantau seluruh ekosistem Bot Telegram & Mini App Drama</p>
                </div>
            </div>

            <!-- Right: Status Badge, Mini App Preview, User Profile -->
            <div class="flex items-center gap-3 sm:gap-4">
                <!-- Database Connection Indicator -->
                <?php if ($isDbConnected): ?>
                    <div class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20" title="Terhubung ke database MySQL drama_bot">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Database MySQL Terhubung</span>
                    </div>
                <?php else: ?>
                    <div class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20" title="MySQL belum terhubung. Menampilkan data demo interaktif.">
                        <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                        <span>Mode Demo Interaktif</span>
                    </div>
                <?php endif; ?>

                <!-- Mini App Shortcut Link -->
                <a href="../Homepage/index.php" target="_blank" class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-medium bg-slate-800 text-slate-200 hover:text-[#00D0B6] hover:bg-slate-700/80 transition border border-white/5" title="Buka Mini App Pengguna">
                    <i class="fab fa-telegram-plane text-[#00D0B6]"></i>
                    <span>Lihat Mini App</span>
                </a>

                <!-- Notification Bell -->
                <div class="relative">
                    <button class="relative p-2.5 rounded-xl bg-slate-800/60 text-slate-400 hover:text-white hover:bg-slate-700/60 transition border border-white/5">
                        <i class="fas fa-bell text-sm"></i>
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-[#00D0B6]"></span>
                    </button>
                </div>

                <!-- Admin Profile Dropdown Pill -->
                <div class="flex items-center gap-3 pl-3 sm:pl-4 border-l border-white/10">
                    <a href="index.php?page=settings#profile-section" class="flex items-center gap-2.5 group cursor-pointer" title="Kelola Profil & Password Akun">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-[#00D0B6] to-sky-400 text-slate-950 font-bold text-xs flex items-center justify-center ring-2 ring-[#00D0B6]/30 group-hover:scale-105 transition">
                            <?= strtoupper(substr($adminInfo['full_name'] ?? $adminInfo['username'], 0, 1)); ?>
                        </div>
                        <div class="hidden xl:block text-left">
                            <p class="text-xs font-bold text-white group-hover:text-[#00D0B6] transition"><?= htmlspecialchars($adminInfo['full_name'] ?? $adminInfo['username']); ?></p>
                            <p class="text-[10px] text-emerald-400 font-medium">Online • <?= htmlspecialchars($adminInfo['role']); ?></p>
                        </div>
                    </a>
                    <a href="logout.php" class="p-2 rounded-xl text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 transition" title="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Body Content Canvas -->
        <main class="flex-1 p-6 sm:p-8 max-w-7xl w-full mx-auto">
