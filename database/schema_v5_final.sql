-- ============================================================
-- SKEMA DATABASE FINAL v5 (MySQL 8+): Bot Drama Telegram + Mini App
-- Berdasarkan: drama_bot_schema_v4_final.sql
-- Versi 5 — Perubahan dari v4 (berdasarkan konfirmasi):
--   [A3] HAPUS kolom users.is_vip → cek VIP via: vip_until > NOW()
--   [A4] TAMBAH kolom affiliate_commissions.status (active/reversed)
--        untuk mendukung pembatalan komisi saat refund VIP
--   [A5] TAMBAH CHECK (coin_balance >= 0) di tabel users
--   [A6] users.vip_until selalu sinkron dengan vip_purchases.vip_end_at
--        (dihandle oleh bot/aplikasi, bukan trigger DB)
-- ============================================================
-- RINGKASAN ATURAN BISNIS:
-- 1. Episode gratis: dramas.free_episodes_count menentukan batas.
--    0 = seluruh drama wajib VIP. Default 1 = ep.1 gratis.
-- 2. is_vip DIHAPUS. Cek VIP: vip_until IS NOT NULL AND vip_until > NOW()
-- 3. Affiliate aktif setelah pernah VIP (bot set is_affiliate=TRUE).
-- 4. Komisi = COIN (1 coin = Rp1, rate dari app_settings).
-- 5. Level affiliate dari total_referral_value; bisa NAIK dan TURUN.
-- 6. Refund: commission reversed, coin_balance dikurangi (min 0).
-- 7. Request Film: max 2/hari/user (cek di level bot).
-- ============================================================

SET NAMES utf8mb4;
SET time_zone = '+07:00';

-- Aktifkan Event Scheduler
SET GLOBAL event_scheduler = ON;

-- ============================================================
-- DROP TABLES (urutan terbalik karena FK dependency)
-- Hapus komentar di bawah ini jika ingin reset ulang database
-- ============================================================
-- DROP TABLE IF EXISTS search_logs;
-- DROP TABLE IF EXISTS app_settings;
-- DROP TABLE IF EXISTS film_requests;
-- DROP TABLE IF EXISTS affiliate_withdrawals;
-- DROP TABLE IF EXISTS affiliate_commissions;
-- DROP TABLE IF EXISTS affiliate_levels;
-- DROP TABLE IF EXISTS qris_payments;
-- DROP TABLE IF EXISTS vip_purchases;
-- DROP TABLE IF EXISTS vip_plans;
-- DROP TABLE IF EXISTS favorites;
-- DROP TABLE IF EXISTS watch_history;
-- DROP TABLE IF EXISTS episodes;
-- DROP TABLE IF EXISTS drama_categories;
-- DROP TABLE IF EXISTS dramas;
-- DROP TABLE IF EXISTS categories;
-- DROP TABLE IF EXISTS users;


