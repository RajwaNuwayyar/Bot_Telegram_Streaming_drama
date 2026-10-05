package database

import (
	"database/sql"
	"fmt"
	"log"
	"time"

	_ "modernc.org/sqlite"
)

type SQLiteRepo struct {
	db *sql.DB
}

// NewSQLiteRepo menginisialisasi database SQLite dan membuat skema tabel jika belum ada
func NewSQLiteRepo(dbPath string) (*SQLiteRepo, error) {
	db, err := sql.Open("sqlite", dbPath)
	if err != nil {
		return nil, fmt.Errorf("gagal membuka koneksi sqlite: %w", err)
	}

	repo := &SQLiteRepo{db: db}
	if err := repo.migrate(); err != nil {
		return nil, fmt.Errorf("migrasi database gagal: %w", err)
	}

	if err := repo.seedVIPPlans(); err != nil {
		log.Printf("[Database] Peringatan seeding vip_plans: %v\n", err)
	}

	return repo, nil
}

func (r *SQLiteRepo) migrate() error {
	queries := []string{
		`CREATE TABLE IF NOT EXISTS users (
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			telegram_id INTEGER UNIQUE NOT NULL,
			username TEXT,
			first_name TEXT,
			last_name TEXT,
			vip_until DATETIME,
			created_at DATETIME,
			updated_at DATETIME
		);`,
		`CREATE TABLE IF NOT EXISTS vip_plans (
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			name TEXT NOT NULL,
			duration_days INTEGER NOT NULL,
			price REAL NOT NULL,
			badge TEXT,
			description TEXT,
			is_active INTEGER DEFAULT 1
		);`,
		`CREATE TABLE IF NOT EXISTS episodes (
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			drama_id INTEGER DEFAULT 0,
			drama_title TEXT NOT NULL,
			episode_number INTEGER NOT NULL,
			title TEXT,
			channel_id INTEGER NOT NULL,
			message_id INTEGER NOT NULL,
			file_id TEXT NOT NULL,
			duration INTEGER DEFAULT 0,
			is_vip INTEGER DEFAULT 0,
			caption TEXT,
			created_at DATETIME,
			UNIQUE(drama_title, episode_number)
		);`,
		`CREATE TABLE IF NOT EXISTS transactions (
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			trx_code TEXT UNIQUE NOT NULL,
			telegram_id INTEGER NOT NULL,
			plan_id INTEGER NOT NULL,
			plan_name TEXT NOT NULL,
			amount REAL NOT NULL,
			qris_string TEXT,
			status TEXT NOT NULL,
			created_at DATETIME,
			paid_at DATETIME,
			expired_at DATETIME
		);`,
	}

	for _, q := range queries {
		if _, err := r.db.Exec(q); err != nil {
			return err
		}
	}

	return nil
}

// seedVIPPlans menginput 7 pilihan durasi VIP sesuai instruksi tugas
func (r *SQLiteRepo) seedVIPPlans() error {
	var count int
	err := r.db.QueryRow(`SELECT COUNT(*) FROM vip_plans`).Scan(&count)
	if err != nil {
		return err
	}

	if count > 0 {
		return nil // Sudah diisi
	}

	plans := []VIPPlan{
		{Name: "VIP 1 Hari", DurationDays: 1, Price: 3000, Badge: "⚡ Trial", Description: "Akses kilat seluruh drama selama 24 jam"},
		{Name: "VIP 3 Hari", DurationDays: 3, Price: 6000, Badge: "✨ Hemat", Description: "Cocok untuk marathon di akhir pekan (~Rp2.000/hari)"},
		{Name: "VIP 7 Hari", DurationDays: 7, Price: 10000, Badge: "🎉 1 Minggu", Description: "Akses VIP puas selama satu minggu (~Rp1.429/hari)"},
		{Name: "VIP 15 Hari", DurationDays: 15, Price: 20000, Badge: "⭐ 2 Minggu", Description: "Pilihan fleksibel dua minggu penuh (~Rp1.333/hari)"},
		{Name: "VIP 30 Hari", DurationDays: 30, Price: 35000, Badge: "🔥 Best Seller", Description: "Paket paling diminati dan paling hemat (~Rp1.167/hari)"},
		{Name: "VIP 90 Hari", DurationDays: 90, Price: 90000, Badge: "💎 3 Bulan", Description: "Bebas nonton sepuasnya selama 3 bulan (~Rp1.000/hari)"},
		{Name: "VIP 365 Hari", DurationDays: 365, Price: 300000, Badge: "👑 Super VIP", Description: "Akses VIP eksklusif 1 tahun penuh tanpa batas (~Rp822/hari)"},
	}

	stmt, err := r.db.Prepare(`INSERT INTO vip_plans (name, duration_days, price, badge, description, is_active) VALUES (?, ?, ?, ?, ?, 1)`)
	if err != nil {
		return err
	}
	defer stmt.Close()

	for _, p := range plans {
		if _, err := stmt.Exec(p.Name, p.DurationDays, p.Price, p.Badge, p.Description); err != nil {
			return err
		}
	}

	log.Println("[Database] Berhasil memasukkan 7 paket VIP ke tabel vip_plans")
	return nil
}

