package bot

import (
	"fmt"
	"log"
	"strconv"
	"strings"

	tgbotapi "github.com/go-telegram-bot-api/telegram-bot-api/v5"
	"dramabot/internal/database"
)

// HandleCallbackQuery memproses interaksi tombol inline Telegram
func (b *Bot) HandleCallbackQuery(callback *tgbotapi.CallbackQuery) {
	data := callback.Data
	telegramID := callback.Message.Chat.ID
	messageID := callback.Message.MessageID

	// Beri feedback cepat ke Telegram agar animasi loading di tombol hilang
	b.answerCallback(callback.ID, "")

	if data == "menu_start" {
		user, _ := b.repo.GetUserByTelegramID(telegramID)
		welcomeText := WelcomeMessage(callback.From.FirstName, user)
		editMsg := tgbotapi.NewEditMessageText(telegramID, messageID, welcomeText)
		editMsg.ParseMode = "Markdown"
		markup := MainMenuInlineKeyboard(b.cfg.WebAppURL, b.cfg.OfficialGroupURL)
		editMsg.ReplyMarkup = &markup
		_, _ = b.api.Send(editMsg)
		return
	}

	if data == "menu_vip" {
		b.handleVIPMenu(telegramID)
		return
	}

	if data == "menu_tutorial" {
		b.handleTutorial(telegramID)
		return
	}

	if data == "menu_help" {
		b.handleHelp(telegramID)
		return
	}

	// 1. Memilih Paket VIP: buy_plan:<plan_id>
	if strings.HasPrefix(data, "buy_plan:") {
		parts := strings.Split(data, ":")
		if len(parts) == 2 {
			planID, _ := strconv.ParseInt(parts[1], 10, 64)
			b.processBuyPlan(telegramID, planID)
		}
		return
	}

	// 2. Cek Status Pembayaran: check_pay:<trx_code>
	if strings.HasPrefix(data, "check_pay:") {
		parts := strings.Split(data, ":")
		if len(parts) == 2 {
			trxCode := parts[1]
			b.processCheckPayment(callback.ID, telegramID, trxCode)
		}
		return
	}

	// 3. Batalkan Pembayaran: cancel_pay:<trx_code>
	if strings.HasPrefix(data, "cancel_pay:") {
		parts := strings.Split(data, ":")
		if len(parts) == 2 {
			trxCode := parts[1]
			_ = b.repo.CancelTransaction(trxCode)
			editMsg := tgbotapi.NewEditMessageCaption(telegramID, messageID, fmt.Sprintf("❌ Tagihan transaksi `%s` telah dibatalkan.", trxCode))
			editMsg.ParseMode = "Markdown"
			_, _ = b.api.Send(editMsg)
		}
		return
	}

	// 4. Navigasi Tonton Episode: watch:<episode_id>
	if strings.HasPrefix(data, "watch:") {
		parts := strings.Split(data, ":")
		if len(parts) == 2 {
			epID, _ := strconv.ParseInt(parts[1], 10, 64)
			_ = b.SendEpisode(telegramID, epID)
		}
		return
	}
}

