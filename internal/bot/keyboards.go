package bot

import (
	"fmt"
	"strings"

	tgbotapi "github.com/go-telegram-bot-api/telegram-bot-api/v5"
	"dramabot/internal/database"
)

// MainMenuInlineKeyboard membuat tombol inline menu utama sesuai tugas
func MainMenuInlineKeyboard(webAppURL, officialGroupURL string) tgbotapi.InlineKeyboardMarkup {
	var rows [][]tgbotapi.InlineKeyboardButton

	// Tombol Buka Aplikasi (Mini App Telegram via URL link)
	openAppBtn := tgbotapi.NewInlineKeyboardButtonURL("📱 Buka Aplikasi", webAppURL)
	rows = append(rows, tgbotapi.NewInlineKeyboardRow(openAppBtn))

	// Baris 2: Jadi VIP
	rows = append(rows, tgbotapi.NewInlineKeyboardRow(
		tgbotapi.NewInlineKeyboardButtonData("⭐ Jadi VIP", "menu_vip"),
	))

	// Baris 3: Tutorial & Bantuan
	rows = append(rows, tgbotapi.NewInlineKeyboardRow(
		tgbotapi.NewInlineKeyboardButtonData("📖 Tutorial", "menu_tutorial"),
		tgbotapi.NewInlineKeyboardButtonData("❓ Bantuan", "menu_help"),
	))

	// Baris 4: Grup Resmi
	rows = append(rows, tgbotapi.NewInlineKeyboardRow(
		tgbotapi.NewInlineKeyboardButtonURL("👥 Grup Resmi", officialGroupURL),
	))

	return tgbotapi.NewInlineKeyboardMarkup(rows...)
}

// MainMenuReplyKeyboard membuat menu tombol di keyboard bawah chat
func MainMenuReplyKeyboard(webAppURL string) tgbotapi.ReplyKeyboardMarkup {
	keyboard := tgbotapi.NewReplyKeyboard(
		tgbotapi.NewKeyboardButtonRow(
			tgbotapi.NewKeyboardButton("🚀 Buka Aplikasi"),
		),
		tgbotapi.NewKeyboardButtonRow(
			tgbotapi.NewKeyboardButton("⭐ Jadi VIP"),
			tgbotapi.NewKeyboardButton("📖 Tutorial"),
		),
		tgbotapi.NewKeyboardButtonRow(
			tgbotapi.NewKeyboardButton("❓ Bantuan"),
			tgbotapi.NewKeyboardButton("👥 Grup Resmi"),
		),
	)
	keyboard.ResizeKeyboard = true
	return keyboard
}

// VIPPlansKeyboard menampilkan 7 pilihan paket VIP dari tabel vip_plans
func VIPPlansKeyboard(plans []database.VIPPlan) tgbotapi.InlineKeyboardMarkup {
	var rows [][]tgbotapi.InlineKeyboardButton

	for _, p := range plans {
		// Format label: [🔥] 30 Hari - Rp 75.000
		badgeText := ""
		if p.Badge != "" {
			badgeText = p.Badge + " "
		}
		btnText := fmt.Sprintf("%s%s • Rp %s", badgeText, p.Name, formatRupiah(p.Price))
		callbackData := fmt.Sprintf("buy_plan:%d", p.ID)

		rows = append(rows, tgbotapi.NewInlineKeyboardRow(
			tgbotapi.NewInlineKeyboardButtonData(btnText, callbackData),
		))
	}

	// Tombol Kembali
	rows = append(rows, tgbotapi.NewInlineKeyboardRow(
		tgbotapi.NewInlineKeyboardButtonData("🔙 Kembali ke Menu Utama", "menu_start"),
	))

	return tgbotapi.NewInlineKeyboardMarkup(rows...)
}

// PaymentActionsKeyboard membuat tombol interaksi saat pembayaran QRIS berlangsung
func PaymentActionsKeyboard(trxCode string) tgbotapi.InlineKeyboardMarkup {
	return tgbotapi.NewInlineKeyboardMarkup(
		tgbotapi.NewInlineKeyboardRow(
			tgbotapi.NewInlineKeyboardButtonData("🔄 Cek Status Pembayaran", fmt.Sprintf("check_pay:%s", trxCode)),
		),
		tgbotapi.NewInlineKeyboardRow(
			tgbotapi.NewInlineKeyboardButtonData("❌ Batalkan Pembayaran", fmt.Sprintf("cancel_pay:%s", trxCode)),
		),
	)
}

// VideoNavigationKeyboard membuat tombol navigasi episode dan link mini app
func VideoNavigationKeyboard(prevEp, nextEp *database.Episode, webAppURL string) tgbotapi.InlineKeyboardMarkup {
	var navRow []tgbotapi.InlineKeyboardButton

	if prevEp != nil {
		navRow = append(navRow, tgbotapi.NewInlineKeyboardButtonData(
			fmt.Sprintf("◀️ Ep %d", prevEp.EpisodeNumber),
			fmt.Sprintf("watch:%d", prevEp.ID),
		))
	}

	if nextEp != nil {
		navRow = append(navRow, tgbotapi.NewInlineKeyboardButtonData(
			fmt.Sprintf("Ep %d ▶️", nextEp.EpisodeNumber),
			fmt.Sprintf("watch:%d", nextEp.ID),
		))
	}

	var rows [][]tgbotapi.InlineKeyboardButton
	if len(navRow) > 0 {
		rows = append(rows, navRow)
	}

	// Tombol kembali ke katalog / mini app
	if webAppURL != "" {
		rows = append(rows, tgbotapi.NewInlineKeyboardRow(
			tgbotapi.NewInlineKeyboardButtonURL("📱 Pilih Episode Lain di Mini App", webAppURL),
		))
	}

	return tgbotapi.NewInlineKeyboardMarkup(rows...)
}

// VIPLockedKeyboard tombol ajakan berlangganan jika episode terkunci
func VIPLockedKeyboard() tgbotapi.InlineKeyboardMarkup {
	return tgbotapi.NewInlineKeyboardMarkup(
		tgbotapi.NewInlineKeyboardRow(
			tgbotapi.NewInlineKeyboardButtonData("⭐ Berlangganan VIP Sekarang", "menu_vip"),
		),
		tgbotapi.NewInlineKeyboardRow(
			tgbotapi.NewInlineKeyboardButtonData("🔙 Kembali ke Menu Utama", "menu_start"),
		),
	)
}

// Helper untuk format rupiah sederhana tanpa ribet
func formatRupiah(amount float64) string {
	intVal := int64(amount)
	strVal := fmt.Sprintf("%d", intVal)
	n := len(strVal)
	if n <= 3 {
		return strVal
	}

	var result []string
	remainder := n % 3
	if remainder > 0 {
		result = append(result, strVal[:remainder])
	}
	for i := remainder; i < n; i += 3 {
		result = append(result, strVal[i:i+3])
	}
	return strings.Join(result, ".")
}
