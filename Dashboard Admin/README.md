# 🎬 Dashboard Admin - Telegram Drama Bot & Mini App

Selamat datang di **Dashboard Admin DramaStream**. Dashboard ini dirancang khusus untuk memantau data pelanggan masuk dari bot Telegram, melacak pendapatan langganan VIP (QRIS), mengelola katalog drama/episode, memproses komisi affiliate, serta meninjau request film penonton.

---

## 📁 Struktur Folder & File yang Rapi

Dashboard ini dibuat di dalam folder baru `Dashboard Admin/` dengan penamaan file yang intuitif dan modular:

```text
Dashboard Admin/
├── config/
│   └── database.php         # Konfigurasi koneksi PDO MySQL (drama_bot) & auto-detect demo mode
├── includes/
│   ├── auth.php             # Session guard & autentikasi hak akses administrator
│   ├── functions.php        # Helper format Rupiah, tanggal Indo, status badge, dan query provider
│   ├── header.php           # Template header, topbar navbar, indikator database, dan CSS framework
│   ├── sidebar.php          # Sidebar navigasi modern dengan highlight menu aktif
│   └── footer.php           # Template footer, modal dialog, dan script JS
├── pages/
│   ├── overview.php         # Halaman Beranda: Grafik pendapatan bulanan, konversi VIP & metrik utama
│   ├── users.php            # Halaman Pelanggan Masuk: Data user Telegram, status VIP, koin & filter
│   ├── vip.php              # Halaman VIP & Keuangan: Pantau pemasukan harian/bulanan & transaksi QRIS
│   ├── dramas.php           # Halaman Katalog Drama: Kelola series, poster, dan episode Telegram
│   ├── affiliate.php        # Halaman Affiliate: Verifikasi permohonan pencairan saldo koin
│   ├── requests.php         # Halaman Request Film: Mendata film yang diminta pengguna (VIP prioritas)
│   └── settings.php         # Halaman Status Database: Pemeriksaan tabel dan panduan koneksi
├── assets/
│   ├── css/
│   │   └── custom.css       # Style modern dark theme, glassmorphism, dan custom scrollbar
│   └── js/
│       ├── charts.js        # Konfigurasi Chart.js untuk grafik pendapatan & user
│       └── main.js          # Skrip interaktif (toggle sidebar, pencarian tabel, toast, modal)
├── index.php                # Router utama dashboard (?page=overview|users|vip|...)
├── login.php                # Halaman login modern dengan opsi Instant Demo Login
├── logout.php               # Handler logout sesi
└── README.md                # Dokumentasi & panduan integrasi
```

---

## 🚀 Cara Menjalankan Dashboard

1. **Jalankan Web Server Lokal (Apache/XAMPP/PHP Built-in)**:
   - Jika menggunakan **XAMPP**, pastikan Apache dan MySQL dalam status **Running**.
   - Atau jalankan built-in server PHP dari terminal:
     ```bash
     cd "d:\KULIAH\Semester 6\magang\Bot_Telegram_Streaming_drama-main"
     php -S localhost:8000
     ```

2. **Akses Dashboard di Browser**:
   - Jika menggunakan PHP Built-in Server:  
     `http://localhost:8000/Dashboard%20Admin/`
   - Jika menggunakan XAMPP (htdocs):  
     `http://localhost/Bot_Telegram_Streaming_drama-main/Dashboard%20Admin/`

3. **Kredensial Login Admin**:
   - **Username**: `admin`
   - **Password**: `admin123`
   - *(Atau klik tombol **"Masuk Instan (Demo Mode)"** untuk masuk langsung)*

---

## ⚡ Fitur Utama yang Telah Disediakan

1. **Pemantauan Pendapatan & User VIP**:
   - Pantau total omset penjualan langganan VIP (Harian, Mingguan, Bulanan, dan All-Time).
   - Grafik tren pendapatan bulanan interaktif menggunakan Chart.js.
   - Status transaksi QRIS realtime (`Paid`, `Pending`, `Expired`, `Failed`).

2. **Pendataan Pelanggan Masuk (`users.php`)**:
   - Mendata pengguna Telegram yang masuk ke Bot & Mini App.
   - Menampilkan Telegram ID, Nama, Username, Status VIP, Sisa Hari VIP, dan Saldo Koin.
   - Filter cepat: Semua, VIP Aktif, Pelanggan Free, Affiliate, dan Banned.
   - Fitur pencarian instan (Live Search) berdasarkan ID atau Nama.

3. **Dukungan Mode Demo Interaktif (Graceful Offline Handling)**:
   - Jika MySQL belum dinyalakan atau tabel belum terisi, dashboard **tetap tampil rapi dan estetik** dengan sampel visual data tanpa error crash.
   - Begitu MySQL dinyalakan dan database `drama_bot` terhubung, data riil dari database akan otomatis diambil!

4. **Katalog Drama & Episode (`dramas.php`)**:
   - Kelola judul drama, batas episode gratis, sinopsis, dan status ongoing/completed.
   - Sinkronisasi episode dengan channel Telegram (Channel ID, Message ID, File ID).

5. **Program Affiliate & Penarikan Saldo (`affiliate.php`)**:
   - Pantau saldo komisi koin pengguna.
   - Tombol verifikasi setujui / tolak permintaan pencairan dana ke rekening / e-wallet.

6. **Permintaan Film (`requests.php`)**:
   - Menampung request film dari penonton dengan penanda prioritas otomatis untuk pelanggan VIP.

---

## 🔌 Cara Menghubungkan ke Database Mini App

Konfigurasi database berada di file:
`Dashboard Admin/config/database.php`

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'drama_bot');
```

- Skema tabel database yang digunakan sesuai dengan skema terbaru di `database/schema_v5_final.sql`.
- Status koneksi dan ketersediaan tabel dapat dicek langsung kapan saja melalui menu **Pengaturan Database** (`?page=settings`).
