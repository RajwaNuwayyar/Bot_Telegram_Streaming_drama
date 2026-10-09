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
	case "set_poster":
		// Admin mengatur poster drama via command
		b.handleSetPoster(telegramID, args)
	case "hapus_poster", "delete_poster":
		// Admin menghapus/mereset poster drama via command
		b.handleDeletePoster(telegramID, args)
	case "edit_drama", "update_drama", "ubah_drama", "edit_judul":
		// Admin mengubah judul drama via command
		b.handleEditDrama(telegramID, args)
	case "tf":
		// Admin mengirim bukti transfer penarikan affiliate
		b.handleTransferProof(msg, args)
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

// handleSetPoster memungkinkan admin mengatur atau memperbarui file_id poster drama secara manual.
// Penggunaan: /set_poster <Judul Drama> | <file_id>
func (b *Bot) handleSetPoster(telegramID int64, args string) {
	if b.cfg.AdminUserID == 0 || telegramID != b.cfg.AdminUserID {
		msg := tgbotapi.NewMessage(telegramID, "⛔ Perintah ini hanya bisa digunakan oleh admin.")
		_, _ = b.api.Send(msg)
		return
	}

	args = strings.TrimSpace(args)
	if args == "" {
		msg := tgbotapi.NewMessage(telegramID,
			"⚠️ *Format salah.*\n\nGunakan: `/set_poster <Judul Drama> | <file_id>`\n\n"+
				"💡 *Contoh:*\n`/set_poster GrandBlue | AgACAgUAAxkBA...`\n`/set_poster Charlotte | AgACAgUAAxkBA...`\n\n"+
				"Atau Anda cukup mengunggah foto langsung ke Channel/Bot dengan caption `#poster`.")
		msg.ParseMode = "Markdown"
		_, _ = b.api.Send(msg)
		return
	}

	var title, fileID string
	if strings.Contains(args, "|") {
		parts := strings.SplitN(args, "|", 2)
		title = strings.TrimSpace(parts[0])
		fileID = strings.TrimSpace(parts[1])
	} else {
		parts := strings.Fields(args)
		if len(parts) >= 2 {
			fileID = parts[len(parts)-1]
			title = strings.Join(parts[:len(parts)-1], " ")
		}
	}

	if title == "" || fileID == "" {
		msg := tgbotapi.NewMessage(telegramID, "❌ Judul drama dan File ID harus diisi.")
		_, _ = b.api.Send(msg)
		return
	}

	if err := b.repo.UpdateDramaPoster(title, fileID); err != nil {
		log.Printf("[Admin] Gagal set poster drama '%s': %v\n", title, err)
		msg := tgbotapi.NewMessage(telegramID, fmt.Sprintf("❌ Gagal update poster: %v", err))
		_, _ = b.api.Send(msg)
		return
	}

	log.Printf("[Admin] Poster drama '%s' berhasil diupdate oleh admin %d (FileID: %s)\n", title, telegramID, fileID)
	successMsg := tgbotapi.NewMessage(telegramID,
		fmt.Sprintf("✅ *Poster drama berhasil diperbarui!*\n\n🎬 *Judul:* %s\n🔑 *File ID:* `%s`\n💾 Perubahan telah tersimpan di database.", title, fileID))
	successMsg.ParseMode = "Markdown"
	_, _ = b.api.Send(successMsg)
}

func (b *Bot) handleDeletePoster(telegramID int64, args string) {
	if b.cfg.AdminUserID == 0 || telegramID != b.cfg.AdminUserID {
		msg := tgbotapi.NewMessage(telegramID, "⛔ Perintah ini hanya bisa digunakan oleh admin.")
		_, _ = b.api.Send(msg)
		return
	}

	title := strings.TrimSpace(args)
	if title == "" {
		msg := tgbotapi.NewMessage(telegramID,
			"⚠️ *Format salah.*\n\nGunakan: `/hapus_poster <Judul Drama>`\n\n"+
				"💡 *Contoh:*\n`/hapus_poster GrandBlue`\n`/hapus_poster Charlotte`\n\n"+
				"Poster akan direset kembali ke tampilan default.")
		msg.ParseMode = "Markdown"
		_, _ = b.api.Send(msg)
		return
	}

	if err := b.repo.DeleteDramaPoster(title); err != nil {
		log.Printf("[Admin] Gagal hapus poster drama '%s': %v\n", title, err)
		msg := tgbotapi.NewMessage(telegramID, fmt.Sprintf("❌ Gagal menghapus poster: %v", err))
		_, _ = b.api.Send(msg)
		return
	}

	log.Printf("[Admin] Poster drama '%s' berhasil dihapus oleh admin %d\n", title, telegramID)
	successMsg := tgbotapi.NewMessage(telegramID,
		fmt.Sprintf("🗑️ *Poster drama berhasil dihapus!*\n\n🎬 *Judul:* %s\n💾 Poster telah direset kembali ke tampilan default di Mini App.", title))
	successMsg.ParseMode = "Markdown"
	_, _ = b.api.Send(successMsg)
}

