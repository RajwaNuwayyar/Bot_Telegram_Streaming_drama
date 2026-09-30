package bot

import (
	"fmt"
	"time"

	"dramabot/internal/database"
)

// WelcomeMessage menghasilkan teks pesan sambutan dan status pengguna
func WelcomeMessage(firstName string, u *database.User) string {
	vipStatus := "⚪ *Member Reguler* (Gratis)"
	if u != nil && u.IsVIPActive() {
		vipStatus = fmt.Sprintf("👑 *Member VIP Aktif* (s/d %s)", u.VIPUntil.Format("02 Jan 2006 15:04 WIB"))
	}

	return fmt.Sprintf(`👋 *Halo, %s!*

Selamat datang di *DramaBot* — platform streaming drama pendek / mini-drama viral terlengkap dengan subtitle Indonesia! 🎬🍿

📊 *Status Akun Anda:*
%s

🚀 *Fitur Utama:*
• 📱 *Buka Aplikasi:* Buka katalog Mini App drama terkini.
• ⭐ *Jadi VIP:* Buka seluruh episode VIP tanpa batas.
• 📖 *Tutorial:* Panduan mudah cara nonton dan berlangganan.
• ❓ *Bantuan:* Pusat informasi FAQ dan kendala teknis.

Silakan pilih menu di bawah ini untuk memulai:`, firstName, vipStatus)
}

// TutorialMessage menghasilkan pesan teks tutorial cara nonton dan berlangganan
func TutorialMessage() string {
	return `📖 *PANDUAN & TUTORIAL MENONTON DI DRAMABOT*
━━━━━━━━━━━━━━━━━━━━━━━━

*1. Cara Membuka Katalog Drama*
• Klik tombol *📱 Buka Aplikasi* di bawah chat.
• Aplikasi Mini App akan terbuka di dalam Telegram tanpa perlu download aplikasi lain.

*2. Memilih & Memutar Episode*
• Pilih judul drama yang Anda suka di katalog.
• Klik pada nomor episode yang ingin ditonton.
• Bot akan langsung mengirimkan video episode tersebut ke dalam obrolan chat ini!

*3. Episode Gratis vs Episode VIP*
• *Episode Gratis:* Dapat ditonton langsung oleh semua pengguna.
• *Episode VIP:* Memiliki tanda 🔒 dan hanya dapat ditonton oleh pelanggan VIP.

*4. Cara Berlangganan VIP Lewat QRIS*
• Klik tombol *⭐ Jadi VIP*.
• Pilih salah satu dari 7 durasi paket sesuai kebutuhan Anda.
• Bot akan mengirimkan gambar *QRIS resmi*.
• Buka aplikasi M-Banking (BCA, Mandiri, BRI, BNI) atau E-Wallet (GoPay, OVO, DANA, ShopeePay).
• Scan QRIS dan bayar sesuai nominal.
• Akun VIP akan aktif otomatis dalam hitungan detik! 🎉`
}

// HelpMessage menghasilkan teks bantuan, FAQ, dan kontak admin
func HelpMessage(officialGroupURL string) string {
	return fmt.Sprintf(`❓ *PUSAT BANTUAN & FAQ*
━━━━━━━━━━━━━━━━━━━━━━━━

*Q: Video tidak dapat diputar atau loading terus?*
A: Pastikan koneksi internet Anda stabil dan aplikasi Telegram Anda telah diperbarui ke versi terbaru.

*Q: Mengapa video episode dikirim di chat ini?*
A: Telegram bot berfungsi sebagai pengirim video berkecepatan tinggi langsung dari server channel privat kami tanpa kompresi berlebih.

*Q: Saya sudah membayar QRIS tapi status belum VIP?*
A: Pembayaran diproses otomatis 10-60 detik. Anda bisa klik tombol *🔄 Cek Status Pembayaran*. Jika ada kendala, hubungi kami di grup resmi.

*Q: Apakah akun VIP bisa dipakai di perangkat lain?*
A: Ya, selama Anda login ke akun Telegram yang sama.

👥 *Komunitas & Layanan Pelanggan:*
Bergabunglah dengan grup resmi kami untuk update judul drama terbaru dan bantuan langsung dari admin:
👉 [Klik di sini untuk Gabung Grup Resmi](%s)`, officialGroupURL)
}

