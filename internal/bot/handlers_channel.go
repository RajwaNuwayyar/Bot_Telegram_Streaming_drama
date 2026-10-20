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

	// Urai judul drama, nomor episode/part, status VIP, dan kategori dari caption
	dramaTitle, epNum, isVIP, categories := parseCaption(caption)
	_ = epNum // epNum digunakan untuk metadata, bukan penentu VIP otomatis

	// Episode hanya dikunci VIP jika caption SECARA EKSPLISIT mengandung tag #vip atau [vip].
	// Tanpa tag tersebut, episode bebas ditonton semua pengguna (gratis).
	isVIP = strings.Contains(strings.ToLower(caption), "#vip") ||
		strings.Contains(strings.ToLower(caption), "[vip]") ||
		strings.Contains(strings.ToLower(caption), "(vip)")

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
		Categories:      categories,
	}

	// Simpan ke database (sesuai tugas: kirim data ke Database Engineer / tabel episodes)
	if err := b.repo.SaveEpisode(episode); err != nil {
		log.Printf("[ChannelListener] Gagal menyimpan episode ke database: %v\n", err)
		return
	}

	log.Printf("[ChannelListener] Berhasil menyimpan episode: '%s' Part %d (VIP: %v, Categories: %v)\n",
		dramaTitle, epNum, isVIP, categories)

	// Kirim notifikasi konfirmasi ke admin jika terkonfigurasi
	if b.cfg.AdminUserID != 0 {
		vipStatusStr := "🟢 Gratis"
		if isVIP {
			vipStatusStr = "👑 VIP Only"
		}
		catInfo := "-"
		if len(categories) > 0 {
			catInfo = strings.Join(categories, ", ")
		}
		notifText := fmt.Sprintf(`📥 *Penyimpanan Video Berhasil!*
━━━━━━━━━━━━━━━━━━━━
🎬 *Judul Drama:* %s
🔢 *Episode:* %d
🏷️ *Kategori:* %s
🔑 *Akses:* %s
⏱️ *Durasi:* %d detik
🆔 *Message ID:* %d
💾 Data telah tersimpan di database dan siap diakses pengguna di Mini App.`,
			dramaTitle, epNum, catInfo, vipStatusStr, video.Duration, messageID)

		notifMsg := tgbotapi.NewMessage(b.cfg.AdminUserID, notifText)
		notifMsg.ParseMode = "Markdown"
		_, _ = b.api.Send(notifMsg)
	}
}

// HandleEditedChannelPost menangani saat admin mengedit caption postingan di channel privat.
// Contoh: admin menghapus "#vip" dari caption → episode diubah menjadi gratis di database, atau menambah/mengubah tag kategori.
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
	dramaTitle, epNum, _, categories := parseCaption(caption)

	// Episode hanya dikunci VIP jika caption SECARA EKSPLISIT mengandung tag #vip atau [vip].
	isVIP := strings.Contains(strings.ToLower(caption), "#vip") ||
		strings.Contains(strings.ToLower(caption), "[vip]") ||
		strings.Contains(strings.ToLower(caption), "(vip)")

	log.Printf("[ChannelEdit] Deteksi edit caption MsgID %d: '%s' Ep %d → isVIP: %v, Cats: %v\n",
		messageID, dramaTitle, epNum, isVIP, categories)

	// Update detail episode dan judul drama di database berdasarkan message_id
	if err := b.repo.UpdateEpisodeDetailsByMessageID(messageID, dramaTitle, epNum, isVIP, caption); err != nil {
		log.Printf("[ChannelEdit] Gagal update detail episode (MsgID %d): %v, fallback ke update status VIP saja\n", messageID, err)
		if errVIP := b.repo.UpdateEpisodeVIPByMessageID(messageID, isVIP); errVIP != nil {
			log.Printf("[ChannelEdit] Gagal update status VIP episode (MsgID %d): %v\n", messageID, errVIP)
			return
		}
	} else {
		log.Printf("[ChannelEdit] Berhasil update episode MsgID %d: '%s' Ep %d (isVIP: %v)\n", messageID, dramaTitle, epNum, isVIP)
	}

	// Update relasi kategori jika ada
	if len(categories) > 0 {
		_ = b.repo.LinkDramaCategoriesByTitle(dramaTitle, categories)
	}

	// Notifikasi ke admin
	if b.cfg.AdminUserID != 0 {
		vipStatusStr := "🟢 Gratis (Diperbarui)"
		if isVIP {
			vipStatusStr = "👑 VIP Only (Diperbarui)"
		}
		catInfo := "-"
		if len(categories) > 0 {
			catInfo = strings.Join(categories, ", ")
		}
		notifText := fmt.Sprintf(`✏️ *Update Postingan Episode Terdeteksi!*
━━━━━━━━━━━━━━━━━━━━
🎬 *Judul Drama:* %s
🔢 *Episode:* %d
🏷️ *Kategori:* %s
🔑 *Status Baru:* %s
🆔 *Message ID:* %d
💾 Database dan Mini App telah diperbarui sesuai caption terbaru.`,
			dramaTitle, epNum, catInfo, vipStatusStr, messageID)

		notifMsg := tgbotapi.NewMessage(b.cfg.AdminUserID, notifText)
		notifMsg.ParseMode = "Markdown"
		_, _ = b.api.Send(notifMsg)
	}
}

