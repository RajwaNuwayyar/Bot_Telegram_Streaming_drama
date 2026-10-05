package database

import "time"

// Repository mendefinisikan kontrak operasi database yang disiapkan untuk Bot Developer
// Interface ini mempermudah kolaborasi dengan Database Engineer (dapat dihubungkan ke PostgreSQL/MySQL/SQLite/API)
type Repository interface {
	// User
	UpsertUser(user *User) error
	GetUserByTelegramID(telegramID int64) (*User, error)
	AddUserVIPDuration(telegramID int64, days int) (time.Time, error)

	// VIP Plans
	GetActiveVIPPlans() ([]VIPPlan, error)
	GetVIPPlanByID(planID int64) (*VIPPlan, error)

	// Drama & Episode
	SaveEpisode(episode *Episode) error
	GetEpisodeByID(id int64) (*Episode, error)
	GetEpisodeByTitleAndNumber(title string, epNum int) (*Episode, error)
	GetAdjacentEpisodes(title string, currentEpNum int) (prev *Episode, next *Episode, err error)
	UpdateEpisodeVIPByMessageID(messageID int, isVIP bool) error  // Update status VIP saat caption diedit
	DeleteEpisodeByMessageID(messageID int) error                  // Hapus episode saat video dihapus dari channel

	// Transactions
	CreateTransaction(tx *Transaction) error
	GetTransactionByCode(trxCode string) (*Transaction, error)
	MarkTransactionPaid(trxCode string) (*Transaction, error)
	CancelTransaction(trxCode string) error
}
