package bot

import (
	"fmt"
	"log"
	"strconv"
	"strings"

	tgbotapi "github.com/go-telegram-bot-api/telegram-bot-api/v5"
	"dramabot/internal/database"
)

// HandleCommand memproses seluruh perintah berawalan slash (/)
func (b *Bot) HandleCommand(msg *tgbotapi.Message) {
	command := msg.Command()
	args := strings.TrimSpace(msg.CommandArguments())
	telegramID := msg.Chat.ID

	// Daftarkan / perbarui profil user di database
	b.saveOrUpdateUser(msg.From)

	switch command {
	case "start":
		b.handleStart(msg, args)
	case "help", "bantuan":
		b.handleHelp(telegramID)
	case "tutorial":
		b.handleTutorial(telegramID)
	case "vip":
		b.handleVIPMenu(telegramID)
	case "status":
		b.handleStatus(telegramID)
	case "simulate_pay":
		b.handleSimulatePay(telegramID, args)
	case "hapus_episode":
		// Hanya admin yang bisa menghapus episode dari database
		b.handleHapusEpisode(telegramID, args)
	default:
		// Default tampilkan menu utama
		b.handleStart(msg, "")
	}
}

// handleStart menangani /start dan deep linking saat user memilih episode di Mini App
func (b *Bot) handleStart(msg *tgbotapi.Message, args string) {
	telegramID := msg.Chat.ID

	// Cek apakah ada parameter deep linking dari Mini App:
	// Contoh: /start watch_15 atau /start ep_15
	if args != "" {
		if strings.HasPrefix(args, "watch_") || strings.HasPrefix(args, "ep_") {
			parts := strings.Split(args, "_")
			if len(parts) >= 2 {
				episodeID, err := strconv.ParseInt(parts[1], 10, 64)
				if err == nil && episodeID > 0 {
					log.Printf("[Bot] Deep-link terdeteksi: User %d ingin menonton episode %d\n", telegramID, episodeID)
					_ = b.SendEpisode(telegramID, episodeID)
					return
				}
			}
		}
	}

	// Tampilkan Menu Utama /start sesuai instruksi tugas:
	// Menu: Buka Aplikasi, Bantuan, Tutorial, Jadi VIP, Grup Resmi
	user, _ := b.repo.GetUserByTelegramID(telegramID)
	welcomeText := WelcomeMessage(msg.From.FirstName, user)

	replyMsg := tgbotapi.NewMessage(telegramID, welcomeText)
	replyMsg.ParseMode = "Markdown"
	replyMsg.ReplyMarkup = MainMenuInlineKeyboard(b.cfg.WebAppURL, b.cfg.OfficialGroupURL)

	if _, err := b.api.Send(replyMsg); err != nil {
		log.Printf("[Bot] Gagal mengirim pesan /start: %v\n", err)
	}

	// Kirim juga reply keyboard bawah agar user mudah mengakses menu kapan saja
	bottomMenu := tgbotapi.NewMessage(telegramID, "👇 Akses cepat tombol menu:")
	bottomMenu.ReplyMarkup = MainMenuReplyKeyboard(b.cfg.WebAppURL)
	_, _ = b.api.Send(bottomMenu)
}

// handleHelp menampilkan menu bantuan dan FAQ
func (b *Bot) handleHelp(telegramID int64) {
	msg := tgbotapi.NewMessage(telegramID, HelpMessage(b.cfg.OfficialGroupURL))
	msg.ParseMode = "Markdown"
	msg.DisableWebPagePreview = true
	_, _ = b.api.Send(msg)
}

// handleTutorial menampilkan panduan penggunaan
func (b *Bot) handleTutorial(telegramID int64) {
	msg := tgbotapi.NewMessage(telegramID, TutorialMessage())
	msg.ParseMode = "Markdown"
	_, _ = b.api.Send(msg)
}

// handleVIPMenu menampilkan daftar 7 paket VIP dari tabel vip_plans
func (b *Bot) handleVIPMenu(telegramID int64) {
	user, _ := b.repo.GetUserByTelegramID(telegramID)
	plans, err := b.repo.GetActiveVIPPlans()
	if err != nil || len(plans) == 0 {
		msg := tgbotapi.NewMessage(telegramID, "⚠️ Daftar paket VIP sedang diperbarui oleh tim. Silakan coba kembali sesaat lagi.")
		_, _ = b.api.Send(msg)
		return
	}

	infoText := VIPInfoMessage(user)
	msg := tgbotapi.NewMessage(telegramID, infoText)
	msg.ParseMode = "Markdown"
	msg.ReplyMarkup = VIPPlansKeyboard(plans)
	_, _ = b.api.Send(msg)
}

