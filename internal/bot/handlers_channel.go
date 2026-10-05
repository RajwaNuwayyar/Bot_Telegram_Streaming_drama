package bot

import (
	"fmt"
	"log"
	"regexp"
	"strconv"
	"strings"

	tgbotapi "github.com/go-telegram-bot-api/telegram-bot-api/v5"
	"dramabot/internal/database"
)

// HandleChannelPost memproses postingan video baru dari channel privat
// Mengambil video, mengurai caption judul & part/episode, lalu menyimpannya ke database
func (b *Bot) HandleChannelPost(post *tgbotapi.Message) {
	if post == nil || post.Video == nil {
		return
	}

	channelID := post.Chat.ID
	messageID := post.MessageID
	video := post.Video
	caption := strings.TrimSpace(post.Caption)

	log.Printf("[ChannelListener] Video baru terdeteksi di Channel %d (MsgID: %d, FileID: %s)\n",
		channelID, messageID, video.FileID)

	// Urai judul drama, nomor episode/part, dan status VIP dari caption
	dramaTitle, epNum, isVIP := parseCaption(caption)

	// Default penentuan VIP jika tidak ditulis spesifik di caption:
	// Episode 1 dan 2 gratis untuk menarik penonton, episode 3 ke atas VIP
	if !strings.Contains(strings.ToLower(caption), "#free") && !strings.Contains(strings.ToLower(caption), "gratis") {
		if strings.Contains(strings.ToLower(caption), "#vip") || strings.Contains(strings.ToLower(caption), "[vip]") || epNum > 2 {
			isVIP = true
		}
	}

	episode := &database.Episode{
		DramaTitle:    dramaTitle,
		EpisodeNumber: epNum,
		Title:         fmt.Sprintf("%s - Episode %d", dramaTitle, epNum),
		ChannelID:     channelID,
		MessageID:     messageID,
		FileID:        video.FileID,
		Duration:      video.Duration,
		IsVIP:         isVIP,
		Caption:       caption,
	}

	// Simpan ke database (sesuai tugas: kirim data ke Database Engineer / tabel episodes)
	if err := b.repo.SaveEpisode(episode); err != nil {
		log.Printf("[ChannelListener] Gagal menyimpan episode ke database: %v\n", err)
		return
	}

	log.Printf("[ChannelListener] Berhasil menyimpan episode: '%s' Part %d (VIP: %v)\n",
		dramaTitle, epNum, isVIP)

	// Kirim notifikasi konfirmasi ke admin jika terkonfigurasi
	if b.cfg.AdminUserID != 0 {
		vipStatusStr := "🟢 Gratis"
		if isVIP {
			vipStatusStr = "👑 VIP Only"
		}
		notifText := fmt.Sprintf(`📥 *Penyimpanan Video Berhasil!*
━━━━━━━━━━━━━━━━━━━━
🎬 *Judul Drama:* %s
🔢 *Episode:* %d
🏷️ *Akses:* %s
⏱️ *Durasi:* %d detik
🆔 *Message ID:* %d
💾 Data telah tersimpan di database dan siap diakses pengguna di Mini App.`,
			dramaTitle, epNum, vipStatusStr, video.Duration, messageID)

		notifMsg := tgbotapi.NewMessage(b.cfg.AdminUserID, notifText)
		notifMsg.ParseMode = "Markdown"
		_, _ = b.api.Send(notifMsg)
	}
}

