package bot

import (
	"fmt"
	"log"

	tgbotapi "github.com/go-telegram-bot-api/telegram-bot-api/v5"
)

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

	// 4. Pengguna berhak menonton -> Kirim video dari channel privat
	// Gunakan CopyMessage agar video tampil bersih tanpa tag "Forwarded from..."
	copyMsg := tgbotapi.CopyMessageConfig{
		BaseChat:   tgbotapi.BaseChat{ChatID: telegramID},
		FromChatID: episode.ChannelID,
		MessageID:  episode.MessageID,
	}

	sentMsg, err := b.api.CopyMessage(copyMsg)
	if err != nil {
		log.Printf("[Video] Gagal CopyMessage (channel_id=%d, msg_id=%d): %v. Mencoba fallback sendVideo dengan file_id.\n",
			episode.ChannelID, episode.MessageID, err)

		// Fallback menggunakan FileID
		videoConfig := tgbotapi.NewVideo(telegramID, tgbotapi.FileID(episode.FileID))
		videoConfig.Caption = fmt.Sprintf("🎬 %s - Episode %d", episode.DramaTitle, episode.EpisodeNumber)
		_, err = b.api.Send(videoConfig)
		if err != nil {
			log.Printf("[Video] Gagal fallback sendVideo: %v\n", err)
			errorNotif := tgbotapi.NewMessage(telegramID, "⚠️ Terjadi kendala saat memuat file video dari server storage. Silakan coba kembali sesaat lagi.")
			_, _ = b.api.Send(errorNotif)
			return err
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
