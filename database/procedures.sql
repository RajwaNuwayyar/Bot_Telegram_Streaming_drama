-- ============================================================
-- STORED PROCEDURES & FUNCTIONS: Drama Bot Telegram
-- Jalankan SETELAH schema_v5_final.sql dan seed_data.sql
-- ============================================================

DELIMITER //

-- ============================================================
-- FUNCTION: fn_is_vip(p_user_id)
-- Cek apakah user saat ini aktif VIP.
-- Menggantikan cek kolom is_vip yang sudah dihapus.
-- Penggunaan: SELECT fn_is_vip(123);
-- Return: TRUE jika VIP aktif, FALSE jika tidak
-- ============================================================
CREATE FUNCTION fn_is_vip(p_user_id BIGINT UNSIGNED)
RETURNS BOOLEAN
READS SQL DATA
NOT DETERMINISTIC  -- Hasilnya bergantung pada NOW() dan data tabel, bukan deterministik
COMMENT 'Cek apakah user sedang aktif VIP. Return TRUE/FALSE.'
BEGIN
    DECLARE v_vip_until DATETIME;
    SELECT vip_until INTO v_vip_until
    FROM users
    WHERE id = p_user_id;
    RETURN (v_vip_until IS NOT NULL AND v_vip_until > NOW());
END //


-- ============================================================
-- FUNCTION: fn_get_affiliate_level(p_total_referral_value)
-- Menentukan level affiliate berdasarkan total_referral_value.
-- Penggunaan: SELECT fn_get_affiliate_level(1500000);
-- Return: level_number (1, 2, 3, atau 4)
-- ============================================================
CREATE FUNCTION fn_get_affiliate_level(p_total_referral_value DECIMAL(14,2))
RETURNS INT UNSIGNED
READS SQL DATA
DETERMINISTIC
COMMENT 'Kembalikan level_number affiliate sesuai total_referral_value.'
BEGIN
    DECLARE v_level INT UNSIGNED DEFAULT 1;
    SELECT level_number INTO v_level
    FROM affiliate_levels
    WHERE min_referral_value <= p_total_referral_value
    ORDER BY min_referral_value DESC
    LIMIT 1;
    RETURN IFNULL(v_level, 1);
END //


-- ============================================================
-- PROCEDURE: sp_can_watch(p_user_id, p_episode_id, OUT can_watch, OUT reason)
-- Cek apakah user boleh menonton episode tertentu.
-- Ini adalah fungsi UTAMA yang harus dipakai bot dan Mini App
-- setiap kali user meminta akses ke sebuah episode.
--
-- Penggunaan:
--   CALL sp_can_watch(1, 42, @result, @reason);
--   SELECT @result, @reason;
--
-- Output:
--   can_watch = TRUE/FALSE
--   reason    = 'free_episode' | 'vip_active' | 'requires_vip' | 
--               'episode_not_found' | 'user_not_found' | 'drama_not_published'
-- ============================================================
CREATE PROCEDURE sp_can_watch(
    IN  p_user_id    BIGINT UNSIGNED,
    IN  p_episode_id BIGINT UNSIGNED,
    OUT can_watch    BOOLEAN,
    OUT reason       VARCHAR(50)
)
READS SQL DATA
COMMENT 'Cek akses user ke episode. Gunakan ini di bot dan Mini App.'
sp_block: BEGIN  -- [FIX Bug 1] Label dideklarasikan agar LEAVE sp_block valid
    DECLARE v_episode_number    INT UNSIGNED;
    DECLARE v_drama_id          BIGINT UNSIGNED;
    DECLARE v_free_count        INT UNSIGNED;
    DECLARE v_is_published      BOOLEAN;
    DECLARE v_user_vip_until    DATETIME;
    DECLARE v_user_is_banned    BOOLEAN;

    -- Ambil data episode + drama sekaligus
    SELECT
        e.episode_number,
        e.drama_id,
        d.free_episodes_count,
        d.is_published
    INTO
        v_episode_number,
        v_drama_id,
        v_free_count,
        v_is_published
    FROM episodes e
    JOIN dramas d ON d.id = e.drama_id
    WHERE e.id = p_episode_id;

    -- Episode tidak ditemukan
    IF v_episode_number IS NULL THEN
        SET can_watch = FALSE;
        SET reason = 'episode_not_found';
        LEAVE sp_block;
    END IF;

    -- Drama tidak dipublikasi
    IF v_is_published = FALSE THEN
        SET can_watch = FALSE;
        SET reason = 'drama_not_published';
        LEAVE sp_block;
    END IF;

    -- Ambil data user
    SELECT vip_until, is_banned
    INTO v_user_vip_until, v_user_is_banned
    FROM users
    WHERE id = p_user_id;

    -- User tidak ditemukan
    -- Catatan: cek IS NULL pada keduanya karena user non-VIP memiliki vip_until = NULL,
    -- namun is_banned tetap NOT NULL. Jika is_banned NULL berarti baris user memang tidak ada.
    IF v_user_is_banned IS NULL THEN
        SET can_watch = FALSE;
        SET reason = 'user_not_found';
        LEAVE sp_block;
    END IF;

    -- User di-ban
    IF v_user_is_banned = TRUE THEN
        SET can_watch = FALSE;
        SET reason = 'user_banned';
        LEAVE sp_block;
    END IF;

    -- Cek: apakah episode ini termasuk episode gratis?
    -- Gratis jika: free_episodes_count > 0 DAN episode_number <= free_episodes_count
    IF v_free_count > 0 AND v_episode_number <= v_free_count THEN
        SET can_watch = TRUE;
        SET reason = 'free_episode';
        LEAVE sp_block;
    END IF;

    -- Bukan episode gratis → cek VIP
    IF v_user_vip_until IS NOT NULL AND v_user_vip_until > NOW() THEN
        SET can_watch = TRUE;
        SET reason = 'vip_active';
    ELSE
        SET can_watch = FALSE;
        SET reason = 'requires_vip';
    END IF;

