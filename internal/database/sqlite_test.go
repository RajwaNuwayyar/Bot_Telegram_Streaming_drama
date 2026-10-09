package database

import (
	"os"
	"testing"
	"time"
)

func TestSQLiteRepository(t *testing.T) {
	testDB := "test_dramabot.db"
	_ = os.Remove(testDB)
	defer os.Remove(testDB)

	repo, err := NewSQLiteRepo(testDB)
	if err != nil {
		t.Fatalf("Gagal inisialisasi SQLite repo: %v", err)
	}

	// 1. Uji 7 Paket VIP
	plans, err := repo.GetActiveVIPPlans()
	if err != nil {
		t.Fatalf("Gagal mengambil paket VIP: %v", err)
	}
	if len(plans) != 7 {
		t.Errorf("Jumlah paket VIP = %d, diharapkan 7 paket", len(plans))
	}

	// 2. Uji User & VIP Duration
	user := &User{
		TelegramID: 12345678,
		Username:   "danang_user",
		FirstName:  "Danang",
	}
	if err := repo.UpsertUser(user); err != nil {
		t.Fatalf("Gagal upsert user: %v", err)
	}

	u, err := repo.GetUserByTelegramID(12345678)
	if err != nil || u == nil {
		t.Fatalf("User tidak ditemukan: %v", err)
	}
	if u.IsVIPActive() {
		t.Errorf("User baru seharusnya belum VIP")
	}

	// Tambah durasi VIP 30 hari
	vipUntil, err := repo.AddUserVIPDuration(12345678, 30)
	if err != nil {
		t.Fatalf("Gagal menambah durasi VIP: %v", err)
	}
	if !vipUntil.After(time.Now()) {
		t.Errorf("VIP Until harus lebih dari sekarang")
	}

	u, _ = repo.GetUserByTelegramID(12345678)
	if !u.IsVIPActive() {
		t.Errorf("User harusnya sudah berstatus VIP aktif")
	}

	// 3. Uji Save Episode & Navigasi
	ep1 := &Episode{
		DramaTitle:    "CEO Rahasia",
		EpisodeNumber: 1,
		Title:         "CEO Rahasia - Episode 1",
		ChannelID:     -100123456789,
		MessageID:     101,
		FileID:        "file_ep1_xxx",
		IsVIP:         false,
	}
	ep2 := &Episode{
		DramaTitle:    "CEO Rahasia",
		EpisodeNumber: 2,
		Title:         "CEO Rahasia - Episode 2",
		ChannelID:     -100123456789,
		MessageID:     102,
		FileID:        "file_ep2_yyy",
		IsVIP:         false,
	}
	ep3 := &Episode{
		DramaTitle:    "CEO Rahasia",
		EpisodeNumber: 3,
		Title:         "CEO Rahasia - Episode 3",
		ChannelID:     -100123456789,
		MessageID:     103,
		FileID:        "file_ep3_zzz",
		IsVIP:         true,
	}

	_ = repo.SaveEpisode(ep1)
	_ = repo.SaveEpisode(ep2)
	_ = repo.SaveEpisode(ep3)

	foundEp2, err := repo.GetEpisodeByTitleAndNumber("CEO Rahasia", 2)
	if err != nil || foundEp2 == nil {
		t.Fatalf("Episode 2 tidak ditemukan: %v", err)
	}

	prev, next, err := repo.GetAdjacentEpisodes("CEO Rahasia", 2)
	if err != nil {
		t.Fatalf("Error ambil episode adjacent: %v", err)
	}
	if prev == nil || prev.EpisodeNumber != 1 {
		t.Errorf("Episode sebelumnya harus episode 1")
	}
	if next == nil || next.EpisodeNumber != 3 {
		t.Errorf("Episode selanjutnya harus episode 3")
	}

	// 4. Uji Transaksi & Pembayaran
	plan30 := plans[4] // 30 hari
	tx := &Transaction{
		TrxCode:    "TRXTEST999",
		TelegramID: 12345678,
		PlanID:     plan30.ID,
		PlanName:   plan30.Name,
		Amount:     plan30.Price,
		QRISString: "DUMMY_QRIS_PAYLOAD",
		Status:     TxStatusPending,
		CreatedAt:  time.Now(),
		ExpiredAt:  time.Now().Add(15 * time.Minute),
	}
	if err := repo.CreateTransaction(tx); err != nil {
		t.Fatalf("Gagal membuat transaksi: %v", err)
	}

	paidTx, err := repo.MarkTransactionPaid("TRXTEST999")
	if err != nil {
		t.Fatalf("Gagal mark paid: %v", err)
	}
	if paidTx.Status != TxStatusPaid {
		t.Errorf("Status transaksi harus PAID")
	}

	// 5. Uji UpdateDramaTitle
	updatedCount, err := repo.UpdateDramaTitle("CEO Rahasia", "CEO Miliarder Rahasia")
	if err != nil {
		t.Fatalf("Gagal update judul drama: %v", err)
	}
	if updatedCount != 3 {
		t.Errorf("Jumlah episode terupdate = %d, diharapkan 3", updatedCount)
	}

	updatedEp2, err := repo.GetEpisodeByTitleAndNumber("CEO Miliarder Rahasia", 2)
	if err != nil || updatedEp2 == nil {
		t.Fatalf("Episode dengan judul drama baru tidak ditemukan: %v", err)
	}
	if updatedEp2.DramaTitle != "CEO Miliarder Rahasia" {
		t.Errorf("Judul drama = %q, diharapkan %q", updatedEp2.DramaTitle, "CEO Miliarder Rahasia")
	}

	// 6. Uji UpdateEpisodeDetailsByMessageID
	err = repo.UpdateEpisodeDetailsByMessageID(101, "CEO Terhebat", 1, true, "CEO Terhebat - Episode 1 #vip")
	if err != nil {
		t.Fatalf("Gagal update detail episode: %v", err)
	}
	ep1Updated, err := repo.GetEpisodeByTitleAndNumber("CEO Terhebat", 1)
	if err != nil || ep1Updated == nil {
		t.Fatalf("Episode 1 setelah diedit tidak ditemukan: %v", err)
	}
	if !ep1Updated.IsVIP {
		t.Errorf("Episode 1 harusnya VIP true")
	}
}
