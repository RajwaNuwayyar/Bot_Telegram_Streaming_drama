package database

import (
	"database/sql"
	"fmt"
	"strings"
	"time"

	_ "github.com/go-sql-driver/mysql"
)

type MySQLRepo struct {
	db *sql.DB
}

// NewMySQLRepo menginisialisasi koneksi ke database MySQL yang menggunakan schema_v5_final.sql
func NewMySQLRepo(dsn string) (*MySQLRepo, error) {
	db, err := sql.Open("mysql", dsn)
	if err != nil {
		return nil, fmt.Errorf("gagal membuka koneksi mysql: %w", err)
	}
    
    // Karena menggunakan schema eksternal, kita skip migrate() dan seed() internal
	repo := &MySQLRepo{db: db}
	return repo, nil
}

// UpsertUser menyimpan atau mengupdate profil pengguna Telegram (Tabel users v5)
func (r *MySQLRepo) UpsertUser(u *User) error {
	now := time.Now()
	query := `
	INSERT INTO users (telegram_user_id, username, first_name, last_name, vip_until, created_at, last_active_at)
	VALUES (?, ?, ?, ?, ?, ?, ?)
	ON DUPLICATE KEY UPDATE
		username = VALUES(username),
		first_name = VALUES(first_name),
		last_name = VALUES(last_name),
		last_active_at = VALUES(last_active_at);
	`
	_, err := r.db.Exec(query, u.TelegramID, u.Username, u.FirstName, u.LastName, u.VIPUntil, now, now)
	return err
}

// GetUserByTelegramID mengambil data user berdasarkan Telegram ID
func (r *MySQLRepo) GetUserByTelegramID(telegramID int64) (*User, error) {
	row := r.db.QueryRow(`SELECT id, telegram_user_id, username, first_name, last_name, vip_until, created_at, last_active_at FROM users WHERE telegram_user_id = ?`, telegramID)

	var u User
	var vipUntil sql.NullTime
	var username, firstName, lastName sql.NullString
	var createdAt, lastActiveAt sql.NullTime

	err := row.Scan(&u.ID, &u.TelegramID, &username, &firstName, &lastName, &vipUntil, &createdAt, &lastActiveAt)
	if err != nil {
		if err == sql.ErrNoRows {
			return nil, nil
		}
		return nil, err
	}
	u.Username = username.String
	u.FirstName = firstName.String
	u.LastName = lastName.String

	if vipUntil.Valid {
		u.VIPUntil = vipUntil.Time
	}
	if createdAt.Valid {
		u.CreatedAt = createdAt.Time
	}
	if lastActiveAt.Valid {
		u.UpdatedAt = lastActiveAt.Time
	}
	return &u, nil
}

// AddUserVIPDuration menambahkan masa aktif VIP pengguna sejumlah hari
func (r *MySQLRepo) AddUserVIPDuration(telegramID int64, days int) (time.Time, error) {
	u, err := r.GetUserByTelegramID(telegramID)
	if err != nil {
		return time.Time{}, err
	}

	baseTime := time.Now()
	if u != nil && u.VIPUntil.After(baseTime) {
		baseTime = u.VIPUntil
	}

	newVIPUntil := baseTime.AddDate(0, 0, days)

	_, err = r.db.Exec(`UPDATE users SET vip_until = ?, last_active_at = ? WHERE telegram_user_id = ?`, newVIPUntil, time.Now(), telegramID)
	if err != nil {
		return time.Time{}, err
	}

	return newVIPUntil, nil
}

// GetActiveVIPPlans mengambil paket VIP dari tabel vip_plans v5
func (r *MySQLRepo) GetActiveVIPPlans() ([]VIPPlan, error) {
	rows, err := r.db.Query(`SELECT id, name, duration_days, price, is_active FROM vip_plans WHERE is_active = 1 ORDER BY sort_order ASC, duration_days ASC`)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	var plans []VIPPlan
	for rows.Next() {
		var p VIPPlan
		var isActiveInt int
		if err := rows.Scan(&p.ID, &p.Name, &p.DurationDays, &p.Price, &isActiveInt); err != nil {
			return nil, err
		}
		p.IsActive = (isActiveInt == 1)
        // Set default karena badge/description tidak ada di v5
        p.Badge = "VIP"
        p.Description = fmt.Sprintf("Akses VIP selama %d Hari", p.DurationDays)
		plans = append(plans, p)
	}
	return plans, nil
}