// parseCaption mengurai teks caption untuk mendapatkan Judul Drama, Nomor Episode, Status VIP, dan Kategori
// Contoh: "Gacoan - Episode 01 #Romantis" -> Judul: "Gacoan", Ep: 1, VIP: false, Categories: ["Romantis"]
func parseCaption(caption string) (title string, epNum int, isVIP bool, categories []string) {
	if caption == "" {
		return "Drama Tanpa Judul", 1, false, nil
	}

	// 1. Cek tag VIP
	lower := strings.ToLower(caption)
	if strings.Contains(lower, "#vip") || strings.Contains(lower, "[vip]") || strings.Contains(lower, "(vip)") ||
		strings.Contains(lower, "status: vip") || strings.Contains(lower, "status:vip") {
		isVIP = true
	}

	// 2. Ekstrak Kategori
	catMap := make(map[string]bool)

	// A. Ekstrak dari baris Kategori: ... atau Genre: ... atau #kategori: ...
	lines := strings.Split(caption, "\n")
	for _, line := range lines {
		trimmed := strings.TrimSpace(line)
		lowLine := strings.ToLower(trimmed)
		if strings.HasPrefix(lowLine, "kategori:") || strings.HasPrefix(lowLine, "genre:") ||
			strings.HasPrefix(lowLine, "#kategori:") || strings.HasPrefix(lowLine, "#genre:") {
			parts := strings.SplitN(trimmed, ":", 2)
			if len(parts) > 1 {
				rawCats := strings.Split(parts[1], ",")
				for _, rc := range rawCats {
					c := strings.TrimSpace(strings.TrimPrefix(rc, "#"))
					if c != "" {
						catMap[c] = true
					}
				}
			}
		}
	}

	// B. Ekstrak dari seluruh hashtag (#Romantis, #Gacoan, dll.)
	reHashtag := regexp.MustCompile(`#[a-zA-Z0-9_\p{L}]+`)
	tags := reHashtag.FindAllString(caption, -1)
	reservedTags := map[string]bool{
		"#vip":       true,
		"#poster":    true,
		"#thumbnail": true,
		"#hd":        true,
		"#part":      true,
		"#eps":       true,
		"#ep":        true,
		"#episode":   true,
		"#ongoing":   true,
		"#completed": true,
		"#kategori":  true,
		"#genre":     true,
		"#trailer":   true,
	}
	for _, tag := range tags {
		lowTag := strings.ToLower(tag)
		if reservedTags[lowTag] {
			continue
		}
		cleanTag := strings.TrimPrefix(tag, "#")
		cleanTag = strings.ReplaceAll(cleanTag, "_", " ")
		cleanTag = strings.TrimSpace(cleanTag)
		if cleanTag != "" {
			catMap[cleanTag] = true
		}
	}

	for c := range catMap {
		categories = append(categories, c)
	}

	// 3. Bersihkan hashtag dan baris Kategori/Genre dari teks analisa judul
	cleanText := caption
	for _, line := range lines {
		lowLine := strings.ToLower(strings.TrimSpace(line))
		if strings.HasPrefix(lowLine, "kategori:") || strings.HasPrefix(lowLine, "genre:") ||
			strings.HasPrefix(lowLine, "#kategori:") || strings.HasPrefix(lowLine, "#genre:") {
			cleanText = strings.Replace(cleanText, line, "", 1)
		}
	}
	cleanText = regexp.MustCompile(`#[a-zA-Z0-9_\p{L}]+`).ReplaceAllString(cleanText, "")
	cleanText = strings.TrimSpace(cleanText)

	// Pola 1: <Judul> [ -–:| ] (?:Episode|Ep|Part|Eps)\.?\s*([0-9]+)
	rePattern := regexp.MustCompile(`(?i)^(.*?)(?:[-–:|]\s*|\s+)(?:episode|eps|ep|part)\.?\s*([0-9]+)`)
	matches := rePattern.FindStringSubmatch(cleanText)
	if len(matches) == 3 {
		parsedTitle := strings.Trim(matches[1], " [](){}-–:")
		num, _ := strconv.Atoi(matches[2])
		if parsedTitle != "" && num > 0 {
			return parsedTitle, num, isVIP, categories
		}
	}

	// Pola 2: Format baris:
	// Judul: ...
	// Episode: ...
	cleanLines := strings.Split(cleanText, "\n")
	var foundTitle string
	var foundEp int
	for _, line := range cleanLines {
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
		return foundTitle, foundEp, isVIP, categories
	}

	// Pola 3: Mencari angka terakhir dalam caption sebagai nomor episode
	reNumber := regexp.MustCompile(`[0-9]+`)
	numMatches := reNumber.FindAllString(cleanText, -1)
	if len(numMatches) > 0 {
		lastNum, _ := strconv.Atoi(numMatches[len(numMatches)-1])
		if lastNum > 0 {
			rawTitle := strings.Split(cleanText, "\n")[0]
			return rawTitle, lastNum, isVIP, categories
		}
	}

	// Default fallback
	firstLine := strings.Split(cleanText, "\n")[0]
	return firstLine, 1, isVIP, categories
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

	// Perintah: #edit_drama <Judul Lama> | <Judul Baru> atau #update_drama <Judul Lama> | <Judul Baru>
	if strings.HasPrefix(strings.ToLower(text), "#edit_drama") ||
		strings.HasPrefix(strings.ToLower(text), "#update_drama") ||
		strings.HasPrefix(strings.ToLower(text), "#ubah_drama") ||
		strings.HasPrefix(strings.ToLower(text), "#edit_judul") {

		var prefixLen int
		lowerText := strings.ToLower(text)
		if strings.HasPrefix(lowerText, "#edit_drama") {
			prefixLen = len("#edit_drama")
		} else if strings.HasPrefix(lowerText, "#update_drama") {
			prefixLen = len("#update_drama")
		} else if strings.HasPrefix(lowerText, "#ubah_drama") {
			prefixLen = len("#ubah_drama")
		} else {
			prefixLen = len("#edit_judul")
		}

		args := strings.TrimSpace(text[prefixLen:])
		var replyText string

		if args == "" || !strings.Contains(args, "|") {
			replyText = "⚠️ *Format salah.*\n\nGunakan: `#edit_drama <Judul Lama> | <Judul Baru>`\n\n💡 *Contoh:*\n`#edit_drama Grand Blue | Grand Blue Dreaming`\n`#edit_drama The Secret CEO | The Secret Billionaire`\n\n📝 Seluruh episode milik drama ini akan otomatis diperbarui judulnya di database dan Mini App."
		} else {
			parts := strings.SplitN(args, "|", 2)
			oldTitle := strings.TrimSpace(parts[0])
			newTitle := strings.TrimSpace(parts[1])

			if oldTitle == "" || newTitle == "" {
				replyText = "❌ Judul lama dan judul baru harus diisi.\n\nContoh: `#edit_drama Grand Blue | Grand Blue Dreaming`"
			} else {
				epUpdated, err := b.repo.UpdateDramaTitle(oldTitle, newTitle)
				if err != nil {
					log.Printf("[ChannelAdmin] Gagal update judul drama '%s' -> '%s': %v\n", oldTitle, newTitle, err)
					replyText = fmt.Sprintf("❌ Gagal memperbarui judul drama.\n\n`%v`", err)
				} else {
					log.Printf("[ChannelAdmin] Judul drama '%s' berhasil diubah menjadi '%s' (%d episode) via channel command\n", oldTitle, newTitle, epUpdated)
					replyText = fmt.Sprintf("✅ *Judul drama berhasil diperbarui!*\n\n🎬 *Judul Lama:* %s\n✨ *Judul Baru:* %s\n🔢 *Episode diperbarui:* %d episode\n💾 Perubahan telah tersimpan di database dan langsung tampil di Mini App.", oldTitle, newTitle, epUpdated)
				}
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

	// Perintah: #edit_episode <message_id> | <Judul Drama Baru>
	if strings.HasPrefix(strings.ToLower(text), "#edit_episode") {
		args := strings.TrimSpace(text[len("#edit_episode"):])
		var replyText string

		if args == "" || !strings.Contains(args, "|") {
			replyText = "⚠️ *Format salah.*\n\nGunakan: `#edit_episode <message_id> | <Judul Baru>`\n\n💡 *Contoh:*\n`#edit_episode 12345 | The Secret CEO`"
		} else {
			parts := strings.SplitN(args, "|", 2)
			msgIDStr := strings.TrimSpace(parts[0])
			newTitle := strings.TrimSpace(parts[1])

			messageID, err := strconv.Atoi(msgIDStr)
			if err != nil || messageID <= 0 || newTitle == "" {
				replyText = "❌ Message ID tidak valid atau judul baru kosong."
			} else {
				err := b.repo.UpdateEpisodeDetailsByMessageID(messageID, newTitle, 1, false, newTitle)
				if err != nil {
					log.Printf("[ChannelAdmin] Gagal edit episode MsgID %d: %v\n", messageID, err)
					replyText = fmt.Sprintf("❌ Gagal update episode: %v", err)
				} else {
					log.Printf("[ChannelAdmin] Episode MsgID %d berhasil diupdate menjadi '%s'\n", messageID, newTitle)
					replyText = fmt.Sprintf("✅ *Episode berhasil diperbarui!*\n\n🆔 *Message ID:* `%d`\n🎬 *Judul Baru:* %s\n💾 Data telah diperbarui di database.", messageID, newTitle)
				}
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

	// Perintah: #hapus_poster <Judul>
	if strings.HasPrefix(strings.ToLower(text), "#hapus_poster") || strings.HasPrefix(strings.ToLower(text), "#delete_poster") {
		prefixLen := len("#hapus_poster")
		if strings.HasPrefix(strings.ToLower(text), "#delete_poster") {
			prefixLen = len("#delete_poster")
		}
		args := strings.TrimSpace(text[prefixLen:])
		var replyText string

		if args == "" {
			replyText = "⚠️ *Format salah.*\n\nGunakan: `#hapus_poster <Judul Drama>`\n\n💡 *Contoh:*\n`#hapus_poster Grand Blue`\n`#hapus_poster Charlotte`"
		} else {
			if err := b.repo.DeleteDramaPoster(args); err != nil {
				log.Printf("[ChannelAdmin] Gagal hapus poster '%s': %v\n", args, err)
				replyText = fmt.Sprintf("❌ Gagal menghapus poster: %v", err)
			} else {
				log.Printf("[ChannelAdmin] Poster '%s' berhasil dihapus via channel command\n", args)
				replyText = fmt.Sprintf("🗑️ *Poster drama berhasil dihapus!*\n\n🎬 *Judul:* %s\n💾 Poster telah direset kembali ke tampilan default di Mini App.", args)
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

	// Perintah: #pendapatan
	if strings.HasPrefix(strings.ToLower(text), "#pendapatan") {
		var replyText string

		summary, err := b.repo.GetRevenueSummary()
		if err != nil {
			log.Printf("[ChannelAdmin] Gagal menghitung pendapatan: %v\n", err)
			replyText = fmt.Sprintf("❌ Gagal menghitung pendapatan: %v", err)
		} else {
			replyText = fmt.Sprintf(`💰 *Ringkasan Pendapatan VIP*
━━━━━━━━━━━━━━━━━━━━
📅 *Hari Ini:* Rp %.0f
🗓️ *Minggu Ini:* Rp %.0f
📆 *Bulan Ini:* Rp %.0f
📈 *Tahun Ini:* Rp %.0f`, summary["today"], summary["week"], summary["month"], summary["year"])
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

	// Perintah: #edit_kategori <Nama Lama/ID> | <Nama Baru>
	if strings.HasPrefix(strings.ToLower(text), "#edit_kategori") ||
		strings.HasPrefix(strings.ToLower(text), "#update_kategori") ||
		strings.HasPrefix(strings.ToLower(text), "#ubah_kategori") {

		var prefixLen int
		lowerText := strings.ToLower(text)
		if strings.HasPrefix(lowerText, "#edit_kategori") {
			prefixLen = len("#edit_kategori")
		} else if strings.HasPrefix(lowerText, "#update_kategori") {
			prefixLen = len("#update_kategori")
		} else {
			prefixLen = len("#ubah_kategori")
		}

		args := strings.TrimSpace(text[prefixLen:])
		var replyText string

		if args == "" || !strings.Contains(args, "|") {
			replyText = "⚠️ *Format salah.*\n\nGunakan: `#edit_kategori <Nama Lama/ID> | <Nama Baru>`\n\n💡 *Contoh:*\n`#edit_kategori Misteri | Gacoan`\n`#edit_kategori 5 | Drama Keluarga`"
		} else {
			parts := strings.SplitN(args, "|", 2)
			oldIdent := strings.TrimSpace(parts[0])
			newName := strings.TrimSpace(parts[1])

			if oldIdent == "" || newName == "" {
				replyText = "❌ Nama lama/ID dan nama baru kategori harus diisi.\n\nContoh: `#edit_kategori Misteri | Gacoan`"
			} else if err := b.repo.UpdateCategory(oldIdent, newName); err != nil {
				log.Printf("[ChannelAdmin] Gagal edit kategori '%s' -> '%s': %v\n", oldIdent, newName, err)
				replyText = fmt.Sprintf("❌ Gagal memperbarui kategori:\n`%v`", err)
			} else {
				log.Printf("[ChannelAdmin] Kategori '%s' berhasil diubah menjadi '%s' via channel command\n", oldIdent, newName)
				replyText = fmt.Sprintf("✅ *Kategori berhasil diperbarui!*\n\n🏷️ *Target:* %s\n✨ *Nama Baru:* %s\n🔗 *Slug Baru:* `%s`\n💾 Perubahan langsung tersimpan di database dan Mini App.", oldIdent, newName, database.GenerateCategorySlug(newName))
			}
		}

		deleteMsg := tgbotapi.NewDeleteMessage(post.Chat.ID, post.MessageID)
		_, _ = b.api.Request(deleteMsg)

		if b.cfg.AdminUserID != 0 {
			notif := tgbotapi.NewMessage(b.cfg.AdminUserID, replyText)
			notif.ParseMode = "Markdown"
			_, _ = b.api.Send(notif)
		}
		return true
	}

	// Perintah: #hapus_kategori <Nama/ID>
	if strings.HasPrefix(strings.ToLower(text), "#hapus_kategori") ||
		strings.HasPrefix(strings.ToLower(text), "#delete_kategori") {

		var prefixLen int
		if strings.HasPrefix(strings.ToLower(text), "#hapus_kategori") {
			prefixLen = len("#hapus_kategori")
		} else {
			prefixLen = len("#delete_kategori")
		}

		args := strings.TrimSpace(text[prefixLen:])
		var replyText string

		if args == "" {
			replyText = "⚠️ *Format salah.*\n\nGunakan: `#hapus_kategori <Nama atau ID>`\n\n💡 *Contoh:*\n`#hapus_kategori Gacoan`\n`#hapus_kategori 14`"
		} else {
			if err := b.repo.DeleteCategory(args); err != nil {
				log.Printf("[ChannelAdmin] Gagal hapus kategori '%s': %v\n", args, err)
				replyText = fmt.Sprintf("❌ Gagal menghapus kategori:\n`%v`", err)
			} else {
				log.Printf("[ChannelAdmin] Kategori '%s' berhasil dihapus via channel command\n", args)
				replyText = fmt.Sprintf("🗑️ *Kategori berhasil dihapus!*\n\n🏷️ *Kategori:* %s\n💾 Data kategori dan relasi dengan film telah dibersihkan.", args)
			}
		}

		deleteMsg := tgbotapi.NewDeleteMessage(post.Chat.ID, post.MessageID)
		_, _ = b.api.Request(deleteMsg)

		if b.cfg.AdminUserID != 0 {
			notif := tgbotapi.NewMessage(b.cfg.AdminUserID, replyText)
			notif.ParseMode = "Markdown"
			_, _ = b.api.Send(notif)
		}
		return true
	}

	// Perintah: #tambah_kategori <Nama>
	if strings.HasPrefix(strings.ToLower(text), "#tambah_kategori") ||
		strings.HasPrefix(strings.ToLower(text), "#add_kategori") {

		var prefixLen int
		if strings.HasPrefix(strings.ToLower(text), "#tambah_kategori") {
			prefixLen = len("#tambah_kategori")
		} else {
			prefixLen = len("#add_kategori")
		}

		args := strings.TrimSpace(text[prefixLen:])
		var replyText string

		if args == "" {
			replyText = "⚠️ *Format salah.*\n\nGunakan: `#tambah_kategori <Nama Kategori>`\n\n💡 *Contoh:*\n`#tambah_kategori Romantis`\n`#tambah_kategori Thriller Psikologis`"
		} else {
			cat, err := b.repo.AddCategory(args)
			if err != nil {
				log.Printf("[ChannelAdmin] Gagal tambah kategori '%s': %v\n", args, err)
				replyText = fmt.Sprintf("❌ Gagal menambah kategori:\n`%v`", err)
			} else {
				log.Printf("[ChannelAdmin] Kategori '%s' (ID %d) berhasil ditambahkan\n", cat.Name, cat.ID)
				replyText = fmt.Sprintf("✅ *Kategori baru berhasil ditambahkan!*\n\n🏷️ *Nama:* %s\n🔗 *Slug:* `%s`\n🆔 *ID:* %d\n💾 Siap digunakan pada caption video (misal: `#%s`).", cat.Name, cat.Slug, cat.ID, strings.ReplaceAll(cat.Name, " ", "_"))
			}
		}

		deleteMsg := tgbotapi.NewDeleteMessage(post.Chat.ID, post.MessageID)
		_, _ = b.api.Request(deleteMsg)

		if b.cfg.AdminUserID != 0 {
			notif := tgbotapi.NewMessage(b.cfg.AdminUserID, replyText)
			notif.ParseMode = "Markdown"
			_, _ = b.api.Send(notif)
		}
		return true
	}

	// Perintah: #list_kategori atau #daftar_kategori
	if strings.HasPrefix(strings.ToLower(text), "#list_kategori") ||
		strings.HasPrefix(strings.ToLower(text), "#daftar_kategori") {

		var replyText string
		cats, err := b.repo.GetCategories()
		if err != nil {
			log.Printf("[ChannelAdmin] Gagal mengambil daftar kategori: %v\n", err)
			replyText = fmt.Sprintf("❌ Gagal mengambil daftar kategori: %v", err)
		} else if len(cats) == 0 {
			replyText = "📋 Belum ada kategori di database.\n\nGunakan `#tambah_kategori <Nama>` untuk membuat baru."
		} else {
			var sb strings.Builder
			sb.WriteString(fmt.Sprintf("📋 *Daftar Kategori Drama (%d Kategori)*\n━━━━━━━━━━━━━━━━━━━━\n", len(cats)))
			for i, c := range cats {
				sb.WriteString(fmt.Sprintf("%d. *%s* (ID: `%d`, Film: %d)\n", i+1, c.Name, c.ID, c.DramaCount))
			}
			sb.WriteString("\n💡 *Tips Kelola Kategori:*\n• Ubah: `#edit_kategori <Lama/ID> | <Baru>`\n• Hapus: `#hapus_kategori <Nama/ID>`")
			replyText = sb.String()
		}

		deleteMsg := tgbotapi.NewDeleteMessage(post.Chat.ID, post.MessageID)
		_, _ = b.api.Request(deleteMsg)

		if b.cfg.AdminUserID != 0 {
			notif := tgbotapi.NewMessage(b.cfg.AdminUserID, replyText)
			notif.ParseMode = "Markdown"
			_, _ = b.api.Send(notif)
		}
		return true
	}

	// Perintah: #set_kategori <Judul Drama> | <Kategori 1>, <Kategori 2>
	if strings.HasPrefix(strings.ToLower(text), "#set_kategori") {
		args := strings.TrimSpace(text[len("#set_kategori"):])
		var replyText string

		if args == "" || !strings.Contains(args, "|") {
			replyText = "⚠️ *Format salah.*\n\nGunakan: `#set_kategori <Judul Drama> | <Kategori 1>, <Kategori 2>`\n\n💡 *Contoh:*\n`#set_kategori Gacoan | Romantis, Komedi`"
		} else {
			parts := strings.SplitN(args, "|", 2)
			dramaTitle := strings.TrimSpace(parts[0])
			rawCats := strings.Split(parts[1], ",")
			var cleanCats []string
			for _, rc := range rawCats {
				c := strings.TrimSpace(strings.TrimPrefix(rc, "#"))
				if c != "" {
					cleanCats = append(cleanCats, c)
				}
			}

			if dramaTitle == "" || len(cleanCats) == 0 {
				replyText = "❌ Judul drama dan minimal 1 kategori harus diisi."
			} else if err := b.repo.LinkDramaCategoriesByTitle(dramaTitle, cleanCats); err != nil {
				log.Printf("[ChannelAdmin] Gagal set kategori drama '%s': %v\n", dramaTitle, err)
				replyText = fmt.Sprintf("❌ Gagal menghubungkan kategori:\n`%v`", err)
			} else {
				log.Printf("[ChannelAdmin] Berhasil menghubungkan kategori %v ke drama '%s'\n", cleanCats, dramaTitle)
				replyText = fmt.Sprintf("✅ *Kategori drama berhasil diperbarui!*\n\n🎬 *Judul Drama:* %s\n🏷️ *Kategori:* %s\n💾 Data langsung sinkron di Mini App.", dramaTitle, strings.Join(cleanCats, ", "))
			}
		}

		deleteMsg := tgbotapi.NewDeleteMessage(post.Chat.ID, post.MessageID)
		_, _ = b.api.Request(deleteMsg)

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
