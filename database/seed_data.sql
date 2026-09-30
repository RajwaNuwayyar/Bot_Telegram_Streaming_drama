-- ============================================================
-- SEED DATA: Drama Bot Telegram
-- Jalankan SETELAH schema_v5_final.sql berhasil dieksekusi
-- ============================================================

-- ============================================================
-- SEED: Kategori / Genre Drama
-- ============================================================
INSERT INTO categories (name, slug) VALUES
('Romantis',          'romantis'),
('Komedi',            'komedi'),
('Drama Keluarga',    'drama-keluarga'),
('Thriller',          'thriller'),
('Misteri',           'misteri'),
('Aksi',              'aksi'),
('Fantasi',           'fantasi'),
('Historis',          'historis'),
('Horor',             'horor'),
('Slice of Life',     'slice-of-life'),
('Olahraga',          'olahraga'),
('Bisnis',            'bisnis'),
('Medis',             'medis'),
('Hukum',             'hukum'),
('Remaja',            'remaja');


-- ============================================================
-- SEED: Paket VIP (1 Hari = Rp 3.000 sebagai acuan)
-- Harga sudah dikonfirmasi final oleh pemilik sistem.
-- ============================================================
INSERT INTO vip_plans (name, duration_days, price, sort_order) VALUES
('VIP 1 Hari',    1,   3000.00, 1),
('VIP 3 Hari',    3,   6000.00, 2),  -- bundling ~Rp2.000/hari
('VIP 7 Hari',    7,  10000.00, 3),  -- bundling ~Rp1.429/hari
('VIP 15 Hari',  15,  20000.00, 4),  -- bundling ~Rp1.333/hari
('VIP 30 Hari',  30,  35000.00, 5),  -- bundling ~Rp1.167/hari
('VIP 90 Hari',  90,  90000.00, 6),  -- bundling ~Rp1.000/hari
('VIP 365 Hari',365, 300000.00, 7);  -- bundling ~Rp822/hari


-- ============================================================
-- SEED: Level Affiliate
-- Level naik berdasarkan akumulasi total Rupiah transaksi VIP
-- dari orang-orang yang direferensikan (users.total_referral_value).
-- Level BISA TURUN jika ada pembatalan/refund.
-- ============================================================
INSERT INTO affiliate_levels (level_number, name, min_referral_value, commission_percent) VALUES
(1, 'Level 1',       0.00, 10.00),   -- mulai dari Rp0 (semua affiliate)
(2, 'Level 2', 1000000.00, 12.00),   -- setelah Rp1.000.000 total referral
(3, 'Level 3', 3000000.00, 15.00),   -- setelah Rp3.000.000 total referral
(4, 'Level 4', 5000000.00, 18.00);   -- setelah Rp5.000.000 total referral


-- ============================================================
-- SEED: Pengaturan Aplikasi
-- ============================================================
INSERT INTO app_settings (setting_key, setting_value, description) VALUES
('coin_to_rupiah_rate',             '1',     '1 coin = berapa Rupiah (ubah jika ada inflasi coin)'),
('affiliate_min_withdrawal_rupiah', '30000', 'Minimum penarikan saldo affiliate dalam Rupiah'),
('affiliate_withdrawal_fee_percent','1',     'Biaya penarikan saldo affiliate dalam persen (%)'),
('film_request_daily_limit',        '2',     'Batas jumlah request film per user per hari'),
('qris_payment_expiry_minutes',     '15',    'Batas waktu pembayaran QRIS dalam menit sebelum kadaluarsa'),
('bot_name',                        'DramaStream Bot', 'Nama bot Telegram (tampil di pesan dan notifikasi'),
('mini_app_url',                    '',      'URL Mini App Telegram (isi setelah deploy)');