// handleEditDrama memungkinkan admin mengubah/memperbarui judul drama beserta semua episodenya via DM bot.
// Penggunaan: /edit_drama <Judul Lama> | <Judul Baru>
func (b *Bot) handleEditDrama(telegramID int64, args string) {
	if b.cfg.AdminUserID == 0 || telegramID != b.cfg.AdminUserID {
		msg := tgbotapi.NewMessage(telegramID, "⛔ Perintah ini hanya bisa digunakan oleh admin.")
		_, _ = b.api.Send(msg)
		return
	}

	args = strings.TrimSpace(args)
	if args == "" || !strings.Contains(args, "|") {
		msg := tgbotapi.NewMessage(telegramID,
			"⚠️ *Format salah.*\n\n"+
				"Gunakan: `/edit_drama <Judul Lama> | <Judul Baru>`\n\n"+
				"💡 *Contoh:*\n"+
				"`/edit_drama Grand Blue | Grand Blue Dreaming`\n"+
				"`/edit_drama The Secret CEO | The Secret Billionaire`\n\n"+
				"Seluruh episode milik drama ini akan otomatis diperbarui judulnya di database dan Mini App.")
		msg.ParseMode = "Markdown"
		_, _ = b.api.Send(msg)
		return
	}

	parts := strings.SplitN(args, "|", 2)
	oldTitle := strings.TrimSpace(parts[0])
	newTitle := strings.TrimSpace(parts[1])

	if oldTitle == "" || newTitle == "" {
		msg := tgbotapi.NewMessage(telegramID, "❌ Judul lama dan judul baru harus diisi.")
		_, _ = b.api.Send(msg)
		return
	}

	epUpdated, err := b.repo.UpdateDramaTitle(oldTitle, newTitle)
	if err != nil {
		log.Printf("[Admin] Gagal update drama '%s' -> '%s': %v\n", oldTitle, newTitle, err)
		msg := tgbotapi.NewMessage(telegramID, fmt.Sprintf("❌ Gagal memperbarui judul drama: %v", err))
		_, _ = b.api.Send(msg)
		return
	}

	log.Printf("[Admin] Drama '%s' berhasil diubah menjadi '%s' (%d episode) oleh admin %d\n", oldTitle, newTitle, epUpdated, telegramID)
	successMsg := tgbotapi.NewMessage(telegramID,
		fmt.Sprintf("✅ *Judul drama berhasil diperbarui!*\n\n🎬 *Judul Lama:* %s\n✨ *Judul Baru:* %s\n🔢 *Episode Diperbarui:* %d episode\n💾 Perubahan telah tersimpan di database dan Mini App.",
			oldTitle, newTitle, epUpdated))
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

// handleTransferProof memproses bukti transfer penarikan affiliate dari admin
// Penggunaan: /tf <ID_PENARIKAN> pada caption foto
func (b *Bot) handleTransferProof(msg *tgbotapi.Message, args string) {
	telegramID := msg.Chat.ID
	if b.cfg.AdminUserID == 0 || telegramID != b.cfg.AdminUserID {
		reply := tgbotapi.NewMessage(telegramID, "⛔ Perintah ini hanya bisa digunakan oleh admin.")
		_, _ = b.api.Send(reply)
		return
	}

	args = strings.TrimSpace(args)
	if args == "" {
		reply := tgbotapi.NewMessage(telegramID, "⚠️ Format salah. Gunakan: `/tf <ID_PENARIKAN>` pada caption foto.")
		reply.ParseMode = "Markdown"
		_, _ = b.api.Send(reply)
		return
	}

	wdID, err := strconv.ParseInt(args, 10, 64)
	if err != nil || wdID <= 0 {
		reply := tgbotapi.NewMessage(telegramID, "❌ ID Penarikan tidak valid.")
		_, _ = b.api.Send(reply)
		return
	}

	// Cek apakah melampirkan foto
	var photoFileID string
	if len(msg.Photo) > 0 {
		photoFileID = msg.Photo[len(msg.Photo)-1].FileID
	} else if msg.Document != nil && strings.HasPrefix(msg.Document.MimeType, "image/") {
		photoFileID = msg.Document.FileID
	}

	if photoFileID == "" {
		reply := tgbotapi.NewMessage(telegramID, "❌ Anda harus melampirkan foto bukti transfer bersama dengan perintah ini.")
		_, _ = b.api.Send(reply)
		return
	}

	// Proses update di database
	targetTelegramID, err := b.repo.CompleteAffiliateWithdrawal(wdID)
	if err != nil {
		log.Printf("[Admin] Gagal menyelesaikan penarikan ID %d: %v\n", wdID, err)
		reply := tgbotapi.NewMessage(telegramID, fmt.Sprintf("❌ Gagal menyelesaikan penarikan: %v", err))
		_, _ = b.api.Send(reply)
		return
	}

	// Kirim sukses ke Admin
	replyAdmin := tgbotapi.NewMessage(telegramID, fmt.Sprintf("✅ *Penarikan Selesai!*\n\nStatus penarikan ID `#WD_%d` telah diubah menjadi `terkirim`.\nBukti transfer sedang diteruskan ke user bersangkutan.", wdID))
	replyAdmin.ParseMode = "Markdown"
	_, _ = b.api.Send(replyAdmin)

	// Kirim notif dan foto ke User
	notifUser := tgbotapi.NewPhoto(targetTelegramID, tgbotapi.FileID(photoFileID))
	notifUser.Caption = fmt.Sprintf("🎉 *PENARIKAN BERHASIL!*\n\nPenarikan saldo Affiliate Anda (ID `#WD_%d`) telah berhasil diproses dan ditransfer ke rekening/DANA Anda.\n\nTerima kasih telah berpartisipasi dalam program affiliate kami!", wdID)
	notifUser.ParseMode = "Markdown"
	
	if _, err := b.api.Send(notifUser); err != nil {
		log.Printf("[Bot] Gagal mengirim bukti transfer ke user %d: %v\n", targetTelegramID, err)
		// Beritahu admin jika gagal kirim ke user
		failNotif := tgbotapi.NewMessage(telegramID, fmt.Sprintf("⚠️ Berhasil diproses di database, tapi bot GAGAL mengirim pesan ke user (mungkin bot diblokir oleh user)."))
		_, _ = b.api.Send(failNotif)
	}
}
