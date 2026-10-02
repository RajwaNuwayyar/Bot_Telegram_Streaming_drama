package main

import (
	"log"
	"os"
	"os/signal"
	"syscall"

	"dramabot/internal/bot"
	"dramabot/internal/config"
	"dramabot/internal/database"
	"dramabot/internal/payment"
	"dramabot/internal/server"
)

func main() {
	log.Println("==================================================")
	log.Println("🎬 DRAMABOT TELEGRAM - BACKEND & BOT RUNTIME 🚀")
	log.Println("==================================================")

	// 1. Muat Konfigurasi
	cfg := config.LoadConfig()

	// 2. Inisialisasi Database MySQL (dengan 7 VIP plans seed)
	repo, err := database.NewMySQLRepo(cfg.DatabaseDSN)
	if err != nil {
		log.Fatalf("❌ Gagal inisialisasi database: %v\n", err)
	}
	log.Printf("✅ Database MySQL berhasil dimuat\n")

	// 3. Inisialisasi Layanan Payment Coordinator (QRIS)
	payCoordinator := payment.NewPaymentCoordinator("", "")
	log.Println("✅ Payment Coordinator service siap (QRIS Generator & Webhook)")

	// 4. Periksa BOT_TOKEN
	if cfg.BotToken == "" || cfg.BotToken == "ISI_DENGAN_TOKEN_BOTFATHER_ANDA" {
		log.Println("⚠️  PERINGATAN: BOT_TOKEN belum diatur pada file .env!")
		log.Println("👉 Buka file .env dan masukkan BOT_TOKEN yang Anda dapatkan dari @BotFather.")
		log.Println("👉 Aplikasi tetap menyalakan HTTP Server untuk pengujian endpoint...")
	}

	var teleBot *bot.Bot
	if cfg.BotToken != "" && cfg.BotToken != "ISI_DENGAN_TOKEN_BOTFATHER_ANDA" {
		teleBot, err = bot.NewBot(cfg, repo, payCoordinator)
		if err != nil {
			log.Fatalf("❌ Gagal inisialisasi Telegram Bot: %v\n", err)
		}
	}

	// 5. Jalankan HTTP Server (untuk Webhook QRIS & Integrasi Mini App)
	httpServer := server.NewServer(cfg, teleBot, repo)
	go func() {
		if err := httpServer.Start(); err != nil {
			log.Fatalf("❌ HTTP Server error: %v\n", err)
		}
	}()

	// 6. Jalankan Telegram Bot jika token tersedia
	if teleBot != nil {
		go teleBot.Start()
	}

	// Menunggu sinyal shutdown anggun (graceful shutdown)
	quit := make(chan os.Signal, 1)
	signal.Notify(quit, syscall.SIGINT, syscall.SIGTERM)
	<-quit

	log.Println("🛑 Menghentikan DramaBot dengan aman...")
}