// GetVIPPlanByID mengambil paket VIP berdasarkan ID
func (r *MySQLRepo) GetVIPPlanByID(planID int64) (*VIPPlan, error) {
	row := r.db.QueryRow(`SELECT id, name, duration_days, price, is_active FROM vip_plans WHERE id = ?`, planID)
	var p VIPPlan
	var isActiveInt int

	if err := row.Scan(&p.ID, &p.Name, &p.DurationDays, &p.Price, &isActiveInt); err != nil {
		if err == sql.ErrNoRows {
			return nil, nil
		}
		return nil, err
	}
	p.IsActive = (isActiveInt == 1)
    p.Badge = "VIP"
    p.Description = fmt.Sprintf("Akses VIP selama %d Hari", p.DurationDays)
	return &p, nil
}

// SaveEpisode menyimpan informasi video ke tabel episodes dan otomatis menambah dramas jika belum ada
func (r *MySQLRepo) SaveEpisode(ep *Episode) error {
    // 1. Cek atau Buat Drama
	var dramaID int64
	err := r.db.QueryRow(`SELECT id FROM dramas WHERE title = ? LIMIT 1`, ep.DramaTitle).Scan(&dramaID)
	if err == sql.ErrNoRows {
		slug := strings.ToLower(strings.ReplaceAll(ep.DramaTitle, " ", "-"))
		// default free episode = 1 (sesuai spesifikasi v5)
		res, err := r.db.Exec(`INSERT INTO dramas (title, slug, poster_url, total_episodes, free_episodes_count) VALUES (?, ?, ?, 0, 1)`, ep.DramaTitle, slug, ep.ThumbnailFileID)
		if err != nil {
			return err
		}
		dramaID, _ = res.LastInsertId()
	} else if err != nil {
		return err
	} else if ep.ThumbnailFileID != "" {
		// Jika drama sudah ada tapi poster_url masih kosong, gunakan thumbnail ini sebagai fallback
		_, _ = r.db.Exec(`UPDATE dramas SET poster_url = ? WHERE id = ? AND (poster_url IS NULL OR poster_url = '')`, ep.ThumbnailFileID, dramaID)
	}

    // 2. Insert Episode
	query := `
	INSERT INTO episodes (drama_id, episode_number, title, telegram_channel_id, telegram_message_id, telegram_file_id, duration_seconds, created_at)
	VALUES (?, ?, ?, ?, ?, ?, ?, ?)
	ON DUPLICATE KEY UPDATE
		telegram_channel_id = VALUES(telegram_channel_id),
		telegram_message_id = VALUES(telegram_message_id),
		telegram_file_id = VALUES(telegram_file_id),
		duration_seconds = VALUES(duration_seconds);
	`
	_, err = r.db.Exec(query, dramaID, ep.EpisodeNumber, ep.Title, ep.ChannelID, ep.MessageID, ep.FileID, ep.Duration, time.Now())
	return err
}

// GetEpisodeByID mengambil episode beserta info free_episodes_count dari dramas
func (r *MySQLRepo) GetEpisodeByID(id int64) (*Episode, error) {
	row := r.db.QueryRow(`
        SELECT e.id, e.drama_id, d.title, e.episode_number, e.title, e.telegram_channel_id, e.telegram_message_id, e.telegram_file_id, e.duration_seconds, e.created_at, d.free_episodes_count 
        FROM episodes e 
        JOIN dramas d ON e.drama_id = d.id 
        WHERE e.id = ?`, id)
	var ep Episode
	var title sql.NullString
	var createdAt sql.NullTime
    var freeEpsCount int

	err := row.Scan(&ep.ID, &ep.DramaID, &ep.DramaTitle, &ep.EpisodeNumber, &title, &ep.ChannelID, &ep.MessageID, &ep.FileID, &ep.Duration, &createdAt, &freeEpsCount)
	if err != nil {
		if err == sql.ErrNoRows {
			return nil, nil
		}
		return nil, err
	}
	ep.Title = title.String
	ep.IsVIP = (ep.EpisodeNumber > freeEpsCount)
	if createdAt.Valid {
		ep.CreatedAt = createdAt.Time
	}
	return &ep, nil
}

// GetEpisodeByTitleAndNumber mencari episode berdasarkan judul
func (r *MySQLRepo) GetEpisodeByTitleAndNumber(title string, epNum int) (*Episode, error) {
	row := r.db.QueryRow(`
        SELECT e.id, e.drama_id, d.title, e.episode_number, e.title, e.telegram_channel_id, e.telegram_message_id, e.telegram_file_id, e.duration_seconds, e.created_at, d.free_episodes_count 
        FROM episodes e 
        JOIN dramas d ON e.drama_id = d.id 
        WHERE d.title LIKE ? AND e.episode_number = ?`, "%"+title+"%", epNum)
	var ep Episode
	var epTitle sql.NullString
	var createdAt sql.NullTime
    var freeEpsCount int

	err := row.Scan(&ep.ID, &ep.DramaID, &ep.DramaTitle, &ep.EpisodeNumber, &epTitle, &ep.ChannelID, &ep.MessageID, &ep.FileID, &ep.Duration, &createdAt, &freeEpsCount)
	if err != nil {
		if err == sql.ErrNoRows {
			return nil, nil
		}
		return nil, err
	}
	ep.Title = epTitle.String
	ep.IsVIP = (ep.EpisodeNumber > freeEpsCount)
	if createdAt.Valid {
		ep.CreatedAt = createdAt.Time
	}
	return &ep, nil
}