// UpsertUser menyimpan atau mengupdate profil pengguna Telegram
func (r *SQLiteRepo) UpsertUser(u *User) error {
	now := time.Now()
	query := `
	INSERT INTO users (telegram_id, username, first_name, last_name, vip_until, created_at, updated_at)
	VALUES (?, ?, ?, ?, ?, ?, ?)
	ON CONFLICT(telegram_id) DO UPDATE SET
		username = excluded.username,
		first_name = excluded.first_name,
		last_name = excluded.last_name,
		updated_at = excluded.updated_at;
	`
	_, err := r.db.Exec(query, u.TelegramID, u.Username, u.FirstName, u.LastName, u.VIPUntil, now, now)
	return err
}

// GetUserByTelegramID mengambil data user berdasarkan Telegram ID
func (r *SQLiteRepo) GetUserByTelegramID(telegramID int64) (*User, error) {
	row := r.db.QueryRow(`SELECT id, telegram_id, username, first_name, last_name, vip_until, created_at, updated_at FROM users WHERE telegram_id = ?`, telegramID)

	var u User
	var vipUntil sql.NullTime
	err := row.Scan(&u.ID, &u.TelegramID, &u.Username, &u.FirstName, &u.LastName, &vipUntil, &u.CreatedAt, &u.UpdatedAt)
	if err != nil {
		if err == sql.ErrNoRows {
			return nil, nil
		}
		return nil, err
	}
	if vipUntil.Valid {
		u.VIPUntil = vipUntil.Time
	}
	return &u, nil
}

// AddUserVIPDuration menambahkan masa aktif VIP pengguna sejumlah hari
func (r *SQLiteRepo) AddUserVIPDuration(telegramID int64, days int) (time.Time, error) {
	u, err := r.GetUserByTelegramID(telegramID)
	if err != nil {
		return time.Time{}, err
	}

	baseTime := time.Now()
	if u != nil && u.VIPUntil.After(baseTime) {
		baseTime = u.VIPUntil
	}

	newVIPUntil := baseTime.AddDate(0, 0, days)

	_, err = r.db.Exec(`UPDATE users SET vip_until = ?, updated_at = ? WHERE telegram_id = ?`, newVIPUntil, time.Now(), telegramID)
	if err != nil {
		return time.Time{}, err
	}

	return newVIPUntil, nil
}

// GetActiveVIPPlans mengambil seluruh 7 paket VIP aktif
func (r *SQLiteRepo) GetActiveVIPPlans() ([]VIPPlan, error) {
	rows, err := r.db.Query(`SELECT id, name, duration_days, price, badge, description, is_active FROM vip_plans WHERE is_active = 1 ORDER BY duration_days ASC`)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	var plans []VIPPlan
	for rows.Next() {
		var p VIPPlan
		var isActiveInt int
		if err := rows.Scan(&p.ID, &p.Name, &p.DurationDays, &p.Price, &p.Badge, &p.Description, &isActiveInt); err != nil {
			return nil, err
		}
		p.IsActive = (isActiveInt == 1)
		plans = append(plans, p)
	}
	return plans, nil
}

