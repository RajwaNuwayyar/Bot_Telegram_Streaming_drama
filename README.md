# 🎬 DramaBot - Fullstack Telegram Drama Streaming & Mini App Platform

<div align="center">

![Go](https://img.shields.io/badge/Golang-1.22+-00ADD8?style=for-the-badge&logo=go&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![SQLite](https://img.shields.io/badge/SQLite-003B57?style=for-the-badge&logo=sqlite&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![Telegram](https://img.shields.io/badge/Telegram_Bot_API-v5-2CA5E0?style=for-the-badge&logo=telegram&logoColor=white)
![QRIS](https://img.shields.io/badge/QRIS_Payment-Midtrans-002B49?style=for-the-badge&logo=google-pay&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker&logoColor=white)

**Platform streaming drama pendek & mini-drama viral terlengkap dengan integrasi Telegram Bot, Telegram Mini App (Webview), Database SQLite/MySQL, Gateway Pembayaran QRIS Otomatis, dan Dashboard Admin Web.**

[Fitur Utama](#-fitur-utama) • [Arsitektur Sistem](#️-arsitektur-sistem) • [Struktur Proyek](#-struktur-direktori) • [Panduan Instalasi](#️-panduan-instalasi--konfigurasi) • [Dokumentasi API](#-dokumentasi-api--webhook) • [Perintah Bot](#️-daftar-perintah-bot--admin)

</div>

---

## 🌟 Gambaran Umum (Overview)

**DramaBot** adalah ekosistem hiburan streaming drama pendek modern di dalam Telegram. Sistem ini menggabungkan kecepatan pengiriman video dari **Channel Telegram Privat (CDN Storage)** dengan antarmuka katalog visual interaktif dari **Telegram Mini App (TMA)** dan sebuah **Dashboard Admin** berbasis web penuh.

### 💡 Keunggulan Utama:
- 🚀 **High Performance Backend (Go)**: Pemrosesan update bot real-time, event listener channel, REST API berkecepatan tinggi, dan dukungan SQLite bawaan tanpa setup database eksternal.
- 📱 **Sleek Mini App (PHP + TailwindCSS)**: Tampilan visual modern bertema dark-mode glassmorphism, responsif di semua perangkat mobile, mendukung multi-bahasa (ID/EN).
- 🖥️ **Dashboard Admin Web Lengkap**: Pantau pendapatan, kelola katalog drama, verifikasi komisi affiliate, dan proses request film langsung dari browser.
- 🔒 **Sistem VIP & Hak Akses Ketat**: Penguncian otomatis episode berbayar dengan verifikasi durasi aktif berbasis waktu (`vip_until`).
- 💳 **Pembayaran QRIS Otomatis**: Integrasi Midtrans & Generator QRIS instan dengan verifikasi status real-time melalui webhook dan polling.
- 📊 **Dual Database Support**: Backend Go berjalan dengan SQLite (default/development) atau MySQL (production). Skema MySQL dilengkapi Stored Procedures (`sp_can_watch`), Functions (`fn_is_vip`), Views, dan Scheduled Events untuk auto-expire.

---

## 🏗️ Arsitektur Sistem

```mermaid
graph TD
    User([Pengguna / Penonton])
    TBot[🤖 Telegram Bot Runtime - Go]
    TMA[📱 Telegram Mini App - PHP / Tailwind]
    AdminDash[🖥️ Dashboard Admin - PHP]
    Chan[📦 Channel Telegram Privat / CDN Storage]
    DB[(🗄️ Database SQLite / MySQL)]
    PG[💳 Payment Gateway Midtrans / QRIS]

    %% Alur Bot & Mini App
    User -->|Kirim /start atau Menu| TBot
    User -->|Buka Katalog| TMA
    TMA -->|Deep Link /start watch_ID| TBot
    TBot -->|Kirim Video CopyMessage| User

    %% Alur Indexing Channel
    Admin([Admin / Uploader]) -->|Upload Video / #poster| Chan
    Admin -->|Login Dashboard| AdminDash
    Chan -->|ChannelPost Event Listener| TBot
    TBot -->|Simpan Metadata Drama & Episode| DB

    %% Alur Mini App & Data
    TMA -->|Sinkronisasi Profil, Favorit & Watch History| DB
    TMA -->|Pencarian & Filter Genre| DB

    %% Alur Dashboard Admin
    AdminDash -->|Kelola Drama, User, VIP, Affiliate| DB

    %% Alur Pembayaran
    User -->|Beli Paket VIP| TMA
    User -->|Menu /vip di Chat| TBot
    TMA -->|Request QRIS| PG
    PG -->|Webhook Callback PAID| TBot
    TBot -->|Aktivasi Durasi VIP & Notifikasi User| DB
```

---

## 🚀 Fitur Utama

### 1. 🤖 Telegram Bot Core
- **Channel Storage Listener**: Otomatis mendeteksi video baru dari channel privat, mengekstrak judul drama, nomor part/episode, dan status VIP.
- **Admin Channel Commands**: Manajemen episode langsung dari channel tanpa buka database:
  - `#hapus_episode <message_id>`: Menghapus data episode dari database.
  - `#hapus_drama <Judul Drama>`: Menghapus seluruh judul drama dan relasinya secara permanen.
  - `#edit_drama <Judul Lama> | <Judul Baru>`: Memperbarui judul drama dan seluruh episodenya.
  - `#edit_episode <message_id> | <Judul Baru>`: Memperbarui judul episode spesifik.
  - `#set_poster <Judul> | <file_id>`: Memperbarui poster/thumbnail drama.
  - `#edit_kategori <Nama Lama/ID> | <Nama Baru>`: Mengubah nama master kategori dan slug URL-nya.
  - `#hapus_kategori <Nama/ID>`: Menghapus kategori dari database dan relasi drama.
  - `#tambah_kategori <Nama>`: Menambahkan master kategori baru.
  - `#list_kategori`: Menampilkan seluruh daftar kategori aktif dan jumlah dramanya.
  - `#set_kategori <Judul> | <Kategori1>, <Kategori2>`: Menghubungkan kategori ke judul drama.
  - Unggah video dengan tag `#<Kategori>` (contoh: `#Romantis`) untuk auto-assign kategori ke drama.
  - Unggah foto dengan tag `#poster` untuk auto-update thumbnail drama.
  - Edit caption postingan di channel untuk auto-update judul drama, kategori & status VIP.
- **Pengiriman Video Berkecepatan Tinggi**: Menggunakan metode `CopyMessage` dari channel privat tanpa watermark/forward header dan fallback `FileID`.
- **Navigasi Episode Terpadu**: Tombol interaktif *Episode Sebelumnya*, *Episode Selanjutnya*, dan tombol pintas kembali ke Mini App.

### 2. 📱 Telegram Mini App (TMA Frontend)
- **Katalog & Featured Banner**: Carousel drama unggulan dengan rating bintang, jumlah tayang, dan tag genre.
- **Pencarian Real-Time & Filter Genre**: Pencarian instan berdasarkan judul dan kategori dengan debounce otomatis.
- **Riwayat Tontonan (Watch History)**: Melacak progress tontonan (*Sedang Ditonton* vs *Selesai*) dengan fitur hapus riwayat.
- **Favorit Drama**: Pengguna dapat menyimpan dan mengelola daftar drama favorit pribadi.
- **Sistem Permintaan Drama (Request Drama)**: Formulir pengajuan judul drama baru dengan kuota harian dan notifikasi instan ke admin.
- **Program Afiliasi (Referral & Komisi)**: Sistem tingkatan komisi berjenjang (*Level 1 - Level 4*) dengan saldo koin, tautan referral unik, dan fitur penarikan saldo.
- **Multi-Bahasa (i18n)**: Pilihan bahasa instan antara Bahasa Indonesia (ID) dan English (EN).

### 3. 🖥️ Dashboard Admin Web
Dashboard web berbasis PHP dengan tampilan dark-mode modern untuk pengelolaan sistem secara penuh:
- **Beranda (Overview)**: Grafik pendapatan bulanan interaktif (Chart.js), konversi VIP, dan metrik utama real-time.
- **Manajemen Pengguna**: Data user Telegram (ID, Nama, Status VIP, Saldo Koin) dengan filter cepat & live search.
- **VIP & Keuangan**: Pantau pemasukan harian/bulanan dan status transaksi QRIS (`Paid`, `Pending`, `Expired`).
- **Katalog Drama**: Kelola judul drama, poster, episode, kategori, dan status ongoing/completed.
- **Manajemen Affiliate**: Verifikasi dan proses permohonan pencairan saldo koin pengguna.
- **Permintaan Film**: Mendata request film penonton dengan prioritas otomatis untuk pelanggan VIP.
- **Pengaturan Database**: Pemeriksaan status koneksi dan ketersediaan tabel secara langsung.
- **Mode Demo Interaktif**: Dashboard tetap tampil rapi dan estetik dengan data sampel jika database belum terhubung.

### 4. 👑 Manajemen VIP & Pembayaran QRIS
- **7 Durasi Paket Langganan**:
  1. ⚡ **VIP 1 Hari** - Rp 3.000 / Rp 5.000
  2. ✨ **VIP 3 Hari** - Rp 6.000 / Rp 12.000
  3. 🎉 **VIP 7 Hari** - Rp 10.000 / Rp 25.000
  4. ⭐ **VIP 15 Hari** - Rp 20.000 / Rp 45.000
  5. 🔥 **VIP 30 Hari (Best Value)** - Rp 35.000 / Rp 75.000
  6. 💎 **VIP 90 Hari** - Rp 90.000 / Rp 180.000
  7. 👑 **VIP 365 Hari (1 Tahun)** - Rp 300.000 / Rp 500.000
- **Modal QRIS Interaktif**: Countdown timer 15 menit, live polling status pembayaran otomatis, dan panduan transfer bank/e-wallet.
- **Simulasi Pengujian Pembayaran**: Perintah bot `/simulate_pay <trx_code>` untuk kemudahan testing tanpa saldo riil.

---

## 📁 Struktur Direktori

```text
Bot_Telegram_Streaming_drama/
├── cmd/
│   └── bot/
│       └── main.go                     # Entry point bot Telegram & server HTTP
├── internal/
│   ├── bot/
│   │   ├── bot.go                      # Core lifecycle Telegram Bot & Update loop
│   │   ├── handlers_callback.go        # Handler tombol inline keyboard & QRIS
│   │   ├── handlers_channel.go         # Channel post listener, parser & admin command
│   │   ├── handlers_channel_test.go    # Unit test caption & poster parser
│   │   ├── handlers_command.go         # Handler slash commands (/start, /vip, dll)
│   │   ├── handlers_video.go           # Logika gatekeeper VIP & pengiriman video
│   │   ├── keyboards.go                # Konstruktor inline markup & reply keyboard
│   │   └── messages.go                 # Template pesan, teks panduan, & notifikasi
│   ├── config/
│   │   └── config.go                   # Pemuat variabel lingkungan .env
│   ├── database/
│   │   ├── models.go                   # Model struct data (User, Drama, Episode, Trx)
│   │   ├── mysql.go                    # Implementasi repository database MySQL
│   │   ├── repository.go               # Interface kontrak abstraksi database
│   │   ├── sqlite.go                   # Implementasi SQLite (default/development)
│   │   └── sqlite_test.go              # Unit test repositori SQLite
│   ├── payment/
│   │   └── payment.go                  # Layanan QRIS generator & Payment Coordinator
│   └── server/
│       └── server.go                   # HTTP API Server (Webhook & Mini App integration)
├── Homepage/                           # Telegram Mini App Frontend (Webview)
│   ├── index.php                       # Single Entry Point Mini App & routing
│   ├── api/
│   │   ├── add_history.php             # API pencatatan riwayat tontonan
│   │   ├── clear_history.php           # API pembersihan riwayat tontonan
│   │   ├── poster.php                  # Proxy loader gambar poster dari Telegram
│   │   ├── search.php                  # API pencarian drama & filter kategori
│   │   ├── submit_request.php          # API pengajuan request drama baru
│   │   ├── toggle_favorite.php         # API tambah / hapus drama dari favorit
│   │   ├── user_sync.php               # API sinkronisasi data Telegram User ke DB
│   │   └── withdraw.php                # API pengajuan penarikan saldo koin affiliate
│   ├── includes/
│   │   ├── db_queries.php              # Kumpulan fungsi query database PHP
│   │   ├── nav.php                     # Navigasi bawah (Bottom Navigation Bar)
│   │   └── qris_modal.php              # Komponen modal pembayaran QRIS & timer
│   ├── js/
│   │   └── i18n.js                     # Sistem translasi multi-bahasa (ID / EN)
│   └── pages/
│       ├── home.php                    # Halaman utama katalog drama & banner
│       ├── history.php                 # Halaman riwayat tontonan
│       ├── favorites.php               # Halaman daftar drama favorit pengguna
│       ├── vip.php                     # Halaman paket langganan VIP
│       ├── profile.php                 # Halaman profil akun & preferensi bahasa
│       ├── affiliate.php               # Halaman program komisi & referral
│       └── request.php                 # Halaman formulir request drama
├── admin/                              # Dashboard Admin Web (PHP)
│   ├── index.php                       # Router utama dashboard (?page=...)
│   ├── login.php                       # Halaman login modern + Instant Demo Login
│   ├── logout.php                      # Handler logout sesi
│   ├── poster_proxy.php                # Proxy poster untuk dashboard admin
│   ├── config/
│   │   └── database.php                # Konfigurasi PDO MySQL & auto-detect demo mode
│   ├── includes/
│   │   ├── auth.php                    # Session guard & autentikasi hak akses
│   │   ├── functions.php               # Helper format Rupiah, tanggal, badge & query
│   │   ├── header.php                  # Template header, topbar, & CSS framework
│   │   ├── sidebar.php                 # Sidebar navigasi dengan highlight menu aktif
│   │   └── footer.php                  # Template footer, modal dialog, & script JS
│   ├── pages/
│   │   ├── overview.php                # Beranda: Grafik pendapatan & metrik utama
│   │   ├── users.php                   # Manajemen pengguna Telegram
│   │   ├── vip.php                     # VIP & keuangan (transaksi QRIS)
│   │   ├── dramas.php                  # Katalog drama & episode
│   │   ├── affiliate.php               # Verifikasi pencairan saldo affiliate
│   │   ├── requests.php                # Request film dari penonton
│   │   └── settings.php                # Status & pengaturan database
│   └── assets/
│       ├── css/
│       │   └── custom.css              # Style dark theme & glassmorphism
│       └── js/
│           ├── charts.js               # Konfigurasi Chart.js grafik pendapatan
│           └── main.js                 # Skrip interaktif sidebar, search, toast, modal
├── payment/                            # Gateway Pembayaran Midtrans (PHP)
│   ├── check_status.php                # Endpoint cek status transaksi QRIS
│   ├── midtrans_config.php             # Konfigurasi Midtrans Server Key
│   ├── request_qris.php                # Endpoint inisialisasi QRIS Midtrans
│   └── webhook.php                     # Handler notifikasi webhook Midtrans
├── database/                           # Skema & Prosedur SQL
│   ├── schema_v5_final.sql             # Skema DDL tabel MySQL v5 Final
│   ├── procedures.sql                  # Stored Procedures & Functions
│   ├── scheduled_events.sql            # Event Scheduler auto-expire VIP
│   ├── seed_data.sql                   # Data awal (Kategori, Paket VIP, Drama)
│   ├── backup_daily.bat                # Script automasi backup harian MySQL
│   └── koneksi.php                     # Koneksi database PDO untuk PHP
├── .env.example                        # Template konfigurasi environment
├── .env                                # Konfigurasi aktif (rahasia, tidak di-commit)
├── Dockerfile                          # Konfigurasi container Docker (Go backend)
├── Dockerfile.php                      # Konfigurasi container Docker (PHP frontend)
├── docker-compose.yml                  # Orkestrasi container Docker
├── go.mod                              # Modul dependencies Golang
└── README.md                           # Dokumentasi resmi proyek
```

---

## 🛠️ Panduan Instalasi & Konfigurasi

### 📋 Prasyarat Sistem
- **Golang**: v1.22 atau lebih baru
- **PHP**: v8.1 atau lebih baru (dengan ekstensi `pdo_mysql`, `pdo_sqlite`, `curl`)
- **Database**: SQLite (bawaan, tanpa setup) atau MySQL v8.0 / MariaDB 10.5+ (opsional untuk production)
- **Web Server**: Apache / Nginx / PHP Built-in Server (atau tunneling ngrok)
- **Akun Telegram** & Token Bot dari [@BotFather](https://t.me/BotFather)

---

### 1️⃣ Konfigurasi File Lingkungan (`.env`)
Salin file template `.env.example` menjadi `.env`, lalu lengkapi isinya:
```bash
cp .env.example .env
```

Sesuaikan nilai variabel:
```env
# Token bot resmi dari Telegram @BotFather
BOT_TOKEN=1234567890:ABCdefGHIjklMNOpqrSTUvwxYZ

# Username bot Telegram tanpa tanda @
BOT_USERNAME=NamaBotAnda

# ID Channel Privat penyimpanan video (diawali -100)
PRIVATE_CHANNEL_ID=-1001234567890

# URL Telegram Mini App (gunakan HTTPS / URL Ngrok untuk pengujian)
WEB_APP_URL=https://your-domain.com/Homepage/index.php

# Grup / Channel Resmi Komunitas
OFFICIAL_GROUP_URL=https://t.me/YourGroup

# ID Telegram Admin untuk menerima notifikasi upload & request
ADMIN_USER_ID=123456789

# Port HTTP Server Go
SERVER_PORT=8080

# Lokasi File Database SQLite (otomatis dibuat & dimigrasi)
DATABASE_PATH=drama_bot

# URL Webhook Pembayaran QRIS
PAYMENT_WEBHOOK_URL=http://localhost:8080/api/payment/webhook
```

> **💡 Catatan Database:** Backend Go secara default menggunakan **SQLite** (file `drama_bot.db` dibuat otomatis). Untuk production dengan MySQL, tambahkan variabel `DATABASE_DSN` dan sesuaikan implementasi di `internal/database/`.

---

### 2️⃣ Konfigurasi Database MySQL (Production)
Buat database dan impor skema secara berurutan:
```sql
CREATE DATABASE bot_drama CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```
```bash
mysql -u root -p bot_drama < database/schema_v5_final.sql
mysql -u root -p bot_drama < database/seed_data.sql
mysql -u root -p bot_drama < database/procedures.sql
mysql -u root -p bot_drama < database/scheduled_events.sql
```

Konfigurasi koneksi database PHP ada di:
- **Mini App**: `database/koneksi.php`
- **Dashboard Admin**: `admin/config/database.php`

---

### 3️⃣ Menjalankan Bot & Backend (Golang)

1. Unduh seluruh dependensi Go:
   ```bash
   go mod download
   ```
2. Jalankan unit test untuk memastikan integritas:
   ```bash
   go test -v ./...
   ```
3. Jalankan bot:
   ```bash
   go run ./cmd/bot/main.go
   ```
   Atau gunakan binary yang sudah dikompilasi:
   ```bash
   ./bot.exe
   ```

---

### 4️⃣ Menjalankan Mini App Frontend & Dashboard Admin (PHP)

Jalankan built-in web server PHP dari direktori proyek:
```bash
php -S localhost:8000
```

Akses masing-masing komponen:
- **Mini App (Telegram Webview)**: `http://localhost:8000/Homepage/index.php`
- **Dashboard Admin**: `http://localhost:8000/admin/`
  - **Username**: `admin` | **Password**: `admin123`
  - Atau klik **"Masuk Instan (Demo Mode)"** untuk melihat dashboard tanpa database.

Untuk menguji Mini App di Telegram pada perangkat ponsel, gunakan tunneling HTTPS:
```bash
ngrok http 8000
```
Lalu perbarui `WEB_APP_URL` di file `.env` dengan URL HTTPS dari ngrok.

---

### 5️⃣ Menjalankan via Docker (Opsional)

Aplikasi dapat dijalankan secara terisolasi menggunakan Docker Compose:
```bash
docker-compose up --build -d
```

---

## 📡 Dokumentasi API & Webhook

### 1. HTTP Server Bot (Golang - Port 8080)

| Method | Endpoint | Deskripsi | Payload / Parameter |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/payment/webhook` | Webhook konfirmasi pembayaran QRIS | `{"transaction_code": "TRX123", "status": "PAID"}` |
| `POST` | `/api/send-episode` | Kirim video episode langsung ke chat user | `{"telegram_id": 12345, "episode_id": 1}` |
| `GET`  | `/api/plans` | Ambil daftar paket VIP aktif format JSON | *None* |
| `GET`  | `/api/health` | Healthcheck status server | *None* |

### 2. Mini App API (PHP)

| Method | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| `GET`  | `Homepage/api/search.php?q=keyword&cat=genre` | Pencarian & filter katalog drama |
| `GET`  | `Homepage/api/poster.php?fid=TELEGRAM_FILE_ID` | Proxy image loader untuk thumbnail Telegram |
| `POST` | `Homepage/api/user_sync.php` | Sinkronisasi profil user Telegram ke DB |
| `POST` | `Homepage/api/add_history.php` | Catat progress tontonan pengguna |
| `POST` | `Homepage/api/clear_history.php` | Hapus riwayat tontonan pengguna |
| `POST` | `Homepage/api/toggle_favorite.php` | Tambah / hapus drama dari daftar favorit |
| `POST` | `Homepage/api/withdraw.php` | Pengajuan penarikan saldo koin affiliate |
| `POST` | `Homepage/api/submit_request.php` | Pengajuan request drama baru ke admin |
| `GET`  | `payment/request_qris.php` | Request pembuatan invoice QRIS Midtrans |
| `GET`  | `payment/check_status.php?order_id=TRX` | Polling cek status pembayaran QRIS |
| `POST` | `payment/webhook.php` | Webhook listener callback Midtrans |

---

## ⌨️ Daftar Perintah Bot & Admin

### 👤 Perintah Pengguna (User Commands)
- `/start` : Membuka menu utama, cek status akun, dan tautan ke Mini App.
- `/start watch_<episode_id>` : Deep link instan untuk memutar episode tertentu.
- `/vip` : Menampilkan katalog 7 pilihan paket langganan VIP.
- `/status` : Memeriksa sisa masa aktif langganan VIP akun.
- `/tutorial` : Panduan langkah demi langkah cara menonton dan berlangganan.
- `/bantuan` : Pusat informasi bantuan dan FAQ.

### 🛡️ Perintah Admin & Channel (Admin Controls)
- `/edit_drama <Judul Lama> | <Judul Baru>` : Mengubah/memperbarui judul drama beserta semua episodenya via DM admin.
- `/set_poster <Judul Drama> | <File_ID>` : Menyetel poster drama via DM admin.
- `/simulate_pay <TRX_CODE>` : Simulasi pelunasan transaksi untuk pengujian.
- `#edit_drama <Judul Lama> | <Judul Baru>` : Di channel privat — memperbarui judul serial drama & semua episodenya.
- `#edit_episode <message_id> | <Judul Baru>` : Di channel privat — memperbarui judul episode spesifik.
- `#hapus_episode <message_id>` : Di channel privat — menghapus episode dari database.
- `#hapus_drama <Judul Drama>` : Di channel privat — menghapus seluruh serial drama.
- `#set_poster <Judul> | <file_id>` : Di channel privat — update poster drama.
- `#edit_kategori <Nama Lama/ID> | <Nama Baru>` : Di channel privat — mengubah nama & slug kategori.
- `#hapus_kategori <Nama/ID>` : Di channel privat — menghapus kategori dari database.
- `#tambah_kategori <Nama>` : Di channel privat — membuat kategori baru.
- `#list_kategori` : Di channel privat — melihat semua kategori aktif.
- `#set_kategori <Judul> | <Kategori>` : Di channel privat — assign/update kategori film.
- **Upload Video + Tag `#<Kategori>`** : Otomatis mendeteksi kategori (contoh: `#Romantis`) dan menautkannya ke drama.
- **Edit Caption Video di Channel** : Otomatis mendeteksi pembaruan judul drama, nomor episode, kategori, dan tag `#vip`.
- **Upload Gambar + Caption `#poster`** : Otomatis memperbarui thumbnail serial drama di database dan Mini App.

---

## 📄 Lisensi & Kontribusi

Proyek ini dikembangkan untuk kebutuhan platform streaming drama interaktif Telegram. Silakan lakukan *fork*, buat *feature branch*, dan ajukan *Pull Request* untuk berkontribusi.

<div align="center">
Made with ❤️ by DramaBot Engineering Team
</div>