func (r *MySQLRepo) GetAdjacentEpisodes(title string, currentEpNum int) (prev *Episode, next *Episode, err error) {
	prev, _ = r.GetEpisodeByTitleAndNumber(title, currentEpNum-1)
	next, _ = r.GetEpisodeByTitleAndNumber(title, currentEpNum+1)
	return prev, next, nil
}

// CreateTransaction menggunakan tabel vip_purchases dan qris_payments v5
func (r *MySQLRepo) CreateTransaction(tx *Transaction) error {
    var userID int64
    err := r.db.QueryRow(`SELECT id FROM users WHERE telegram_user_id = ?`, tx.TelegramID).Scan(&userID)
    if err != nil {
        // Jika user belum ada (seharusnya tidak mungkin karena /start membuat user), buat dulu
        r.UpsertUser(&User{TelegramID: tx.TelegramID})
        r.db.QueryRow(`SELECT id FROM users WHERE telegram_user_id = ?`, tx.TelegramID).Scan(&userID)
    }

	res, err := r.db.Exec(`INSERT INTO vip_purchases (user_id, plan_id, amount, payment_method, status, created_at) VALUES (?, ?, ?, 'qris', ?, ?)`, 
        userID, tx.PlanID, tx.Amount, string(tx.Status), tx.CreatedAt)
	if err != nil {
		return err
	}
	purchaseID, _ := res.LastInsertId()
	tx.ID = purchaseID

    // Simpan gateway reference di qris_payments
    _, err = r.db.Exec(`INSERT INTO qris_payments (vip_purchase_id, gateway_name, gateway_ref_id, qris_payload, gateway_status, expired_at) VALUES (?, 'local_qris', ?, ?, ?, ?)`,
        purchaseID, tx.TrxCode, tx.QRISString, string(tx.Status), tx.ExpiredAt)
    return err
}

// GetTransactionByCode
func (r *MySQLRepo) GetTransactionByCode(trxCode string) (*Transaction, error) {
	row := r.db.QueryRow(`
        SELECT v.id, q.gateway_ref_id, u.telegram_user_id, v.plan_id, p.name, v.amount, q.qris_payload, v.status, v.created_at, v.paid_at, q.expired_at 
        FROM vip_purchases v
        JOIN qris_payments q ON v.id = q.vip_purchase_id
        JOIN users u ON v.user_id = u.id
        JOIN vip_plans p ON v.plan_id = p.id
        WHERE q.gateway_ref_id = ?`, trxCode)
	
    var tx Transaction
	var paidAt sql.NullTime
	var qrisString sql.NullString
	var createdAt, expiredAt sql.NullTime
    var statusStr string

	err := row.Scan(&tx.ID, &tx.TrxCode, &tx.TelegramID, &tx.PlanID, &tx.PlanName, &tx.Amount, &qrisString, &statusStr, &createdAt, &paidAt, &expiredAt)
	if err != nil {
		if err == sql.ErrNoRows {
			return nil, nil
		}
		return nil, err
	}
    tx.Status = TransactionStatus(statusStr)
	tx.QRISString = qrisString.String
	if createdAt.Valid {
		tx.CreatedAt = createdAt.Time
	}
	if expiredAt.Valid {
		tx.ExpiredAt = expiredAt.Time
	}
	if paidAt.Valid {
		tx.PaidAt = &paidAt.Time
	}
	return &tx, nil
}

// MarkTransactionPaid
func (r *MySQLRepo) MarkTransactionPaid(trxCode string) (*Transaction, error) {
	tx, err := r.GetTransactionByCode(trxCode)
	if err != nil || tx == nil {
		return nil, fmt.Errorf("transaksi tidak ditemukan: %w", err)
	}

	if tx.Status == TxStatusPaid {
		return tx, nil
	}

	now := time.Now()
    
    // Update vip_purchases & set vip_start_at
	_, err = r.db.Exec(`UPDATE vip_purchases SET status = 'paid', paid_at = ?, vip_start_at = ? WHERE id = ?`, now, now, tx.ID)
	if err != nil {
		return nil, err
	}
    // Update qris_payments
    _, err = r.db.Exec(`UPDATE qris_payments SET gateway_status = 'paid', paid_at = ? WHERE gateway_ref_id = ?`, now, trxCode)
    
	tx.Status = TxStatusPaid
	tx.PaidAt = &now

	plan, err := r.GetVIPPlanByID(tx.PlanID)
	if err != nil || plan == nil {
		_, _ = r.AddUserVIPDuration(tx.TelegramID, 30)
	} else {
		newVipUntil, _ := r.AddUserVIPDuration(tx.TelegramID, plan.DurationDays)
        // Sesuaikan vip_end_at di tabel purchase
        r.db.Exec(`UPDATE vip_purchases SET vip_end_at = ? WHERE id = ?`, newVipUntil, tx.ID)
	}

	return tx, nil
}

