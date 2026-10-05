package bot

import (
	"fmt"
	"log"

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
			go b.HandleChannelPost(update.ChannelPost)
			continue
		}

		// 1b. Tangani EDIT postingan di Channel Privat (misal admin ubah/hapus #vip dari caption)
		if update.EditedChannelPost != nil {
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
	return err
}

// GetAPI mengembalikan instance underlying bot API untuk integrasi tambahan
func (b *Bot) GetAPI() *tgbotapi.BotAPI {
	return b.api
}
