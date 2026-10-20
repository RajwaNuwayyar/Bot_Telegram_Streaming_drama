<?php
/**
 * Login Page - Dashboard Admin Drama Stream
 * Terotentikasi penuh dengan database MySQL tabel admins menggunakan enkripsi BCrypt.
 */
require_once __DIR__ . '/includes/auth.php';

$error = '';
$usernameInput = '';

// Jika sudah login, langsung alihkan ke index dashboard
if (isAdminLoggedIn()) {
    header("Location: index.php");
    exit;
}

// Proses submit form login
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $usernameInput = trim($_POST['username'] ?? '');
    $passwordInput = trim($_POST['password'] ?? '');

    $result = attemptAdminLogin($usernameInput, $passwordInput);
    if ($result['success']) {
        header("Location: index.php");
        exit;
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin | DramaStream Bot</title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        darkbg: '#0A0E17',
                        accent: '#00D0B6',
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        heading: ['"Outfit"', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="assets/css/custom.css">
</head>
<body class="bg-[#090D16] min-h-screen flex items-center justify-center p-4 relative overflow-hidden select-none">

    <!-- Glowing Background Orbs -->
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-[#00D0B6]/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-blue-600/15 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10">
        
        <!-- Brand Header -->
        <div class="text-center mb-8">
            <div class="inline-flex w-16 h-16 rounded-3xl bg-gradient-to-tr from-[#00D0B6] to-sky-400 items-center justify-center shadow-xl shadow-[#00D0B6]/25 mb-4">
                <i class="fas fa-play text-slate-950 text-2xl ml-1"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-white font-heading tracking-wide">DramaStream Bot</h1>
            <p class="text-xs text-slate-400 mt-1">Admin Management & Monitoring System</p>
        </div>

        <!-- Glass Login Card -->
        <div class="glass-panel p-8 rounded-3xl shadow-2xl border border-white/10 backdrop-blur-xl">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-lg font-bold text-white">Masuk ke Dashboard</h2>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    Sistem Aman
                </span>
            </div>
            <p class="text-xs text-slate-400 mb-6">Masukkan kredensial akun administrator Anda untuk mengakses panel kontrol.</p>

            <?php if (!empty($error)): ?>
            <div class="mb-5 p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs flex items-center gap-2.5 animate-slide-up">
                <i class="fas fa-exclamation-circle text-rose-400 text-sm flex-shrink-0"></i>
                <span><?= htmlspecialchars($error); ?></span>
            </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Username</label>
                    <div class="relative">
                        <i class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                        <input type="text" name="username" value="<?= htmlspecialchars($usernameInput); ?>" required autocomplete="username" placeholder="Masukkan username admin..." class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-900/90 border border-white/10 text-sm text-white focus:outline-none focus:border-[#00D0B6] transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Password</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                        <input type="password" id="passwordField" name="password" required autocomplete="current-password" placeholder="Masukkan password..." class="w-full pl-10 pr-11 py-3 rounded-xl bg-slate-900/90 border border-white/10 text-sm text-white focus:outline-none focus:border-[#00D0B6] transition">
                        <button type="button" onclick="togglePasswordVisibility()" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 text-sm p-1 transition" title="Lihat/Sembunyikan Password">
                            <i id="eyeIcon" class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="pt-3">
                    <button type="submit" class="w-full py-3 px-4 rounded-xl bg-[#00D0B6] hover:bg-[#00D0B6]/90 text-slate-950 font-bold text-sm tracking-wide transition shadow-lg shadow-[#00D0B6]/25 flex items-center justify-center gap-2">
                        <span>Masuk ke Dashboard</span>
                        <i class="fas fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-4 border-t border-white/5 flex items-center justify-center gap-2 text-[11px] text-slate-500">
                <i class="fas fa-shield-halved text-[#00D0B6]"></i>
                <span>Enkripsi Password BCrypt &amp; Proteksi Sesi Aktif</span>
            </div>
        </div>

    </div>

    <script>
    function togglePasswordVisibility() {
        const field = document.getElementById('passwordField');
        const icon = document.getElementById('eyeIcon');
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
    </script>

</body>
</html>