// VIPInfoMessage menghasilkan ringkasan status VIP dan ajakan berlangganan
func VIPInfoMessage(u *database.User) string {
	status := "⚪ *Status:* Member Reguler"
	if u != nil && u.IsVIPActive() {
		status = fmt.Sprintf("👑 *Status:* VIP Aktif sampai *%s*", u.VIPUntil.Format("02 Jan 2006 15:04 WIB"))
	}

	return fmt.Sprintf(`⭐ *PAKET BERLANGGANAN VIP DRAMABOT*
━━━━━━━━━━━━━━━━━━━━━━━━
%s

🌟 *Keuntungan Menjadi Member VIP:*
✅ Akses *100%% tanpa batas* ke seluruh episode terkunci.
✅ Nonton serial drama terbaru lebih cepat sebelum rilis publik.
✅ Kualitas video HD jernih tanpa jeda iklan.
✅ Pembayaran praktis instan via QRIS semua bank & e-wallet.

Pilih salah satu durasi paket di bawah ini untuk mengaktifkan VIP:`, status)
}

// VideoLockedMessage pesan saat user non-VIP mencoba memutar episode VIP
func VideoLockedMessage(dramaTitle string, epNum int) string {
	return fmt.Sprintf(`🔒 *EPISODE TERKUNCI (KHUSUS VIP)*
━━━━━━━━━━━━━━━━━━━━━━━━
🎬 *Drama:* %s
🔢 *Episode:* %d

Maaf, episode ini hanya dapat diakses oleh *Member VIP*. 

Tingkatkan akun Anda ke status VIP untuk membuka seluruh episode dari drama ini dan ribuan drama seru lainnya! 👇`, dramaTitle, epNum)
}

// PaymentInstructionMessage pesan caption foto QRIS
func PaymentInstructionMessage(tx *database.Transaction) string {
	return fmt.Sprintf(`💳 *TAGIHAN PEMBAYARAN QRIS*
━━━━━━━━━━━━━━━━━━━━━━━━
🆔 *ID Transaksi:* `+"`%s`"+`
📦 *Paket:* %s
💰 *Total Bayar:* *Rp %s*
⏳ *Batas Waktu:* %s WIB (15 Menit)
━━━━━━━━━━━━━━━━━━━━━━━━

*Langkah Pembayaran:*
1. Simpan/Screenshot gambar QRIS di atas.
2. Buka BCA, Mandiri, BRI, BNI, GoPay, OVO, DANA, atau ShopeePay.
3. Pilih fitur *Scan / Bayar QRIS* lalu unggah gambar QR ini.
4. Periksa nama merchant: *DRAMABOT VIP* & nominal: *Rp %s*.
5. Selesaikan pembayaran.

Setelah bayar, bot akan mengaktifkan VIP Anda secara otomatis! 🚀`,
		tx.TrxCode,
		tx.PlanName,
		formatRupiah(tx.Amount),
		tx.ExpiredAt.Format("15:04"),
		formatRupiah(tx.Amount),
	)
}

// PaymentSuccessMessage pesan notifikasi setelah pembayaran sukses
func PaymentSuccessMessage(vipUntil time.Time, planName string) string {
	return fmt.Sprintf(`🎉 *PEMBAYARAN BERHASIL DIVERIFIKASI!*
━━━━━━━━━━━━━━━━━━━━━━━━
Selamat, pembayaran paket *%s* telah diterima.

👑 Akun Anda kini berstatus *VIP AKTIF* sampai:
🗓️ *%s*

Sekarang Anda dapat menonton seluruh episode drama tanpa batas! Selamat menikmati hiburan berkualitas di DramaBot. 🍿🎬`,
		planName,
		vipUntil.Format("02 January 2006 15:04 WIB"),
	)
}
