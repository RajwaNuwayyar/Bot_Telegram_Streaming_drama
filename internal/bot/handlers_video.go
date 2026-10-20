package bot

import (
	"encoding/json"
	"fmt"
	"log"

	tgbotapi "github.com/go-telegram-bot-api/telegram-bot-api/v5"
)

// sendProtectedCopyMessage mengirim video dengan protect_content=true menggunakan raw API call.
// Caption disesuaikan agar hanya menampilkan Judul Film dan Episode (bersih dari hashtag admin/kategori/vip).
func (b *Bot) sendProtectedCopyMessage(toChatID int64, fromChatID int64, messageID int, caption string) (tgbotapi.Message, error) {
	params := tgbotapi.Params{}
	params.AddNonZero64("chat_id", toChatID)
	params.AddNonZero64("from_chat_id", fromChatID)
	params.AddNonZero("message_id", messageID)
	params["protect_content"] = "true" // 🔒 Mencegah user mem-forward atau menyimpan/download video
	if caption != "" {
		params["caption"] = caption
	}

	resp, err := b.api.MakeRequest("copyMessage", params)
	if err != nil {
		return tgbotapi.Message{}, err
	}

	var msg tgbotapi.Message
	err = json.Unmarshal(resp.Result, &msg)
	return msg, err
}

// SendEpisode memproses pengecekan akses hak tayang dan pengiriman video dari channel privat
func (b *Bot) SendEpisode(telegramID int64, episodeID int64) error {
	// 1. Ambil data episode dari database
	episode, err := b.repo.GetEpisodeByID(episodeID)
	if err != nil {
		log.Printf("[Video] Error mengambil episode id %d: %v\n", episodeID, err)
		return err
	}
	if episode == nil {
		msg := tgbotapi.NewMessage(telegramID, "⚠️ Episode yang Anda cari belum tersedia atau sedang diproses.")
		_, _ = b.api.Send(msg)
		return fmt.Errorf("episode tidak ditemukan")
	}

	// 2. Ambil data profil user untuk cek status VIP
	user, err := b.repo.GetUserByTelegramID(telegramID)
	if err != nil {
		log.Printf("[Video] Error mengambil user %d: %v\n", telegramID, err)
	}

	// 3. Pengecekan Hak Akses (Episode Gratis vs VIP)
	// Sesuai aturan: jika episode membutuhkan VIP dan user bukan VIP aktif, tolak akses dan tampilkan tombol beli VIP
	isUserVIP := (user != nil && user.IsVIPActive())
	if episode.IsVIP && !isUserVIP {
		lockMsg := tgbotapi.NewMessage(telegramID, VideoLockedMessage(episode.DramaTitle, episode.EpisodeNumber))
		lockMsg.ParseMode = "Markdown"
		lockMsg.ReplyMarkup = VIPLockedKeyboard()
		_, err := b.api.Send(lockMsg)
		return err
	}

	// 4. Pengguna berhak menonton -> Kirim video dari channel privat dengan protect_content=true
	// 🔒 protect_content mencegah user mem-forward atau menyimpan/download video
	// Caption dibersihkan agar hanya menampilkan Judul Film dan Episode (sesuai permintaan user)
	cleanCaption := fmt.Sprintf("🎬 %s - Episode %d", episode.DramaTitle, episode.EpisodeNumber)
	sentMsg, err := b.sendProtectedCopyMessage(telegramID, episode.ChannelID, episode.MessageID, cleanCaption)
	if err != nil {
		log.Printf("[Video] Gagal CopyMessage (channel_id=%d, msg_id=%d): %v. Mencoba fallback sendVideo dengan file_id.\n",
			episode.ChannelID, episode.MessageID, err)

		// Fallback menggunakan FileID via raw API call dengan protect_content=true
		params := tgbotapi.Params{}
		params.AddNonZero64("chat_id", telegramID)
		params["video"] = episode.FileID
		params["caption"] = fmt.Sprintf("🎬 %s - Episode %d", episode.DramaTitle, episode.EpisodeNumber)
		params["protect_content"] = "true" // 🔒 Mencegah user mem-forward atau menyimpan/download video

		_, fallbackErr := b.api.MakeRequest("sendVideo", params)
		if fallbackErr != nil {
			log.Printf("[Video] Gagal fallback sendVideo: %v\n", fallbackErr)
			errorNotif := tgbotapi.NewMessage(telegramID, "⚠️ Terjadi kendala saat memuat file video dari server storage. Silakan coba kembali sesaat lagi.")
			_, _ = b.api.Send(errorNotif)
			return fallbackErr
		}
	} else {
		log.Printf("[Video] Berhasil mengirim video episode %d '%s' ke user %d (msg_id: %d)\n",
			episode.EpisodeNumber, episode.DramaTitle, telegramID, sentMsg.MessageID)
	}

	// 5. Kirim tombol navigasi episode (Sebelumnya, Selanjutnya, Mini App)
	prevEp, nextEp, _ := b.repo.GetAdjacentEpisodes(episode.DramaTitle, episode.EpisodeNumber)
	navText := fmt.Sprintf("🍿 *Sedang Menonton:* %s (Ep %d)", episode.DramaTitle, episode.EpisodeNumber)
	navMsg := tgbotapi.NewMessage(telegramID, navText)
	navMsg.ParseMode = "Markdown"
	navMsg.ReplyMarkup = VideoNavigationKeyboard(prevEp, nextEp, b.cfg.WebAppURL)

	_, _ = b.api.Send(navMsg)
	return nil
}