// HandleEditedChannelPost menangani saat admin mengedit caption postingan di channel privat.
// Contoh: admin menghapus "#vip" dari caption → episode diubah menjadi gratis di database.
func (b *Bot) HandleEditedChannelPost(post *tgbotapi.Message) {
	if post == nil || post.Video == nil {
		return
	}

	caption := strings.TrimSpace(post.Caption)
	messageID := post.MessageID

	// Urai ulang caption yang sudah diedit
	dramaTitle, epNum, isVIP := parseCaption(caption)

	// Terapkan aturan default VIP yang sama seperti saat upload
	if !strings.Contains(strings.ToLower(caption), "#free") && !strings.Contains(strings.ToLower(caption), "gratis") {
		if strings.Contains(strings.ToLower(caption), "#vip") || strings.Contains(strings.ToLower(caption), "[vip]") || epNum > 2 {
			isVIP = true
		}
	}

	// Paksa gratis jika caption secara eksplisit mengandung #free (override aturan episode > 2)
	if strings.Contains(strings.ToLower(caption), "#free") || strings.Contains(strings.ToLower(caption), "gratis") {
		isVIP = false
	}

	log.Printf("[ChannelEdit] Deteksi edit caption MsgID %d: '%s' Ep %d → isVIP: %v\n",
		messageID, dramaTitle, epNum, isVIP)

	// Update status VIP episode di database berdasarkan message_id
	if err := b.repo.UpdateEpisodeVIPByMessageID(messageID, isVIP); err != nil {
		log.Printf("[ChannelEdit] Gagal update status VIP episode (MsgID %d): %v\n", messageID, err)
		return
	}

	log.Printf("[ChannelEdit] Berhasil update episode MsgID %d → isVIP: %v\n", messageID, isVIP)

	// Notifikasi ke admin
	if b.cfg.AdminUserID != 0 {
		vipStatusStr := "🟢 Gratis (Diperbarui)"
		if isVIP {
			vipStatusStr = "👑 VIP Only (Diperbarui)"
		}
		notifText := fmt.Sprintf(`✏️ *Update Caption Episode Terdeteksi!*
━━━━━━━━━━━━━━━━━━━━
🎬 *Judul:* %s
🔢 *Episode:* %d
🏷️ *Status Baru:* %s
🆔 *Message ID:* %d
💾 Database telah diperbarui sesuai caption terbaru.`,
			dramaTitle, epNum, vipStatusStr, messageID)

		notifMsg := tgbotapi.NewMessage(b.cfg.AdminUserID, notifText)
		notifMsg.ParseMode = "Markdown"
		_, _ = b.api.Send(notifMsg)
	}
}

// parseCaption mengurai teks caption untuk mendapatkan Judul Drama dan Nomor Episode
// Contoh pola yang didukung:
// 1. "The Secret CEO - Episode 01"
// 2. "[My Husband] Part 3 #vip"
// 3. "Judul Drama Ep 12"
// 4. "Dendam Sang Istri - Part 4"
func parseCaption(caption string) (title string, epNum int, isVIP bool) {
	if caption == "" {
		return "Drama Tanpa Judul", 1, false
	}

	// Cek tag VIP
	lower := strings.ToLower(caption)
	if strings.Contains(lower, "#vip") || strings.Contains(lower, "[vip]") || strings.Contains(lower, "(vip)") ||
		strings.Contains(lower, "status: vip") || strings.Contains(lower, "status:vip") {
		isVIP = true
	}

	// Bersihkan hashtag dari teks analisa judul
	cleanText := regexp.MustCompile(`#[a-zA-Z0-9_]+`).ReplaceAllString(caption, "")
	cleanText = strings.TrimSpace(cleanText)

	// Pola 1: <Judul> [ -–:| ] (?:Episode|Ep|Part|Eps)\.?\s*([0-9]+)
	rePattern := regexp.MustCompile(`(?i)^(.*?)(?:[-–:|]\s*|\s+)(?:episode|eps|ep|part)\.?\s*([0-9]+)`)
	matches := rePattern.FindStringSubmatch(cleanText)
	if len(matches) == 3 {
		parsedTitle := strings.Trim(matches[1], " [](){}-–:")
		num, _ := strconv.Atoi(matches[2])
		if parsedTitle != "" && num > 0 {
			return parsedTitle, num, isVIP
		}
	}

	// Pola 2: Format baris:
	// Judul: ...
	// Episode: ...
	lines := strings.Split(cleanText, "\n")
	var foundTitle string
	var foundEp int
	for _, line := range lines {
		trimmed := strings.TrimSpace(line)
		if strings.HasPrefix(strings.ToLower(trimmed), "judul:") {
			foundTitle = strings.TrimSpace(trimmed[6:])
		} else if strings.HasPrefix(strings.ToLower(trimmed), "episode:") || strings.HasPrefix(strings.ToLower(trimmed), "part:") {
			parts := strings.Split(trimmed, ":")
			if len(parts) > 1 {
				num, _ := strconv.Atoi(strings.TrimSpace(parts[1]))
				foundEp = num
			}
		}
	}

	if foundTitle != "" && foundEp > 0 {
		return foundTitle, foundEp, isVIP
	}

	// Pola 3: Mencari angka terakhir dalam caption sebagai nomor episode
	reNumber := regexp.MustCompile(`[0-9]+`)
	numMatches := reNumber.FindAllString(cleanText, -1)
	if len(numMatches) > 0 {
		lastNum, _ := strconv.Atoi(numMatches[len(numMatches)-1])
		if lastNum > 0 {
			rawTitle := strings.Split(cleanText, "\n")[0]
			return rawTitle, lastNum, isVIP
		}
	}

	// Default fallback
	firstLine := strings.Split(cleanText, "\n")[0]
	return firstLine, 1, isVIP
}