END //


-- ============================================================
-- PROCEDURE: sp_process_vip_payment(p_vip_purchase_id)
-- Dijalankan bot SETELAH menerima konfirmasi bayar dari gateway.
-- Melakukan:
--   1. Update vip_purchases.status = 'paid'
--   2. Hitung vip_start_at dan vip_end_at
--   3. Update users.vip_until
--   4. Aktifkan is_affiliate jika ini pembelian VIP pertama user
--   5. Buat referral_code jika belum ada
--   6. Proses komisi affiliate (jika user punya referrer)
--
-- Penggunaan:
--   CALL sp_process_vip_payment(99, @ok, @msg);
--   SELECT @ok, @msg;
-- ============================================================
CREATE PROCEDURE sp_process_vip_payment(
    IN  p_vip_purchase_id  BIGINT UNSIGNED,
    OUT p_success          BOOLEAN,
    OUT p_message          VARCHAR(255)
)
COMMENT 'Proses pembayaran VIP berhasil: update status, VIP until, dan komisi affiliate.'
proc_end: BEGIN  -- [FIX Bug 2] Label dideklarasikan agar LEAVE proc_end valid
    DECLARE v_user_id           BIGINT UNSIGNED;
    DECLARE v_plan_id           INT UNSIGNED;
    DECLARE v_amount            DECIMAL(12,2);
    DECLARE v_duration_days     INT UNSIGNED;
    DECLARE v_current_status    VARCHAR(20);
    DECLARE v_current_vip_until DATETIME;
    DECLARE v_new_vip_start     DATETIME;
    DECLARE v_new_vip_end       DATETIME;
    DECLARE v_is_affiliate      BOOLEAN;
    DECLARE v_referred_by       BIGINT UNSIGNED;
    DECLARE v_referrer_total    DECIMAL(14,2);
    DECLARE v_commission_pct    DECIMAL(5,2);
    DECLARE v_commission_coin   BIGINT;
    DECLARE v_coin_rate         INT;
    DECLARE v_first_vip         BOOLEAN DEFAULT FALSE;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_success = FALSE;
        SET p_message = 'Database error saat memproses pembayaran VIP.';
    END;

    START TRANSACTION;

    -- Ambil data transaksi (lock row)
    SELECT
        vp.user_id, vp.plan_id, vp.amount, vp.status,
        pl.duration_days
    INTO
        v_user_id, v_plan_id, v_amount, v_current_status,
        v_duration_days
    FROM vip_purchases vp
    JOIN vip_plans pl ON pl.id = vp.plan_id
    WHERE vp.id = p_vip_purchase_id
    FOR UPDATE;

    -- Validasi: hanya proses jika masih 'pending'
    IF v_current_status != 'pending' THEN
        ROLLBACK;
        SET p_success = FALSE;
        SET p_message = CONCAT('Transaksi sudah berstatus: ', v_current_status);
        LEAVE proc_end;
    END IF;

    -- Hitung periode VIP baru
    -- Jika user sudah VIP aktif, mulai dari vip_until; jika tidak, dari sekarang
    SELECT vip_until INTO v_current_vip_until
    FROM users WHERE id = v_user_id FOR UPDATE;

    IF v_current_vip_until IS NOT NULL AND v_current_vip_until > NOW() THEN
        SET v_new_vip_start = v_current_vip_until;                              -- stack dari akhir VIP sekarang
    ELSE
        SET v_new_vip_start = NOW();
    END IF;
    SET v_new_vip_end = DATE_ADD(v_new_vip_start, INTERVAL v_duration_days DAY);

    -- Update vip_purchases
    UPDATE vip_purchases
    SET status       = 'paid',
        vip_start_at = v_new_vip_start,
        vip_end_at   = v_new_vip_end,
        paid_at      = NOW()
    WHERE id = p_vip_purchase_id;

    -- Cek apakah ini pembelian VIP pertama user
    SELECT COUNT(*) = 1 INTO v_first_vip
    FROM vip_purchases
    WHERE user_id = v_user_id AND status = 'paid';

    -- Update users: vip_until, is_affiliate, referral_code
    IF v_first_vip = TRUE THEN
        UPDATE users
        SET vip_until     = v_new_vip_end,
            is_affiliate  = TRUE,
            referral_code = CONCAT('REF', id)
        WHERE id = v_user_id;
    ELSE
        UPDATE users
        SET vip_until = v_new_vip_end
        WHERE id = v_user_id;
    END IF;

    -- --------------------------------------------------------
    -- PROSES KOMISI AFFILIATE
    -- --------------------------------------------------------
    SELECT referred_by INTO v_referred_by
    FROM users WHERE id = v_user_id;

    IF v_referred_by IS NOT NULL THEN
        -- Ambil data referrer
        SELECT is_affiliate, total_referral_value
        INTO v_is_affiliate, v_referrer_total
        FROM users WHERE id = v_referred_by FOR UPDATE;

        IF v_is_affiliate = TRUE THEN
            -- Ambil coin rate dari app_settings
            SELECT CAST(setting_value AS UNSIGNED) INTO v_coin_rate
            FROM app_settings WHERE setting_key = 'coin_to_rupiah_rate';
            SET v_coin_rate = IFNULL(v_coin_rate, 1);

            -- Tentukan persen komisi sesuai level referrer SAAT INI
            SELECT commission_percent INTO v_commission_pct
            FROM affiliate_levels
            WHERE min_referral_value <= v_referrer_total
            ORDER BY min_referral_value DESC
            LIMIT 1;
            SET v_commission_pct = IFNULL(v_commission_pct, 10.00);

            -- Hitung coin komisi
            SET v_commission_coin = FLOOR(v_amount * v_commission_pct / 100 / v_coin_rate);

            -- Catat komisi
            INSERT INTO affiliate_commissions (
                referrer_user_id, referred_user_id, vip_purchase_id,
                transaction_amount, commission_percent, commission_coin, status
            ) VALUES (
                v_referred_by, v_user_id, p_vip_purchase_id,
                v_amount, v_commission_pct, v_commission_coin, 'active'
            );

            -- Update saldo dan total referral referrer
            UPDATE users
            SET coin_balance         = coin_balance + v_commission_coin,
                total_referral_value = total_referral_value + v_amount
            WHERE id = v_referred_by;
        END IF;
    END IF;

    COMMIT;
    SET p_success = TRUE;
    SET p_message = 'Pembayaran VIP berhasil diproses.';