func (r *MySQLRepo) CancelTransaction(trxCode string) error {
    tx, _ := r.GetTransactionByCode(trxCode)
    if tx != nil {
	    r.db.Exec(`UPDATE vip_purchases SET status = 'cancelled' WHERE id = ? AND status = 'pending'`, tx.ID)
        r.db.Exec(`UPDATE qris_payments SET gateway_status = 'cancelled' WHERE gateway_ref_id = ? AND gateway_status = 'pending'`, trxCode)
    }
	return nil
}

// UpdateEpisodeVIPByMessageID mengupdate kolom is_vip pada episode berdasarkan telegram_message_id.
// Dipanggil saat admin mengedit caption postingan di channel (menambah/menghapus #vip).
func (r *MySQLRepo) UpdateEpisodeVIPByMessageID(messageID int, isVIP bool) error {
	vipVal := 0
	if isVIP {
		vipVal = 1
	}
	res, err := r.db.Exec(`UPDATE episodes SET is_vip = ? WHERE telegram_message_id = ?`, vipVal, messageID)
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
func (r *MySQLRepo) DeleteEpisodeByMessageID(messageID int) error {
	res, err := r.db.Exec(`DELETE FROM episodes WHERE telegram_message_id = ?`, messageID)
	if err != nil {
		return fmt.Errorf("gagal menghapus episode: %w", err)
	}
	rows, _ := res.RowsAffected()
	if rows == 0 {
		return fmt.Errorf("episode dengan message_id %d tidak ditemukan di database", messageID)
	}
	return nil
}

// UpdateDramaPoster memperbarui kolom poster_url pada drama berdasarkan judul atau slug.
// Dipanggil saat admin mengunggah foto thumbnail dengan tag #poster di channel Telegram.
func (r *MySQLRepo) UpdateDramaPoster(dramaTitle string, posterFileID string) error {
	dramaTitle = strings.TrimSpace(dramaTitle)
	if dramaTitle == "" {
		return fmt.Errorf("judul drama tidak boleh kosong")
	}
	slug := strings.ToLower(strings.ReplaceAll(dramaTitle, " ", "-"))

	var dramaID int64
	err := r.db.QueryRow(`SELECT id FROM dramas WHERE LOWER(title) = LOWER(?) OR slug = ? LIMIT 1`, dramaTitle, slug).Scan(&dramaID)
	if err == sql.ErrNoRows {
		// Jika drama belum ada di database, buat record baru dengan poster_url
		_, err = r.db.Exec(`INSERT INTO dramas (title, slug, poster_url, total_episodes, free_episodes_count) VALUES (?, ?, ?, 0, 1)`, dramaTitle, slug, posterFileID)
		return err
	} else if err != nil {
		return err
	}

	// Update drama yang sudah ada
	_, err = r.db.Exec(`UPDATE dramas SET poster_url = ? WHERE id = ?`, posterFileID, dramaID)
	return err
}
// DeleteDramaByTitle menghapus seluruh drama beserta semua episodenya dari database berdasarkan judul.
// Episode ikut terhapus otomatis via ON DELETE CASCADE di foreign key dramas→episodes.
// Mengembalikan jumlah episode yang dihapus.
func (r *MySQLRepo) DeleteDramaByTitle(dramaTitle string) (int64, error) {
	dramaTitle = strings.TrimSpace(dramaTitle)
	if dramaTitle == "" {
		return 0, fmt.Errorf("judul drama tidak boleh kosong")
	}

	// Hitung dulu episode yang akan terhapus (untuk laporan ke admin)
	var epCount int64
	_ = r.db.QueryRow(`SELECT COUNT(*) FROM episodes e INNER JOIN dramas d ON e.drama_id = d.id WHERE LOWER(d.title) = LOWER(?)`, dramaTitle).Scan(&epCount)

	// Hapus drama (episode ikut terhapus via ON DELETE CASCADE)
	res, err := r.db.Exec(`DELETE FROM dramas WHERE LOWER(title) = LOWER(?)`, dramaTitle)
	if err != nil {
		return 0, fmt.Errorf("gagal menghapus drama '%s': %w", dramaTitle, err)
	}
	rows, _ := res.RowsAffected()
	if rows == 0 {
		return 0, fmt.Errorf("drama dengan judul '%s' tidak ditemukan di database", dramaTitle)
	}

	return epCount, nil
}