// GetVIPPlanByID mengambil paket VIP berdasarkan ID
func (r *SQLiteRepo) GetVIPPlanByID(planID int64) (*VIPPlan, error) {
	row := r.db.QueryRow(`SELECT id, name, duration_days, price, badge, description, is_active FROM vip_plans WHERE id = ?`, planID)
	var p VIPPlan
	var isActiveInt int
	if err := row.Scan(&p.ID, &p.Name, &p.DurationDays, &p.Price, &p.Badge, &p.Description, &isActiveInt); err != nil {
		if err == sql.ErrNoRows {
			return nil, nil
		}
		return nil, err
	}
	p.IsActive = (isActiveInt == 1)
	return &p, nil
}

// SaveEpisode menyimpan informasi video yang diposting di channel privat
func (r *SQLiteRepo) SaveEpisode(ep *Episode) error {
	now := time.Now()
	isVIPInt := 0
	if ep.IsVIP {
		isVIPInt = 1
	}

	query := `
	INSERT INTO episodes (drama_id, drama_title, episode_number, title, channel_id, message_id, file_id, duration, is_vip, caption, created_at)
	VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
	ON CONFLICT(drama_title, episode_number) DO UPDATE SET
		channel_id = excluded.channel_id,
		message_id = excluded.message_id,
		file_id = excluded.file_id,
		duration = excluded.duration,
		is_vip = excluded.is_vip,
		caption = excluded.caption;
	`
	_, err := r.db.Exec(query, ep.DramaID, ep.DramaTitle, ep.EpisodeNumber, ep.Title, ep.ChannelID, ep.MessageID, ep.FileID, ep.Duration, isVIPInt, ep.Caption, now)
	return err
}

// GetEpisodeByID mengambil episode berdasarkan ID
func (r *SQLiteRepo) GetEpisodeByID(id int64) (*Episode, error) {
	row := r.db.QueryRow(`SELECT id, drama_id, drama_title, episode_number, title, channel_id, message_id, file_id, duration, is_vip, caption, created_at FROM episodes WHERE id = ?`, id)
	var ep Episode
	var isVIPInt int
	err := row.Scan(&ep.ID, &ep.DramaID, &ep.DramaTitle, &ep.EpisodeNumber, &ep.Title, &ep.ChannelID, &ep.MessageID, &ep.FileID, &ep.Duration, &isVIPInt, &ep.Caption, &ep.CreatedAt)
	if err != nil {
		if err == sql.ErrNoRows {
			return nil, nil
		}
		return nil, err
	}
	ep.IsVIP = (isVIPInt == 1)
	return &ep, nil
}

// GetEpisodeByTitleAndNumber mencari episode berdasarkan judul drama dan nomor episode
func (r *SQLiteRepo) GetEpisodeByTitleAndNumber(title string, epNum int) (*Episode, error) {
	row := r.db.QueryRow(`SELECT id, drama_id, drama_title, episode_number, title, channel_id, message_id, file_id, duration, is_vip, caption, created_at FROM episodes WHERE drama_title LIKE ? AND episode_number = ?`, "%"+title+"%", epNum)
	var ep Episode
	var isVIPInt int
	err := row.Scan(&ep.ID, &ep.DramaID, &ep.DramaTitle, &ep.EpisodeNumber, &ep.Title, &ep.ChannelID, &ep.MessageID, &ep.FileID, &ep.Duration, &isVIPInt, &ep.Caption, &ep.CreatedAt)
	if err != nil {
		if err == sql.ErrNoRows {
			return nil, nil
		}
		return nil, err
	}
	ep.IsVIP = (isVIPInt == 1)
	return &ep, nil
}

// GetAdjacentEpisodes mencari episode sebelum dan sesudah untuk navigasi tombol
func (r *SQLiteRepo) GetAdjacentEpisodes(title string, currentEpNum int) (prev *Episode, next *Episode, err error) {
	prev, _ = r.GetEpisodeByTitleAndNumber(title, currentEpNum-1)
	next, _ = r.GetEpisodeByTitleAndNumber(title, currentEpNum+1)
	return prev, next, nil
}

// CreateTransaction menyimpan transaksi pembelian baru
func (r *SQLiteRepo) CreateTransaction(tx *Transaction) error {
	query := `
	INSERT INTO transactions (trx_code, telegram_id, plan_id, plan_name, amount, qris_string, status, created_at, expired_at)
	VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
	`
	res, err := r.db.Exec(query, tx.TrxCode, tx.TelegramID, tx.PlanID, tx.PlanName, tx.Amount, tx.QRISString, tx.Status, tx.CreatedAt, tx.ExpiredAt)
	if err != nil {
		return err
	}
	tx.ID, _ = res.LastInsertId()
	return nil
}