// handleStatus menampilkan status keanggotaan pengguna
func (b *Bot) handleStatus(telegramID int64) {
	user, _ := b.repo.GetUserByTelegramID(telegramID)
	if user == nil || !user.IsVIPActive() {
		msg := tgbotapi.NewMessage(telegramID, "⚪ Status Anda saat ini: *Member Reguler* (Gratis)\n\nSilakan klik /vip untuk berlangganan.")
		msg.ParseMode = "Markdown"
		_, _ = b.api.Send(msg)
		return
	}

	statusText := fmt.Sprintf(`👑 *STATUS AKUN VIP AKTIF*
━━━━━━━━━━━━━━━━━━━━━━━━
Nama: %s
Telegram ID: `+"`%d`"+`
Status VIP: *Aktif*
Masa Berlaku s/d: *%s*

Nikmati akses tanpa batas ke seluruh episode! 🍿`,
		user.FirstName,
		user.TelegramID,
		user.VIPUntil.Format("02 January 2006 15:04 WIB"),
	)

	msg := tgbotapi.NewMessage(telegramID, statusText)
	msg.ParseMode = "Markdown"
	_, _ = b.api.Send(msg)
}

// handleSimulatePay mempermudah testing pembayaran tanpa gateway nyata
func (b *Bot) handleSimulatePay(telegramID int64, trxCode string) {
	if trxCode == "" {
		msg := tgbotapi.NewMessage(telegramID, "⚠️ Format salah. Gunakan: `/simulate_pay <TRX_CODE>`")
		msg.ParseMode = "Markdown"
		_, _ = b.api.Send(msg)
		return
	}

	tx, err := b.repo.MarkTransactionPaid(trxCode)
	if err != nil || tx == nil {
		msg := tgbotapi.NewMessage(telegramID, fmt.Sprintf("❌ Transaksi `%s` tidak ditemukan atau gagal diproses.", trxCode))
		msg.ParseMode = "Markdown"
		_, _ = b.api.Send(msg)
		return
	}

	user, _ := b.repo.GetUserByTelegramID(tx.TelegramID)
	successMsg := tgbotapi.NewMessage(tx.TelegramID, PaymentSuccessMessage(user.VIPUntil, tx.PlanName))
	successMsg.ParseMode = "Markdown"
	_, _ = b.api.Send(successMsg)

	log.Printf("[SimulatePay] Transaksi %s berhasil disimulasikan lunas untuk user %d\n", trxCode, tx.TelegramID)
}

// handleHapusEpisode menghapus episode dari database berdasarkan telegram_message_id.
// Perintah: /hapus_episode <message_id>
// Cara dapat message_id: klik kanan/tahan postingan di channel → Salin Tautan → angka di akhir URL.
// Hanya bisa dijalankan oleh admin (AdminUserID di config).
func (b *Bot) handleHapusEpisode(telegramID int64, args string) {
	// Cek hak akses admin
	if b.cfg.AdminUserID == 0 || telegramID != b.cfg.AdminUserID {
		msg := tgbotapi.NewMessage(telegramID, "⛔ Perintah ini hanya bisa digunakan oleh admin.")
		_, _ = b.api.Send(msg)
		return
	}

	if args == "" {
		msg := tgbotapi.NewMessage(telegramID,
			"⚠️ *Format salah.*\n\nGunakan: `/hapus_episode <message_id>`\n\n"+
				"💡 *Cara dapat Message ID:*\n"+
				"Buka channel privat → klik kanan/tahan postingan → *Salin Tautan* → angka di akhir URL adalah Message ID.")
		msg.ParseMode = "Markdown"
		_, _ = b.api.Send(msg)
		return
	}

	messageID, err := strconv.Atoi(strings.TrimSpace(args))
	if err != nil || messageID <= 0 {
		msg := tgbotapi.NewMessage(telegramID, "❌ Message ID tidak valid. Masukkan angka yang benar.")
		_, _ = b.api.Send(msg)
		return
	}

	if err := b.repo.DeleteEpisodeByMessageID(messageID); err != nil {
		log.Printf("[Admin] Gagal hapus episode MsgID %d: %v\n", messageID, err)
		msg := tgbotapi.NewMessage(telegramID,
			fmt.Sprintf("❌ Gagal menghapus episode.\n\nKemungkinan episode dengan Message ID `%d` tidak ada di database.", messageID))
		msg.ParseMode = "Markdown"
		_, _ = b.api.Send(msg)
		return
	}

	log.Printf("[Admin] Episode MsgID %d berhasil dihapus dari database oleh admin %d\n", messageID, telegramID)
	successMsg := tgbotapi.NewMessage(telegramID,
		fmt.Sprintf("✅ *Episode berhasil dihapus dari database!*\n\n🆔 Message ID: `%d`\n💾 Data episode telah dihapus dan tidak akan muncul lagi di Mini App.", messageID))
	successMsg.ParseMode = "Markdown"
	_, _ = b.api.Send(successMsg)
}

func (b *Bot) saveOrUpdateUser(from *tgbotapi.User) {
	if from == nil {
		return
	}
	u := &database.User{
		TelegramID: from.ID,
		Username:   from.UserName,
		FirstName:  from.FirstName,
		LastName:   from.LastName,
	}
	_ = b.repo.UpsertUser(u)
}
