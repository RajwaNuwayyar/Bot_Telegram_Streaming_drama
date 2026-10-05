package database

import (
	"time"
)

// User merepresentasikan pengguna bot Telegram
type User struct {
	ID        int64     `json:"id"`
	TelegramID int64    `json:"telegram_id"`
	Username  string    `json:"username"`
	FirstName string    `json:"first_name"`
	LastName  string    `json:"last_name"`
	VIPUntil  time.Time `json:"vip_until"`
	CreatedAt time.Time `json:"created_at"`
	UpdatedAt time.Time `json:"updated_at"`
}

// IsVIPActive memeriksa apakah masa aktif VIP user masih berlaku
func (u *User) IsVIPActive() bool {
	if u == nil {
		return false
	}
	return u.VIPUntil.After(time.Now())
}

// VIPPlan merepresentasikan paket langganan VIP (7 durasi dari tabel vip_plans)
type VIPPlan struct {
	ID           int64   `json:"id"`
	Name         string  `json:"name"`          // misal: "1 Bulan VIP"
	DurationDays int     `json:"duration_days"` // 1, 3, 7, 15, 30, 90, 365
	Price        float64 `json:"price"`         // harga dalam Rupiah
	Badge        string  `json:"badge"`         // misal: "🔥 Best Seller", "👑 Populer"
	Description  string  `json:"description"`
	IsActive     bool    `json:"is_active"`
}

// Drama merepresentasikan judul serial drama
type Drama struct {
	ID            int64     `json:"id"`
	Title         string    `json:"title"`
	Description   string    `json:"description"`
	CoverURL      string    `json:"cover_url"`
	TotalEpisodes int       `json:"total_episodes"`
	CreatedAt     time.Time `json:"created_at"`
}

// Episode merepresentasikan video episode dari channel privat
type Episode struct {
	ID            int64     `json:"id"`
	DramaID       int64     `json:"drama_id"`
	DramaTitle    string    `json:"drama_title"`
	EpisodeNumber int       `json:"episode_number"`
	Title         string    `json:"title"`
	ChannelID     int64     `json:"channel_id"`
	MessageID     int       `json:"message_id"`
	FileID        string    `json:"file_id"`
	Duration        int       `json:"duration"`
	IsVIP           bool      `json:"is_vip"` // true jika hanya untuk member VIP
	Caption         string    `json:"caption"`
	ThumbnailFileID string    `json:"thumbnail_file_id"`
	CreatedAt       time.Time `json:"created_at"`
}

// TransactionStatus status pembayaran QRIS
type TransactionStatus string

const (
	TxStatusPending   TransactionStatus = "PENDING"
	TxStatusPaid      TransactionStatus = "PAID"
	TxStatusExpired   TransactionStatus = "EXPIRED"
	TxStatusCancelled TransactionStatus = "CANCELLED"
)

// Transaction merepresentasikan transaksi pembelian paket VIP
type Transaction struct {
	ID          int64             `json:"id"`
	TrxCode     string            `json:"trx_code"`
	TelegramID  int64             `json:"telegram_id"`
	PlanID      int64             `json:"plan_id"`
	PlanName    string            `json:"plan_name"`
	Amount      float64           `json:"amount"`
	QRISString  string            `json:"qris_string"`
	Status      TransactionStatus `json:"status"`
	CreatedAt   time.Time         `json:"created_at"`
	PaidAt      *time.Time        `json:"paid_at,omitempty"`
	ExpiredAt   time.Time         `json:"expired_at"`
}