END //


-- ============================================================
-- PROCEDURE: sp_reverse_affiliate_commission(p_vip_purchase_id)
-- Dijalankan admin saat membatalkan/refund sebuah transaksi VIP.
-- Melakukan:
--   1. Cek apakah ada komisi 'active' untuk transaksi ini
--   2. Update affiliate_commissions.status = 'reversed'
--   3. Kurangi users.total_referral_value
--   4. Kurangi users.coin_balance (tidak boleh minus — pakai GREATEST)
--
-- Penggunaan:
--   CALL sp_reverse_affiliate_commission(99, @ok, @msg);
--   SELECT @ok, @msg;
-- ============================================================
CREATE PROCEDURE sp_reverse_affiliate_commission(
    IN  p_vip_purchase_id BIGINT UNSIGNED,
    OUT p_success         BOOLEAN,
    OUT p_message         VARCHAR(255)
)
COMMENT 'Balik komisi affiliate saat transaksi VIP dibatalkan/refund.'
proc_end: BEGIN  -- [FIX Bug 3] Label dideklarasikan agar LEAVE proc_end valid
    DECLARE v_commission_id      BIGINT UNSIGNED;
    DECLARE v_referrer_id        BIGINT UNSIGNED;
    DECLARE v_commission_coin    BIGINT;
    DECLARE v_transaction_amount DECIMAL(12,2);
    DECLARE v_already_reversed   INT DEFAULT 0;  -- [FIX Inkonsistensi 1] ganti v_status dengan counter

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_success = FALSE;
        SET p_message = 'Database error saat membalik komisi affiliate.';
    END;

    START TRANSACTION;

    -- [FIX Inkonsistensi 1] Cek dulu apakah ada komisi yang sudah reversed
    -- (query terpisah agar v_already_reversed tidak NULL saat tidak ada baris)
    SELECT COUNT(*) INTO v_already_reversed
    FROM affiliate_commissions
    WHERE vip_purchase_id = p_vip_purchase_id
      AND status = 'reversed';

    -- Ambil data komisi yang masih active
    SELECT id, referrer_user_id, commission_coin, transaction_amount
    INTO v_commission_id, v_referrer_id, v_commission_coin, v_transaction_amount
    FROM affiliate_commissions
    WHERE vip_purchase_id = p_vip_purchase_id
      AND status = 'active'
    LIMIT 1
    FOR UPDATE;

    -- Tidak ada komisi aktif untuk transaksi ini
    IF v_commission_id IS NULL THEN
        ROLLBACK;
        SET p_success = FALSE;
        -- [FIX] Sekarang v_already_reversed pasti terisi (bukan NULL)
        IF v_already_reversed > 0 THEN
            SET p_message = 'Komisi untuk transaksi ini sudah pernah di-reverse sebelumnya.';
        ELSE
            SET p_message = 'Tidak ada komisi aktif untuk transaksi VIP ini (mungkin referral tidak ada).';
        END IF;
        LEAVE proc_end;
    END IF;

    -- Tandai komisi sebagai reversed
    UPDATE affiliate_commissions
    SET status      = 'reversed',
        reversed_at = NOW()
    WHERE id = v_commission_id;

    -- Kurangi saldo referrer (GREATEST memastikan tidak minus)
    UPDATE users
    SET coin_balance         = GREATEST(0, coin_balance - v_commission_coin),
        total_referral_value = GREATEST(0, total_referral_value - v_transaction_amount)
    WHERE id = v_referrer_id;

    COMMIT;
    SET p_success = TRUE;
    SET p_message = CONCAT('Komisi ', v_commission_coin, ' coin berhasil di-reverse dari user ID ', v_referrer_id, '.');

