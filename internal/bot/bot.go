package bot

import (
	"fmt"
	"log"
	"strings"

	tgbotapi "github.com/go-telegram-bot-api/telegram-bot-api/v5"
	"dramabot/internal/config"
	"dramabot/internal/database"
	"dramabot/internal/payment"
)

type Bot struct {
	api     *tgbotapi.BotAPI
	cfg     *config.Config
	repo    database.Repository
	payment payment.PaymentService
}

func NewBot(cfg *config.Config, repo database.Repository, payment payment.PaymentService) (*Bot, error) {
	if cfg.BotToken == "" {
		return nil, fmt.Errorf("BOT_TOKEN tidak ditemukan di konfigurasi (.env)")
	}

	botAPI, err := tgbotapi.NewBotAPI(cfg.BotToken)
	if err != nil {
		return nil, fmt.Errorf("gagal menghubungkan bot ke Telegram API: %w", err)
	}

	log.Printf("[Bot] Berhasil terhubung sebagai @%s\n", botAPI.Self.UserName)

	return &Bot{
		api:     botAPI,
		cfg:     cfg,
		repo:    repo,
		payment: payment,
	}, nil
}

// Start menjalankan update loop (long polling) Telegram
func (b *Bot) Start() {
	u := tgbotapi.NewUpdate(0)
	u.Timeout = 60

	updates := b.api.GetUpdatesChan(u)
	log.Println("[Bot] Mendengarkan update Telegram (Pesan, Channel Post, Callback)...")

	for update := range updates {
		// 1. Tangani postingan video BARU di Channel Privat
		if update.ChannelPost != nil {
			// Cek apakah ini perintah admin (#hapus_episode / #set_poster) dari channel
			if b.handleChannelAdminCommand(update.ChannelPost) {
				continue
			}
			go b.HandleChannelPost(update.ChannelPost)
			continue
		}

		// 1b. Tangani EDIT postingan di Channel Privat (misal admin edit judul drama atau ubah status VIP dari caption)
		if update.EditedChannelPost != nil {
			if b.handleChannelAdminCommand(update.EditedChannelPost) {
				continue
			}
			go b.HandleEditedChannelPost(update.EditedChannelPost)
			continue
		}

		// 2. Tangani interaksi tombol inline (Callback Query)
		if update.CallbackQuery != nil {
			go b.HandleCallbackQuery(update.CallbackQuery)
			continue
		}

		// 3. Tangani pesan langsung dari chat user
		if update.Message != nil {
			// Perintah slash (misal /start, /vip, /help)
			if update.Message.IsCommand() {
				go b.HandleCommand(update.Message)
				continue
			}

			// Cek upload poster langsung dari admin ke bot dengan tag #poster
			if update.Message.From != nil && b.cfg.AdminUserID != 0 && update.Message.From.ID == b.cfg.AdminUserID && isPosterUpload(update.Message, update.Message.Caption) {
				go b.handlePosterPost(update.Message)
				continue
			}

			// Cek upload bukti transfer dari admin ke bot dengan tag #WD_
			if update.Message.From != nil && b.cfg.AdminUserID != 0 && update.Message.From.ID == b.cfg.AdminUserID {
				if hasAffiliateTransferTag(update.Message) {
					go b.handleTransferProof(update.Message, extractWithdrawalID(update.Message.Caption))
					continue
				}
			}

			// Pesan teks biasa / tombol reply keyboard
			go b.HandleTextMessage(update.Message)
		}
	}
}

// NotifyPaymentSuccess mengirimkan pesan notifikasi langsung ke user saat pembayaran berhasil diverifikasi
func (b *Bot) NotifyPaymentSuccess(tx *database.Transaction) error {
	user, err := b.repo.GetUserByTelegramID(tx.TelegramID)
	if err != nil || user == nil {
		return fmt.Errorf("user tidak ditemukan")
	}

	msgText := PaymentSuccessMessage(user.VIPUntil, tx.PlanName)
	msg := tgbotapi.NewMessage(tx.TelegramID, msgText)
	msg.ParseMode = "Markdown"

	// Berikan tombol langsung ke Mini App
	btn := tgbotapi.NewInlineKeyboardMarkup(
		tgbotapi.NewInlineKeyboardRow(
			tgbotapi.NewInlineKeyboardButtonURL("🍿 Buka Mini App & Mulai Nonton", b.cfg.WebAppURL),
		),
	)
	msg.ReplyMarkup = btn

	_, err = b.api.Send(msg)
	
	// Notifikasi japri ke admin
	if b.cfg.AdminUserID != 0 {
		adminText := fmt.Sprintf(`💸 *Pembayaran VIP Berhasil!*
━━━━━━━━━━━━━━━━━━━━
👤 *User ID:* `+"`%d`"+`
📝 *Nama:* %s %s
🛍️ *Paket:* %s
💰 *Nominal:* Rp %.0f
📆 *VIP Sampai:* %s`, 
			user.TelegramID, user.FirstName, user.LastName, tx.PlanName, tx.Amount, user.VIPUntil.Format("02 Jan 2006"))
		
		adminMsg := tgbotapi.NewMessage(b.cfg.AdminUserID, adminText)
		adminMsg.ParseMode = "Markdown"
		_, _ = b.api.Send(adminMsg)
	}
	
	return err
}

// GetAPI mengembalikan instance underlying bot API untuk integrasi tambahan
func (b *Bot) GetAPI() *tgbotapi.BotAPI {
	return b.api
}

// hasAffiliateTransferTag mengecek apakah pesan mengandung tag #WD_ (bukti tf admin)
func hasAffiliateTransferTag(msg *tgbotapi.Message) bool {
	if msg == nil {
		return false
	}
	// Pastikan ada foto atau dokumen
	if len(msg.Photo) == 0 && msg.Document == nil {
		return false
	}
	caption := strings.ToUpper(msg.Caption)
	return strings.Contains(caption, "#WD_")
}

// extractWithdrawalID mengekstrak ID numerik dari teks seperti #WD_123
func extractWithdrawalID(caption string) string {
	caption = strings.ToUpper(caption)
	idx := strings.Index(caption, "#WD_")
	if idx == -1 {
		return ""
	}
	
	// potong mulai dari karakter setelah #WD_
	sub := caption[idx+4:]
	
	// ambil angka sampai spasi atau karakter non-digit
	idStr := ""
	for _, char := range sub {
		if char >= '0' && char <= '9' {
			idStr += string(char)
		} else {
			break
		}
	}
	return idStr
}