// GetTransactionByCode mengambil transaksi berdasarkan kode unik
func (r *SQLiteRepo) GetTransactionByCode(trxCode string) (*Transaction, error) {
	row := r.db.QueryRow(`SELECT id, trx_code, telegram_id, plan_id, plan_name, amount, qris_string, status, created_at, paid_at, expired_at FROM transactions WHERE trx_code = ?`, trxCode)
	var tx Transaction
	var paidAt sql.NullTime
	err := row.Scan(&tx.ID, &tx.TrxCode, &tx.TelegramID, &tx.PlanID, &tx.PlanName, &tx.Amount, &tx.QRISString, &tx.Status, &tx.CreatedAt, &paidAt, &tx.ExpiredAt)
	if err != nil {
		if err == sql.ErrNoRows {
			return nil, nil
		}
		return nil, err
	}
	if paidAt.Valid {
		tx.PaidAt = &paidAt.Time
	}
	return &tx, nil
}

// MarkTransactionPaid menandai transaksi lunas dan memperpanjang VIP
func (r *SQLiteRepo) MarkTransactionPaid(trxCode string) (*Transaction, error) {
	tx, err := r.GetTransactionByCode(trxCode)
	if err != nil || tx == nil {
		return nil, fmt.Errorf("transaksi tidak ditemukan: %w", err)
	}

	if tx.Status == TxStatusPaid {
		return tx, nil // Sudah lunas sebelumnya
	}

	now := time.Now()
	_, err = r.db.Exec(`UPDATE transactions SET status = ?, paid_at = ? WHERE trx_code = ?`, TxStatusPaid, now, trxCode)
	if err != nil {
		return nil, err
	}
	tx.Status = TxStatusPaid
	tx.PaidAt = &now

	// Dapatkan durasi paket
	plan, err := r.GetVIPPlanByID(tx.PlanID)
	if err != nil || plan == nil {
		// fallback jika tidak ditemukan, gunakan perkiraan dari nama atau 30 hari
		_, _ = r.AddUserVIPDuration(tx.TelegramID, 30)
	} else {
		_, _ = r.AddUserVIPDuration(tx.TelegramID, plan.DurationDays)
	}

	return tx, nil
}

// CancelTransaction membatalkan transaksi yang belum dibayar
func (r *SQLiteRepo) CancelTransaction(trxCode string) error {
	_, err := r.db.Exec(`UPDATE transactions SET status = ? WHERE trx_code = ? AND status = ?`, TxStatusCancelled, trxCode, TxStatusPending)
	return err
}

// UpdateEpisodeVIPByMessageID mengupdate kolom is_vip pada episode berdasarkan telegram_message_id.
// Dipanggil saat admin mengedit caption postingan di channel (menambah/menghapus #vip).
func (r *SQLiteRepo) UpdateEpisodeVIPByMessageID(messageID int, isVIP bool) error {
	vipVal := 0
	if isVIP {
		vipVal = 1
	}
	res, err := r.db.Exec(`UPDATE episodes SET is_vip = ? WHERE message_id = ?`, vipVal, messageID)
	if err != nil {
		return fmt.Errorf("gagal update is_vip: %w", err)
	}
	rows, _ := res.RowsAffected()
	if rows == 0 {
		return fmt.Errorf("episode dengan message_id %d tidak ditemukan di database", messageID)
	}
	return nil
}

// DeleteEpisodeByMessageID menghapus episode dari database berdasarkan telegram_message_id.
// Dipanggil saat admin menggunakan perintah /hapus_episode setelah menghapus video dari channel.
func (r *SQLiteRepo) DeleteEpisodeByMessageID(messageID int) error {
	res, err := r.db.Exec(`DELETE FROM episodes WHERE message_id = ?`, messageID)
	if err != nil {
		return fmt.Errorf("gagal menghapus episode: %w", err)
	}
	rows, _ := res.RowsAffected()
	if rows == 0 {
		return fmt.Errorf("episode dengan message_id %d tidak ditemukan di database", messageID)
	}
	return nil
}