END //


-- ============================================================
-- PROCEDURE: sp_request_withdrawal(p_user_id, p_amount_coin,
--            p_bank_name, p_account_number, p_account_holder)
-- Dijalankan bot saat user mengajukan penarikan saldo.
-- Melakukan validasi: minimum withdrawal, saldo cukup, dll.
--
-- Penggunaan:
--   CALL sp_request_withdrawal(5, 50000, 'BCA', '1234567890',
--                              'Budi Santoso', @ok, @msg);
--   SELECT @ok, @msg;
-- ============================================================
CREATE PROCEDURE sp_request_withdrawal(
    IN  p_user_id          BIGINT UNSIGNED,
    IN  p_amount_coin      BIGINT,
    IN  p_bank_name        VARCHAR(50),
    IN  p_account_number   VARCHAR(50),
    IN  p_account_holder   VARCHAR(128),
    OUT p_success          BOOLEAN,
    OUT p_message          VARCHAR(255)
)
COMMENT 'Ajukan penarikan saldo coin affiliate ke rekening/e-wallet.'
proc_end: BEGIN  -- [FIX Bug 4] Label dideklarasikan agar LEAVE proc_end valid
    DECLARE v_coin_balance      BIGINT;
    DECLARE v_coin_rate         INT;
    DECLARE v_min_withdrawal    DECIMAL(12,2);
    DECLARE v_fee_pct           DECIMAL(5,2);
    DECLARE v_amount_rupiah     DECIMAL(12,2);
    DECLARE v_fee_amount        DECIMAL(12,2);
    DECLARE v_amount_after_fee  DECIMAL(12,2);

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_success = FALSE;
        SET p_message = 'Database error saat mengajukan penarikan.';
    END;

    -- Validasi jumlah coin
    IF p_amount_coin <= 0 THEN
        SET p_success = FALSE;
        SET p_message = 'Jumlah penarikan harus lebih dari 0.';
        LEAVE proc_end;
    END IF;

    -- Ambil setting
    SELECT CAST(setting_value AS UNSIGNED) INTO v_coin_rate
    FROM app_settings WHERE setting_key = 'coin_to_rupiah_rate';
    SET v_coin_rate = IFNULL(v_coin_rate, 1);

    SELECT CAST(setting_value AS DECIMAL(12,2)) INTO v_min_withdrawal
    FROM app_settings WHERE setting_key = 'affiliate_min_withdrawal_rupiah';
    SET v_min_withdrawal = IFNULL(v_min_withdrawal, 30000);

    SELECT CAST(setting_value AS DECIMAL(5,2)) INTO v_fee_pct
    FROM app_settings WHERE setting_key = 'affiliate_withdrawal_fee_percent';
    SET v_fee_pct = IFNULL(v_fee_pct, 1.00);

    -- Hitung Rupiah
    SET v_amount_rupiah = p_amount_coin * v_coin_rate;

    -- Validasi minimum withdrawal
    IF v_amount_rupiah < v_min_withdrawal THEN
        SET p_success = FALSE;
        SET p_message = CONCAT('Minimum penarikan Rp', FORMAT(v_min_withdrawal, 0, 'id_ID'),
                               '. Anda mencoba menarik Rp', FORMAT(v_amount_rupiah, 0, 'id_ID'), '.');
        LEAVE proc_end;
    END IF;

    START TRANSACTION;

    -- Ambil saldo user (lock)
    SELECT coin_balance INTO v_coin_balance
    FROM users WHERE id = p_user_id FOR UPDATE;

    -- Validasi saldo cukup
    IF v_coin_balance < p_amount_coin THEN
        ROLLBACK;
        SET p_success = FALSE;
        SET p_message = CONCAT('Saldo coin tidak cukup. Saldo saat ini: ', v_coin_balance, ' coin.');
        LEAVE proc_end;
    END IF;

    -- Hitung fee dan jumlah setelah fee
    SET v_fee_amount      = v_amount_rupiah * v_fee_pct / 100;
    SET v_amount_after_fee = v_amount_rupiah - v_fee_amount;

    -- Kurangi saldo user (CHECK constraint mencegah minus)
    UPDATE users
    SET coin_balance = coin_balance - p_amount_coin
    WHERE id = p_user_id;

    -- Buat record penarikan
    INSERT INTO affiliate_withdrawals (
        user_id, amount_coin, amount_rupiah, fee_percent, fee_amount,
        amount_after_fee, bank_or_ewallet_name, account_number, account_holder_name,
        status
    ) VALUES (
        p_user_id, p_amount_coin, v_amount_rupiah, v_fee_pct, v_fee_amount,
        v_amount_after_fee, p_bank_name, p_account_number, p_account_holder,
        'pending'
    );

    COMMIT;
    SET p_success = TRUE;
    SET p_message = CONCAT('Penarikan Rp', FORMAT(v_amount_after_fee, 0, 'id_ID'),
                           ' (setelah fee) berhasil diajukan dan menunggu proses admin.');

