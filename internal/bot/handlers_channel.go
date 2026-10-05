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
	if post == nil {
		return
	}

	channelID := post.Chat.ID
	messageID := post.MessageID
	caption := strings.TrimSpace(post.Caption)

	// 1. Tangani upload POSTER drama (postingan foto/gambar dengan tag #poster)
	if isPosterUpload(post, caption) {
		b.handlePosterPost(post)
		return
	}

	// 2. Jika bukan video, abaikan
	if post.Video == nil {
		return
	}

	video := post.Video

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

	var thumbFileID string
	if video.Thumbnail != nil {
		thumbFileID = video.Thumbnail.FileID
	}

	episode := &database.Episode{
		DramaTitle:      dramaTitle,
		EpisodeNumber:   epNum,
		Title:           fmt.Sprintf("%s - Episode %d", dramaTitle, epNum),
		ChannelID:       channelID,
		MessageID:       messageID,
		FileID:          video.FileID,
		Duration:        video.Duration,
		IsVIP:           isVIP,
		Caption:         caption,
		ThumbnailFileID: thumbFileID,
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
	if post == nil {
		return
	}

	caption := strings.TrimSpace(post.Caption)

	// Tangani jika yang diedit adalah postingan poster dengan tag #poster
	if isPosterUpload(post, caption) {
		b.handlePosterPost(post)
		return
	}

	if post.Video == nil {
		return
	}

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

// isPosterUpload memeriksa apakah pesan merupakan postingan poster foto/gambar dengan tag #poster
func isPosterUpload(post *tgbotapi.Message, caption string) bool {
	if post == nil {
		return false
	}
	lower := strings.ToLower(caption)
	hasPosterTag := strings.Contains(lower, "#poster") ||
		strings.Contains(lower, "[poster]") ||
		strings.Contains(lower, "(poster)") ||
		strings.Contains(lower, "tag: poster") ||
		strings.Contains(lower, "tag:poster") ||
		strings.Contains(lower, "#thumbnail")

	if !hasPosterTag {
		return false
	}

	return len(post.Photo) > 0 || (post.Document != nil && strings.HasPrefix(post.Document.MimeType, "image/"))
}

// handlePosterPost menangani penyimpanan file_id poster drama ke database
func (b *Bot) handlePosterPost(post *tgbotapi.Message) {
	caption := strings.TrimSpace(post.Caption)
	var fileID string

	if len(post.Photo) > 0 {
		// Ambil resolusi gambar tertinggi (elemen paling akhir di PhotoSize)
		fileID = post.Photo[len(post.Photo)-1].FileID
	} else if post.Document != nil {
		fileID = post.Document.FileID
	}

	if fileID == "" {
		return
	}

	dramaTitle := parsePosterTitle(caption)
	log.Printf("[ChannelListener] Poster drama terdeteksi (MsgID: %d, Drama: '%s', FileID: %s)\n",
		post.MessageID, dramaTitle, fileID)

	if err := b.repo.UpdateDramaPoster(dramaTitle, fileID); err != nil {
		log.Printf("[ChannelListener] Gagal update poster drama '%s': %v\n", dramaTitle, err)
		return
	}

	log.Printf("[ChannelListener] Berhasil update poster drama '%s' ke database (FileID: %s)\n", dramaTitle, fileID)

	// Kirim konfirmasi ke admin
	if b.cfg.AdminUserID != 0 {
		notifText := fmt.Sprintf(`🖼️ *Poster Drama Berhasil Diperbarui!*
━━━━━━━━━━━━━━━━━━━━
🎬 *Judul Drama:* %s
🏷️ *Tag:* #poster
🆔 *Message ID:* %d
🔑 *File ID:* `+"`%s`"+`
💾 Thumbnail drama telah tersimpan di database dan otomatis tampil di Mini App.`,
			dramaTitle, post.MessageID, fileID)

		notifMsg := tgbotapi.NewMessage(b.cfg.AdminUserID, notifText)
		notifMsg.ParseMode = "Markdown"
		_, _ = b.api.Send(notifMsg)
	}
}

// handleChannelAdminCommand mendeteksi perintah teks admin yang dikirim langsung di channel privat.
// Perintah yang didukung (ditulis sebagai teks biasa di channel):
//   #hapus_episode <message_id>   → Hapus episode dari database
//   #set_poster <Judul> | <file_id> → Update poster drama
//
// Fungsi ini return true jika pesan adalah perintah admin dan sudah diproses,
// sehingga caller bisa skip HandleChannelPost normal.
func (b *Bot) handleChannelAdminCommand(post *tgbotapi.Message) bool {
	if post == nil || post.Text == "" {
		return false
	}

	text := strings.TrimSpace(post.Text)

	// Perintah: #hapus_episode <message_id>
	if strings.HasPrefix(strings.ToLower(text), "#hapus_episode") {
		parts := strings.Fields(text)
		var replyText string

		if len(parts) < 2 {
			replyText = "⚠️ *Format salah.*\n\nGunakan: `#hapus_episode <message_id>`\n\n💡 *Cara dapat Message ID:*\nKlik kanan/tahan postingan di channel → *Salin Tautan* → angka di akhir URL."
		} else {
			messageID, err := strconv.Atoi(parts[1])
			if err != nil || messageID <= 0 {
				replyText = "❌ Message ID tidak valid. Masukkan angka yang benar."
			} else if err := b.repo.DeleteEpisodeByMessageID(messageID); err != nil {
				log.Printf("[ChannelAdmin] Gagal hapus episode MsgID %d: %v\n", messageID, err)
				replyText = fmt.Sprintf("❌ Gagal hapus episode.\n\nEpisode dengan Message ID `%d` tidak ditemukan di database.", messageID)
			} else {
				log.Printf("[ChannelAdmin] Episode MsgID %d berhasil dihapus via channel command\n", messageID)
				replyText = fmt.Sprintf("✅ *Episode berhasil dihapus!*\n\n🆔 Message ID: `%d`\n💾 Data telah dihapus dari database.", messageID)
			}
		}

		// Hapus pesan perintah dari channel agar channel tetap bersih
		deleteMsg := tgbotapi.NewDeleteMessage(post.Chat.ID, post.MessageID)
		_, _ = b.api.Request(deleteMsg)

		// Kirim hasil ke admin via DM
		if b.cfg.AdminUserID != 0 {
			notif := tgbotapi.NewMessage(b.cfg.AdminUserID, replyText)
			notif.ParseMode = "Markdown"
			_, _ = b.api.Send(notif)
		}
		return true
	}

	// Perintah: #hapus_drama <Judul Drama>
	if strings.HasPrefix(strings.ToLower(text), "#hapus_drama") {
		args := strings.TrimSpace(text[len("#hapus_drama"):])
		var replyText string

		if args == "" {
			replyText = "⚠️ *Format salah.*\n\nGunakan: `#hapus_drama <Judul Drama>`\n\n💡 *Contoh:*\n`#hapus_drama Grand Blue`\n`#hapus_drama The Secret CEO`\n\n⚠️ Ini akan menghapus *seluruh drama* beserta semua episodenya secara permanen!"
		} else {
			epDeleted, err := b.repo.DeleteDramaByTitle(args)
			if err != nil {
				log.Printf("[ChannelAdmin] Gagal hapus drama '%s': %v\n", args, err)
				replyText = fmt.Sprintf("❌ Gagal menghapus drama.\n\n`%v`", err)
			} else {
				log.Printf("[ChannelAdmin] Drama '%s' berhasil dihapus (%d episode) via channel command\n", args, epDeleted)
				replyText = fmt.Sprintf("✅ *Drama berhasil dihapus secara permanen!*\n\n🎬 *Judul:* %s\n🗑️ *Episode dihapus:* %d episode\n💾 Drama tidak akan muncul lagi di Mini App.", args, epDeleted)
			}
		}

		// Hapus pesan perintah dari channel agar channel tetap bersih
		deleteMsg := tgbotapi.NewDeleteMessage(post.Chat.ID, post.MessageID)
		_, _ = b.api.Request(deleteMsg)

		// Kirim hasil ke admin via DM
		if b.cfg.AdminUserID != 0 {
			notif := tgbotapi.NewMessage(b.cfg.AdminUserID, replyText)
			notif.ParseMode = "Markdown"
			_, _ = b.api.Send(notif)
		}
		return true
	}

	// Perintah: #set_poster <Judul> | <file_id>
	if strings.HasPrefix(strings.ToLower(text), "#set_poster") {
		args := strings.TrimSpace(text[len("#set_poster"):])
		var replyText string

		if args == "" {
			replyText = "⚠️ *Format salah.*\n\nGunakan: `#set_poster <Judul Drama> | <file_id>`\n\n💡 Atau upload foto langsung ke channel/bot dengan caption `#poster`."
		} else {
			var title, fileID string
			if strings.Contains(args, "|") {
				p := strings.SplitN(args, "|", 2)
				title = strings.TrimSpace(p[0])
				fileID = strings.TrimSpace(p[1])
			}
			if title == "" || fileID == "" {
				replyText = "❌ Judul drama dan File ID harus diisi.\n\nContoh: `#set_poster Grand Blue | AgACAgUAAxkBA...`"
			} else if err := b.repo.UpdateDramaPoster(title, fileID); err != nil {
				log.Printf("[ChannelAdmin] Gagal set poster '%s': %v\n", title, err)
				replyText = fmt.Sprintf("❌ Gagal update poster: %v", err)
			} else {
				log.Printf("[ChannelAdmin] Poster '%s' berhasil diupdate via channel command\n", title)
				replyText = fmt.Sprintf("✅ *Poster drama berhasil diperbarui!*\n\n🎬 *Judul:* %s\n🔑 *File ID:* `%s`", title, fileID)
			}
		}

		// Hapus pesan perintah dari channel agar channel tetap bersih
		deleteMsg := tgbotapi.NewDeleteMessage(post.Chat.ID, post.MessageID)
		_, _ = b.api.Request(deleteMsg)

		// Kirim hasil ke admin via DM
		if b.cfg.AdminUserID != 0 {
			notif := tgbotapi.NewMessage(b.cfg.AdminUserID, replyText)
			notif.ParseMode = "Markdown"
			_, _ = b.api.Send(notif)
		}
		return true
	}

	return false
}

// parsePosterTitle mengurai judul drama dari caption poster yang memiliki tag #poster
// Contoh: "GrandBlue #poster" -> "GrandBlue"
// "Charlotte #poster" -> "Charlotte"
// "Judul: Grand Blue #poster" -> "Grand Blue"
// "[Grand Blue] #poster" -> "Grand Blue"
func parsePosterTitle(caption string) string {
	if caption == "" {
		return "Drama Tanpa Judul"
	}

	// Bersihkan hashtag dan tag poster
	reTag := regexp.MustCompile(`(?i)#(?:poster|thumbnail|tag_poster|tag)\b|\[poster\]|\(poster\)`)
	cleanText := reTag.ReplaceAllString(caption, "")

	// Periksa baris untuk pola "Judul: ..." atau "Title: ..."
	lines := strings.Split(cleanText, "\n")
	rePrefix := regexp.MustCompile(`(?i)^(?:judul|title|poster|nama|drama)\s*[:=-]\s*`)
	reSuffix := regexp.MustCompile(`(?i)\s*[-–:]\s*(?:poster|thumbnail)\s*$`)

	for _, line := range lines {
		trimmed := strings.TrimSpace(line)
		if trimmed != "" {
			trimmed = rePrefix.ReplaceAllString(trimmed, "")
			trimmed = reSuffix.ReplaceAllString(trimmed, "")
			trimmed = strings.Trim(trimmed, " -–:[]()")
			if trimmed != "" {
				return trimmed
			}
		}
	}

	firstLine := strings.TrimSpace(lines[0])
	firstLine = reSuffix.ReplaceAllString(firstLine, "")
	firstLine = strings.Trim(firstLine, " -–:[]()")
	if firstLine != "" {
		return firstLine
	}
	return "Drama Tanpa Judul"
}
