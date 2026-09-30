# 🎬 DramaBot - Telegram Drama Streaming Bot (Golang)

DramaBot adalah backend dan bot Telegram untuk layanan streaming drama pendek / mini-drama viral (mirip dengan [@dailydramabot](https://web.telegram.org/k/#@dailydramabot)). Dibangun menggunakan **Golang** dengan arsitektur modular yang cepat, efisien, dan siap berkolaborasi dalam tim pengembangan (Database Engineer, Payment Coordinator, dan Mini App Developer).

---

## 📌 Fitur Utama & Tugas yang Diimplementasikan

- [x] **Pendaftaran Bot**: Panduan lengkap pendaftaran BotFather, konfigurasi deskripsi, foto profil, dan perintah.
- [x] **Handler Menu `/start`**: Menampilkan menu interaktif:
  - 📱 **Buka Aplikasi** (Akses langsung ke Telegram Mini App)
  - ⭐ **Jadi VIP** (Katalog 7 paket langganan)
  - 📖 **Tutorial** (Panduan lengkap cara nonton & berlangganan)
  - ❓ **Bantuan** (FAQ & kontak layanan pelanggan)
  - 👥 **Grup Resmi** (Komunitas resmi)
- [x] **Channel Post Listener**: Mendengarkan postingan video baru dari channel privat (CDN storage), otomatis mengekstrak judul drama, nomor episode/part, flag `#vip`, lalu menyimpannya ke database.
- [x] **Logika Pengiriman Video & Pengecekan Akses**:
  - Pengecekan status VIP pengguna sebelum pengiriman.
  - Episode gratis langsung dikirim via `CopyMessage` berkecepatan tinggi.
  - Episode VIP yang diakses pengguna non-VIP otomatis dikunci dan menampilkan tombol ajakan langganan VIP.
- [x] **Menu "Jadi VIP" (7 Pilihan Durasi)**:
  - Mengambil data dari tabel `vip_plans`:
    1. ⚡ 1 Hari Akses - Rp 5.000
    2. ✨ 3 Hari Nonton - Rp 12.000
    3. 🎉 7 Hari (1 Minggu) - Rp 25.000
    4. ⭐ 15 Hari (2 Minggu) - Rp 45.000
    5. 🔥 30 Hari (1 Bulan Terpopuler) - Rp 75.000
    6. 💎 90 Hari (3 Bulan Favorit) - Rp 180.000
    7. 👑 365 Hari (1 Tahun Super VIP) - Rp 500.000
- [x] **Alur Pembayaran QRIS (Payment Coordinator)**:
  - Membuat tagihan QRIS instan saat paket VIP dipilih.
  - Menghasilkan gambar QR Code PNG yang langsung dikirimkan ke chat pengguna.
  - Menyediakan Webhook `POST /api/payment/webhook` untuk konfirmasi pembayaran otomatis dan perpanjangan VIP secara real-time.
  - Fitur dev helper `/simulate_pay <trx_code>` untuk simulasi testing.

---

## 🛠️ Struktur Direktori Proyek

```text
DramaBot/
├── cmd/
│   └── bot/
│       └── main.go                 # Entry point bot dan server HTTP
├── internal/
│   ├── bot/
│   │   ├── bot.go                  # Core controller Telegram Bot
│   │   ├── handlers_command.go     # Handler perintah slash (/start, /vip, dll)
│   │   ├── handlers_channel.go     # Listener channel privat (video indexer)
│   │   ├── handlers_channel_test.go# Unit test parser caption
│   │   ├── handlers_callback.go    # Handler tombol inline keyboard & QRIS flow
│   │   ├── handlers_video.go       # Pengecekan VIP & pengiriman video
│   │   ├── keyboards.go            # Konstruktor tombol inline & reply keyboard
│   │   └── messages.go             # Template teks pesan & caption
│   ├── config/
│   │   └── config.go               # Manajemen konfigurasi environment (.env)
│   ├── database/
│   │   ├── models.go               # Model data (User, VIPPlan, Episode, Trx)
│   │   ├── repository.go           # Kontrak antarmuka (interface) database
│   │   ├── sqlite.go               # Driver database SQLite & auto-seed
│   │   └── sqlite_test.go          # Unit test operasi database
│   ├── payment/
│   │   └── payment.go              # Layanan integrasi Payment Coordinator & QR generator
│   └── server/
│       └── server.go               # HTTP API untuk Webhook & Mini App
├── .env.example                    # Contoh template konfigurasi
├── .env                            # File konfigurasi aktif
├── go.mod
└── README.md
```

---

## 🚀 Panduan Setup & Konfigurasi

### 1. Buat Bot di @BotFather
1. Buka Telegram dan cari bot [@BotFather](https://t.me/BotFather).
2. Kirim perintah `/newbot` dan ikuti petunjuk untuk menentukan **Nama Bot** dan **Username Bot**.
3. Simpan **Bot Token** yang diberikan (misal: `7123456789:AAFlM_ExampleTokenStringHere`).
4. Atur informasi bot:
   - `/setdescription`: Tulis deskripsi yang muncul sebelum user klik start.
   - `/setuserpic`: Unggah foto profil logo DramaBot.
   - `/setcommands`: Daftarkan daftar perintah bot:
     ```text
     start - Tampilkan menu utama & katalog drama
     vip - Beli atau perpanjang paket VIP (7 pilihan)
     tutorial - Panduan cara nonton & berlangganan
     bantuan - Pusat bantuan & FAQ
     status - Cek masa aktif akun VIP Anda
     ```

### 2. Setup Channel Privat Penyimpanan Video
1. Buat **Channel Baru** di Telegram (pilih tipe **Privat**).
2. Masukkan Bot Anda ke dalam Channel tersebut sebagai **Administrator** dengan izin minimal: *Post Messages*.
3. Dapatkan **Channel ID** (biasanya berupa angka minus diawali `-100`, contoh: `-1001234567890`).
   > *Tips:* Anda bisa meneruskan salah satu pesan dari channel ke bot [@userinfobot](https://t.me/userinfobot) atau [@JsonDumpBot](https://t.me/JsonDumpBot) untuk mengetahui Channel ID.
4. Unggah video drama ke channel privat dengan caption terstruktur, contoh:
   - `The Secret CEO - Episode 01` (Otomatis gratis)
   - `The Secret CEO - Episode 03 #vip` (Otomatis VIP)
   - `[Dendam Sang Istri] Part 5 #vip`

### 3. Konfigurasi File `.env`
Buka file `.env` di root direktori dan sesuaikan nilainya:
```env
BOT_TOKEN=7123456789:AAFlM_YourActualBotTokenHere
BOT_USERNAME=dailydramabot
PRIVATE_CHANNEL_ID=-1001234567890
WEB_APP_URL=https://t.me/dailydramabot/app
OFFICIAL_GROUP_URL=https://t.me/dailydrama_official
ADMIN_USER_ID=123456789
SERVER_PORT=8080
DATABASE_PATH=dramabot.db
```

---

## 💻 Menjalankan Aplikasi

### Menjalankan Unit Test
Pastikan seluruh logika parser dan database berjalan sempurna:
```powershell
go test -v ./...
```

### Menjalankan Bot
```powershell
go run ./cmd/bot/main.go
```

---

## 🤝 Integrasi Antar Tim Magang

### 1. Bersama Database Engineer
- File interface telah disiapkan di [`internal/database/repository.go`](file:///c:/projek%20magang/DramaBot/internal/database/repository.go).
- Database default menggunakan SQLite (`dramabot.db`) yang telah dilengkapi migrasi otomatis dan seeding 7 paket VIP.
- Jika Database Engineer ingin menghubungkan ke PostgreSQL / MySQL, cukup buat implementasi struct baru yang memenuhi interface `Repository`.

### 2. Bersama Mini App Developer
Mini App dapat memicu pengiriman episode ke chat pengguna dengan 2 metode:
1. **Deep Link URL (Direkomendasikan)**:
   Saat tombol "Tonton Episode" ditekan di Mini App:
   ```javascript
   // Buka bot dengan parameter deep link:
   window.Telegram.WebApp.openTelegramLink("https://t.me/dailydramabot?start=watch_" + episodeId);
   ```
2. **REST API Endpoint**:
   Backend Mini App dapat memanggil endpoint HTTP bot:
   - **Method**: `POST`
   - **URL**: `http://localhost:8080/api/send-episode`
   - **Body**:
     ```json
     {
       "telegram_id": 123456789,
       "episode_id": 10
     }
     ```
3. **Membaca Katalog Paket VIP**:
   - **Method**: `GET`
   - **URL**: `http://localhost:8080/api/plans`

### 3. Bersama Payment Coordinator
- Alur pembuatan QRIS ditangani di [`internal/payment/payment.go`](file:///c:/projek%20magang/DramaBot/internal/payment/payment.go).
- Bot mengirimkan gambar QR Code PNG beresolusi tinggi langsung ke chat pengguna.
- Setelah pengguna membayar, sistem Payment Gateway / Coordinator mengirim webhook ke Bot:
  - **Method**: `POST`
  - **URL**: `http://localhost:8080/api/payment/webhook`
  - **Body**:
    ```json
    {
      "transaction_code": "TRX12345",
      "status": "PAID"
    }
    ```
  - Bot akan otomatis mengaktifkan status VIP user di database dan mengirimkan pesan ucapan selamat ke Telegram user secara real-time!
- **Testing Pembayaran**: Gunakan perintah Telegram `/simulate_pay TRX12345` untuk menguji alur pembayaran tanpa transfer riil.