END //


-- ============================================================
-- PROCEDURE: sp_check_film_request_quota(p_user_id, OUT can_request, OUT remaining)
-- Cek apakah user masih bisa request film hari ini.
--
-- Penggunaan:
--   CALL sp_check_film_request_quota(7, @can, @remaining);
--   SELECT @can, @remaining;
-- ============================================================
CREATE PROCEDURE sp_check_film_request_quota(
    IN  p_user_id   BIGINT UNSIGNED,
    OUT can_request BOOLEAN,
    OUT remaining   INT
)
READS SQL DATA
COMMENT 'Cek apakah user masih bisa request film hari ini.'
BEGIN
    DECLARE v_daily_limit   INT DEFAULT 2;
    DECLARE v_used_today    INT DEFAULT 0;

    SELECT CAST(setting_value AS UNSIGNED) INTO v_daily_limit
    FROM app_settings WHERE setting_key = 'film_request_daily_limit';
    SET v_daily_limit = IFNULL(v_daily_limit, 2);

    SELECT COUNT(*) INTO v_used_today
    FROM film_requests
    WHERE user_id = p_user_id
      AND DATE(created_at) = CURDATE();

    SET remaining   = GREATEST(0, v_daily_limit - v_used_today);
    SET can_request = (v_used_today < v_daily_limit);
END //


DELIMITER ;