-- ============================================================
-- 1. USERS
-- ============================================================
CREATE TABLE users (
    id                    BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    telegram_user_id      BIGINT UNSIGNED NOT NULL UNIQUE,
    username              VARCHAR(64),
    first_name            VARCHAR(128),
    last_name             VARCHAR(128),
    language_code         VARCHAR(8)      DEFAULT 'id',

    -- [v5] TIDAK ADA kolom is_vip — status VIP aktif cek via:
    --   vip_until IS NOT NULL AND vip_until > NOW()
    -- vip_until diupdate bot setelah vip_purchases berstatus 'paid'.
    vip_until             DATETIME        NULL,

    is_banned             BOOLEAN         NOT NULL DEFAULT FALSE,

    -- Program Affiliate
    is_affiliate          BOOLEAN         NOT NULL DEFAULT FALSE,
    referral_code         VARCHAR(20)     UNIQUE NULL,
    referred_by           BIGINT UNSIGNED NULL,              -- id (PK) user yang mereferensikan
    coin_balance          BIGINT          NOT NULL DEFAULT 0 CHECK (coin_balance >= 0),  -- [v5] CHECK constraint
    total_referral_value  DECIMAL(14,2)   NOT NULL DEFAULT 0,

    created_at            DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_active_at        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (referred_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_users_vip_until   (vip_until),
    INDEX idx_users_referred_by (referred_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 2. KATEGORI / GENRE
-- ============================================================
CREATE TABLE categories (
    id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name  VARCHAR(64) NOT NULL UNIQUE,
    slug  VARCHAR(64) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 3. DRAMA (judul / series)
-- ============================================================
CREATE TABLE dramas (
    id                    BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title                 VARCHAR(255)    NOT NULL,
    slug                  VARCHAR(255)    NOT NULL UNIQUE,
    description           TEXT,
    poster_url            TEXT,
    country_origin        VARCHAR(64),
    release_year          SMALLINT,
    total_episodes        INT UNSIGNED    NOT NULL DEFAULT 0,
    -- 0 = seluruh drama wajib VIP; N = N episode pertama gratis
    free_episodes_count   INT UNSIGNED    NOT NULL DEFAULT 1,
    status                VARCHAR(20)     NOT NULL DEFAULT 'ongoing',  -- ongoing|completed|hiatus
    license_source        VARCHAR(255),
    license_expires_at    DATETIME        NULL,
    views_count           BIGINT UNSIGNED NOT NULL DEFAULT 0,
    is_published          BOOLEAN         NOT NULL DEFAULT TRUE,
    created_at            DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at            DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_dramas_published (is_published, created_at),
    INDEX idx_dramas_views     (views_count)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE drama_categories (
    drama_id     BIGINT UNSIGNED NOT NULL,
    category_id  INT UNSIGNED    NOT NULL,
    PRIMARY KEY  (drama_id, category_id),
    FOREIGN KEY  (drama_id)    REFERENCES dramas(id)     ON DELETE CASCADE,
    FOREIGN KEY  (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 4. EPISODES (1 baris = 1 postingan di channel Telegram privat)
-- ============================================================
CREATE TABLE episodes (
    id                   BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    drama_id             BIGINT UNSIGNED NOT NULL,
    episode_number       INT UNSIGNED    NOT NULL,
    title                VARCHAR(255),
    telegram_channel_id  BIGINT          NOT NULL,
    telegram_message_id  BIGINT          NOT NULL,
    telegram_file_id     TEXT,
    thumbnail_url        TEXT,
    duration_seconds     INT UNSIGNED,
    views_count          BIGINT UNSIGNED NOT NULL DEFAULT 0,
    created_at           DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uniq_drama_episode (drama_id, episode_number),
    FOREIGN KEY (drama_id) REFERENCES dramas(id) ON DELETE CASCADE,
    INDEX idx_episodes_drama (drama_id, episode_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 5. RIWAYAT TONTON (Library > History)
-- ============================================================
CREATE TABLE watch_history (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id             BIGINT UNSIGNED NOT NULL,
    episode_id          BIGINT UNSIGNED NOT NULL,
    progress_seconds    INT UNSIGNED    NOT NULL DEFAULT 0,
    is_completed        BOOLEAN         NOT NULL DEFAULT FALSE,
    last_watched_at     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uniq_user_episode (user_id, episode_id),
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (episode_id) REFERENCES episodes(id) ON DELETE CASCADE,
    INDEX idx_watch_history_user (user_id, last_watched_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 6. FAVORIT / BOOKMARK (Library > Bookmark)
-- ============================================================
CREATE TABLE favorites (
    user_id     BIGINT UNSIGNED NOT NULL,
    drama_id    BIGINT UNSIGNED NOT NULL,
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (user_id, drama_id),
    FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
    FOREIGN KEY (drama_id) REFERENCES dramas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 7. PAKET VIP
-- ============================================================
CREATE TABLE vip_plans (
    id             INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    name           VARCHAR(50)   NOT NULL,
    duration_days  INT UNSIGNED  NOT NULL,
    price          DECIMAL(12,2) NOT NULL,
    is_active      BOOLEAN       NOT NULL DEFAULT TRUE,
    sort_order     INT           NOT NULL DEFAULT 0,
    created_at     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 8. PEMBELIAN VIP
-- ============================================================
CREATE TABLE vip_purchases (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    plan_id         INT UNSIGNED    NOT NULL,
    amount          DECIMAL(12,2)   NOT NULL,
    payment_method  VARCHAR(30)     NOT NULL DEFAULT 'qris',
    status          VARCHAR(20)     NOT NULL DEFAULT 'pending',  -- pending|paid|expired|cancelled|failed
    -- Bot mengisi vip_start_at dan vip_end_at saat status = 'paid'
    -- Bot juga mengupdate users.vip_until = vip_end_at
    vip_start_at    DATETIME        NULL,
    vip_end_at      DATETIME        NULL,
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    paid_at         DATETIME        NULL,

    FOREIGN KEY (user_id) REFERENCES users(id)      ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES vip_plans(id),
    INDEX idx_vip_purchases_user   (user_id, status),
    INDEX idx_vip_purchases_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 9. DETAIL PEMBAYARAN QRIS
-- ============================================================
CREATE TABLE qris_payments (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vip_purchase_id     BIGINT UNSIGNED NOT NULL UNIQUE,
    gateway_name        VARCHAR(50)     NOT NULL,
    gateway_ref_id      VARCHAR(100)    NOT NULL,
    qris_payload        TEXT,
    qris_image_url      TEXT,
    gateway_status      VARCHAR(30)     NOT NULL DEFAULT 'pending',
    amount_paid         DECIMAL(12,2)   NULL,
    expired_at          DATETIME        NOT NULL,
    paid_at             DATETIME        NULL,
    callback_payload    JSON,
    created_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (vip_purchase_id) REFERENCES vip_purchases(id) ON DELETE CASCADE,
    INDEX idx_qris_gateway_ref (gateway_ref_id),
    INDEX idx_qris_status      (gateway_status, expired_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 10. LEVEL AFFILIATE
-- ============================================================
CREATE TABLE affiliate_levels (
    id                   INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    level_number         INT UNSIGNED  NOT NULL UNIQUE,
    name                 VARCHAR(50)   NOT NULL,
    min_referral_value   DECIMAL(14,2) NOT NULL,
    commission_percent   DECIMAL(5,2)  NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 11. KOMISI AFFILIATE (ledger)
-- ============================================================
CREATE TABLE affiliate_commissions (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    referrer_user_id    BIGINT UNSIGNED NOT NULL,
    referred_user_id    BIGINT UNSIGNED NOT NULL,
    vip_purchase_id     BIGINT UNSIGNED NOT NULL,
    transaction_amount  DECIMAL(12,2)   NOT NULL,
    commission_percent  DECIMAL(5,2)    NOT NULL,
    commission_coin     BIGINT          NOT NULL,
    -- [v5] Kolom baru untuk refund support:
    status              VARCHAR(20)     NOT NULL DEFAULT 'active',  -- active | reversed
    reversed_at         DATETIME        NULL,
    created_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (referrer_user_id) REFERENCES users(id)         ON DELETE CASCADE,
    FOREIGN KEY (referred_user_id) REFERENCES users(id)         ON DELETE CASCADE,
    FOREIGN KEY (vip_purchase_id)  REFERENCES vip_purchases(id) ON DELETE CASCADE,
    INDEX idx_affiliate_commissions_referrer (referrer_user_id, created_at),
    INDEX idx_affiliate_commissions_purchase (vip_purchase_id),
    INDEX idx_affiliate_commissions_status   (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 12. PENARIKAN SALDO AFFILIATE
-- ============================================================
CREATE TABLE affiliate_withdrawals (
    id                   BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id              BIGINT UNSIGNED NOT NULL,
    amount_coin          BIGINT          NOT NULL,
    amount_rupiah        DECIMAL(12,2)   NOT NULL,
    fee_percent          DECIMAL(5,2)    NOT NULL,
    fee_amount           DECIMAL(12,2)   NOT NULL,
    amount_after_fee     DECIMAL(12,2)   NOT NULL,
    bank_or_ewallet_name VARCHAR(50)     NOT NULL,
    account_number       VARCHAR(50)     NOT NULL,
    account_holder_name  VARCHAR(128)    NOT NULL,
    status               VARCHAR(20)     NOT NULL DEFAULT 'pending',  -- pending|processing|completed|rejected
    requested_at         DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    processed_at         DATETIME        NULL,
    admin_notes          VARCHAR(255),

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_affiliate_withdrawals_user   (user_id, status),
    INDEX idx_affiliate_withdrawals_status (status, requested_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 13. REQUEST FILM
-- ============================================================
CREATE TABLE film_requests (
    id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id          BIGINT UNSIGNED NOT NULL,
    requested_title  VARCHAR(255)    NOT NULL,
    source_app       VARCHAR(100)    NULL,
    notes            TEXT,
    is_priority      BOOLEAN         NOT NULL DEFAULT FALSE,
    status           VARCHAR(20)     NOT NULL DEFAULT 'pending',  -- pending|processing|completed|rejected
    linked_drama_id  BIGINT UNSIGNED NULL,
    admin_notes      VARCHAR(255),
    created_at       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    processed_at     DATETIME        NULL,

    FOREIGN KEY (user_id)         REFERENCES users(id)  ON DELETE CASCADE,
    FOREIGN KEY (linked_drama_id) REFERENCES dramas(id) ON DELETE SET NULL,
    INDEX idx_film_requests_queue     (status, is_priority, created_at),
    INDEX idx_film_requests_user_date (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 14. PENGATURAN APLIKASI (key-value)
-- ============================================================
CREATE TABLE app_settings (
    setting_key    VARCHAR(64)  PRIMARY KEY,
    setting_value  VARCHAR(255) NOT NULL,
    description    VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 15. LOG PENCARIAN
-- ============================================================
CREATE TABLE search_logs (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id      BIGINT UNSIGNED NULL,
    query_text   VARCHAR(255)    NOT NULL,
    result_count INT UNSIGNED    NULL,
    created_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_search_logs_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