// processBuyPlan memproses pembuatan QRIS dan mengirimkannya ke pengguna
func (b *Bot) processBuyPlan(telegramID int64, planID int64) {
	plan, err := b.repo.GetVIPPlanByID(planID)
	if err != nil || plan == nil {
		msg := tgbotapi.NewMessage(telegramID, "⚠️ Paket VIP yang dipilih tidak valid atau sudah kedaluwarsa.")
		_, _ = b.api.Send(msg)
		return
	}

	// Notifikasi loading sementara
	loadingMsg := tgbotapi.NewMessage(telegramID, "⏳ Menghubungi Payment Coordinator untuk membuat kode QRIS...")
	sentLoading, _ := b.api.Send(loadingMsg)

	// 1. Minta QRIS dari Payment Coordinator
	tx, qrBytes, err := b.payment.RequestQRIS(telegramID, plan)
	if err != nil {
		log.Printf("[Payment] Gagal generate QRIS: %v\n", err)
		failMsg := tgbotapi.NewMessage(telegramID, "❌ Gagal memproses permintaan QRIS ke sistem pembayaran. Silakan coba sesaat lagi.")
		_, _ = b.api.Send(failMsg)
		return
	}

	// 2. Simpan Transaksi ke database
	if err := b.repo.CreateTransaction(tx); err != nil {
		log.Printf("[Payment] Gagal menyimpan transaksi ke database: %v\n", err)
		failMsg := tgbotapi.NewMessage(telegramID, "❌ Terjadi kendala teknis dalam membuat invoice. Silakan coba kembali.")
		_, _ = b.api.Send(failMsg)
		return
	}

	// Hapus pesan loading jika ada
	if sentLoading.MessageID != 0 {
		delMsg := tgbotapi.NewDeleteMessage(telegramID, sentLoading.MessageID)
		_, _ = b.api.Send(delMsg)
	}

	// 3. Kirim Gambar QRIS ke pengguna
	photoFileBytes := tgbotapi.FileBytes{
		Name:  fmt.Sprintf("QRIS_%s.png", tx.TrxCode),
		Bytes: qrBytes,
	}

	photoConfig := tgbotapi.NewPhoto(telegramID, photoFileBytes)
	photoConfig.Caption = PaymentInstructionMessage(tx)
	photoConfig.ParseMode = "Markdown"
	photoConfig.ReplyMarkup = PaymentActionsKeyboard(tx.TrxCode)

	if _, err := b.api.Send(photoConfig); err != nil {
		log.Printf("[Payment] Gagal mengirim foto QRIS ke user: %v\n", err)
	}
}

// processCheckPayment memverifikasi status pembayaran saat user menekan tombol 'Cek Status'
func (b *Bot) processCheckPayment(callbackID string, telegramID int64, trxCode string) {
	tx, err := b.repo.GetTransactionByCode(trxCode)
	if err != nil || tx == nil {
		b.answerCallback(callbackID, "❌ Transaksi tidak ditemukan.")
		return
	}

	if tx.Status == database.TxStatusPaid {
		user, _ := b.repo.GetUserByTelegramID(telegramID)
		b.answerCallback(callbackID, "🎉 Pembayaran Berhasil Terkonfirmasi!")
		msg := tgbotapi.NewMessage(telegramID, PaymentSuccessMessage(user.VIPUntil, tx.PlanName))
		msg.ParseMode = "Markdown"
		_, _ = b.api.Send(msg)
		return
	}

	// Jika masih pending
	alertText := fmt.Sprintf("⏳ Transaksi %s belum terbayar. Harap selesaikan pembayaran scan QRIS.", trxCode)
	b.answerCallbackWithAlert(callbackID, alertText)
}

// HandleTextMessage menangani pesan teks dari tombol menu reply keyboard
func (b *Bot) HandleTextMessage(msg *tgbotapi.Message) {
	text := strings.TrimSpace(msg.Text)
	telegramID := msg.Chat.ID

	switch text {
	case "⭐ Jadi VIP":
		b.handleVIPMenu(telegramID)
	case "📖 Tutorial":
		b.handleTutorial(telegramID)
	case "❓ Bantuan":
		b.handleHelp(telegramID)
	case "👥 Grup Resmi":
		linkMsg := tgbotapi.NewMessage(telegramID, fmt.Sprintf("👉 Klik untuk bergabung ke grup resmi kami: %s", b.cfg.OfficialGroupURL))
		_, _ = b.api.Send(linkMsg)
	case "🚀 Buka Aplikasi":
		linkMsg := tgbotapi.NewMessage(telegramID, fmt.Sprintf("👉 Klik untuk membuka Mini App: %s", b.cfg.WebAppURL))
		_, _ = b.api.Send(linkMsg)
	default:
		// Pesan teks bebas lainnya: arahkan ke bantuan atau /start
		b.handleStart(msg, "")
	}
}

func (b *Bot) answerCallback(callbackID, text string) {
	ans := tgbotapi.NewCallback(callbackID, text)
	_, _ = b.api.Send(ans)
}

func (b *Bot) answerCallbackWithAlert(callbackID, text string) {
	ans := tgbotapi.NewCallbackWithAlert(callbackID, text)
	_, _ = b.api.Send(ans)
}
