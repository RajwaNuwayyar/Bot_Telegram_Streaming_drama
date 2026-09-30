-- ============================================================
-- SCHEDULED EVENTS (MySQL Event Scheduler)
-- Jalankan SETELAH schema_v5_final.sql dan procedures.sql
-- Pastikan Event Scheduler aktif: SET GLOBAL event_scheduler = ON;
-- ============================================================

DELIMITER //

-- ============================================================
-- EVENT 1: Expire transaksi QRIS yang tidak dibayar
-- Berjalan setiap 5 menit.
-- Menandai transaksi 'pending' sebagai 'expired' jika waktu
-- QRIS (qris_payments.expired_at) sudah lewat.
-- ============================================================
CREATE EVENT IF NOT EXISTS evt_expire_qris_payments
ON SCHEDULE EVERY 5 MINUTE
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    -- Update vip_purchases ke 'expired' jika QRIS-nya sudah kadaluarsa.
    -- CATATAN: setting 'qris_payment_expiry_minutes' di app_settings dibaca oleh BOT
    -- saat membuat transaksi QRIS (untuk mengisi qris_payments.expired_at).
    -- Event ini tidak perlu membaca setting tersebut — cukup bandingkan expired_at vs NOW().
    UPDATE vip_purchases vp
    JOIN qris_payments qp ON qp.vip_purchase_id = vp.id
    SET vp.status = 'expired'
    WHERE vp.status = 'pending'
      AND qp.gateway_status = 'pending'
      AND qp.expired_at <= NOW();
END //


-- ============================================================
-- EVENT 2: Sinkronisasi / Audit VIP kadaluarsa
-- Berjalan setiap 1 jam.
-- Memastikan users yang vip_until-nya sudah lewat tapi entah kenapa
-- masih ada di sistem sebagai VIP aktif sudah benar-benar expired.
-- (Ini safety net — normalnya bot yang update vip_until.)
-- Karena is_vip sudah dihapus, event ini hanya membersihkan
-- vip_until lama yang sudah lewat dengan cara mencatat audit.
--
-- NOTE: Tidak perlu update kolom apapun karena cek VIP kini
-- dilakukan via "vip_until > NOW()" di semua query dan fn_is_vip().
-- Event ini hanya mengupdate qris_payments yang tertinggal.
-- ============================================================
CREATE EVENT IF NOT EXISTS evt_hourly_cleanup
ON SCHEDULE EVERY 1 HOUR
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    -- Expire transaksi QRIS yang sudah melewati batas waktu
    -- (redundan dengan evt_expire_qris_payments, sebagai safety net)
    UPDATE vip_purchases vp
    JOIN qris_payments qp ON qp.vip_purchase_id = vp.id
    SET vp.status = 'expired'
    WHERE vp.status = 'pending'
      AND qp.expired_at <= NOW();

    -- Hapus search_logs yang lebih dari 90 hari (data analitik lama)
    DELETE FROM search_logs
    WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
END //


-- ============================================================
-- EVENT 3: Backup harian via stored procedure logging
-- Berjalan setiap hari pukul 02:00 WIB (UTC+7 → UTC 19:00)
-- Event ini membuat SUMMARY snapshot data ke tabel log_daily_summary
-- (opsional, membantu monitoring tanpa perlu cek tabel besar).
-- Backup file aktual (mysqldump) dihandle di level OS/cron.
-- ============================================================
-- CREATE TABLE IF NOT EXISTS log_daily_summary (
--     id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--     snapshot_date  DATE NOT NULL,
--     total_users    INT UNSIGNED,
--     active_vip     INT UNSIGNED,
--     total_dramas   INT UNSIGNED,
--     pending_requests INT UNSIGNED,
--     created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
--
-- CREATE EVENT IF NOT EXISTS evt_daily_summary
-- ON SCHEDULE EVERY 1 DAY
-- STARTS (CURRENT_DATE + INTERVAL 1 DAY + INTERVAL 19 HOUR)   -- 02:00 WIB next day
-- DO
-- BEGIN
--     INSERT INTO log_daily_summary (snapshot_date, total_users, active_vip, total_dramas, pending_requests)
--     SELECT
--         CURDATE(),
--         (SELECT COUNT(*) FROM users),
--         (SELECT COUNT(*) FROM users WHERE vip_until > NOW()),
--         (SELECT COUNT(*) FROM dramas WHERE is_published = TRUE),
--         (SELECT COUNT(*) FROM film_requests WHERE status = 'pending');
-- END //


DELIMITER ;
