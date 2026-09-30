package server

import (
	"encoding/json"
	"fmt"
	"log"
	"net/http"

	"dramabot/internal/bot"
	"dramabot/internal/config"
	"dramabot/internal/database"
)

type Server struct {
	cfg  *config.Config
	bot  *bot.Bot
	repo database.Repository
}

func NewServer(cfg *config.Config, b *bot.Bot, repo database.Repository) *Server {
	return &Server{
		cfg:  cfg,
		bot:  b,
		repo: repo,
	}
}

// Start menjalankan HTTP server untuk Webhook Payment & Integrasi Mini App
func (s *Server) Start() error {
	mux := http.NewServeMux()

	// 1. Webhook Pembayaran dari Payment Coordinator
	mux.HandleFunc("/api/payment/webhook", s.handlePaymentWebhook)

	// 2. Endpoint bagi Mini App Developer untuk memicu pengiriman video ke chat Telegram
	mux.HandleFunc("/api/send-episode", s.handleSendEpisodeAPI)

	// 3. Endpoint untuk membaca daftar paket VIP bagi Mini App
	mux.HandleFunc("/api/plans", s.handleGetPlans)

	// 4. Health check
	mux.HandleFunc("/api/health", func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Content-Type", "application/json")
		_ = json.NewEncoder(w).Encode(map[string]string{"status": "ok", "service": "dramabot"})
	})

	addr := fmt.Sprintf(":%s", s.cfg.ServerPort)
	log.Printf("[Server] HTTP API & Webhook mendengarkan di %s\n", addr)
	return http.ListenAndServe(addr, mux)
}

type PaymentWebhookPayload struct {
	TransactionCode string `json:"transaction_code"`
	Status          string `json:"status"` // "PAID", "FAILED", dll
}

// handlePaymentWebhook menerima callback dari Payment Coordinator setelah user membayar QRIS
func (s *Server) handlePaymentWebhook(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		http.Error(w, "Metode tidak diizinkan", http.StatusMethodNotAllowed)
		return
	}

	var payload PaymentWebhookPayload
	if err := json.NewDecoder(r.Body).Decode(&payload); err != nil {
		http.Error(w, "Payload JSON tidak valid", http.StatusBadRequest)
		return
	}

	log.Printf("[Webhook] Callback pembayaran diterima: Trx=%s, Status=%s\n", payload.TransactionCode, payload.Status)

	if payload.Status == "PAID" {
		tx, err := s.repo.MarkTransactionPaid(payload.TransactionCode)
		if err != nil {
			log.Printf("[Webhook] Gagal memproses transaksi %s: %v\n", payload.TransactionCode, err)
			http.Error(w, "Gagal memproses transaksi", http.StatusInternalServerError)
			return
		}

		// Kirim notifikasi sukses langsung ke chat Telegram pengguna
		if err := s.bot.NotifyPaymentSuccess(tx); err != nil {
			log.Printf("[Webhook] Gagal mengirim pesan notifikasi ke user %d: %v\n", tx.TelegramID, err)
		}
	}

	w.WriteHeader(http.StatusOK)
	_ = json.NewEncoder(w).Encode(map[string]string{"status": "success"})
}

type SendEpisodePayload struct {
	TelegramID int64 `json:"telegram_id"`
	EpisodeID  int64 `json:"episode_id"`
}

// handleSendEpisodeAPI memungkinkan Mini App backend meminta Bot mengirim video ke user
func (s *Server) handleSendEpisodeAPI(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		http.Error(w, "Metode tidak diizinkan", http.StatusMethodNotAllowed)
		return
	}

	var payload SendEpisodePayload
	if err := json.NewDecoder(r.Body).Decode(&payload); err != nil {
		http.Error(w, "Payload JSON tidak valid", http.StatusBadRequest)
		return
	}

	if payload.TelegramID == 0 || payload.EpisodeID == 0 {
		http.Error(w, "telegram_id dan episode_id wajib diisi", http.StatusBadRequest)
		return
	}

	// Panggil logika pengiriman video (dengan pengecekan VIP otomatis)
	if err := s.bot.SendEpisode(payload.TelegramID, payload.EpisodeID); err != nil {
		http.Error(w, err.Error(), http.StatusForbidden)
		return
	}

	w.WriteHeader(http.StatusOK)
	_ = json.NewEncoder(w).Encode(map[string]string{"status": "sent"})
}

// handleGetPlans mengembalikan 7 daftar paket VIP dalam format JSON
func (s *Server) handleGetPlans(w http.ResponseWriter, r *http.Request) {
	plans, err := s.repo.GetActiveVIPPlans()
	if err != nil {
		http.Error(w, "Gagal mengambil paket VIP", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	_ = json.NewEncoder(w).Encode(plans)
}
