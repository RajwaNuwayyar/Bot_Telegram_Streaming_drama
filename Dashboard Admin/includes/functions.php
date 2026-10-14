<?php
/**
 * Helper Functions & Data Providers untuk Dashboard Admin
 * Otomatis mengambil data live dari MySQL jika terhubung,
 * atau menyajikan Data Demo jika database offline atau belum terisi.
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Format angka ke mata uang Rupiah
 */
function formatRupiah($amount) {
    return 'Rp ' . number_format((float)$amount, 0, ',', '.');
}

/**
 * Format tanggal Indonesia
 */
function formatTanggalIndo($datetime, $withTime = true) {
    if (!$datetime) return '-';
    $timestamp = is_numeric($datetime) ? $datetime : strtotime($datetime);
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $d = date('d', $timestamp);
    $m = (int)date('m', $timestamp);
    $y = date('Y', $timestamp);
    $t = date('H:i', $timestamp);

    return $withTime ? "$d {$bulan[$m]} $y, $t WIB" : "$d {$bulan[$m]} $y";
}

/**
 * Time ago helper (cth: "5 menit yang lalu")
 */
function timeAgo($datetime) {
    if (!$datetime) return '-';
    $timestamp = is_numeric($datetime) ? $datetime : strtotime($datetime);
    $diff = time() - $timestamp;

    if ($diff < 60) return 'Baru saja';
    if ($diff < 3600) return floor($diff / 60) . ' menit lalu';
    if ($diff < 86400) return floor($diff / 3600) . ' jam lalu';
    if ($diff < 604800) return floor($diff / 86400) . ' hari lalu';
    return date('d M Y', $timestamp);
}

/**
 * Badge HTML untuk status pembayaran / VIP
 */
function getStatusBadge($status) {
    $status = strtolower($status);
    switch ($status) {
        case 'paid':
        case 'completed':
        case 'active':
        case 'vip':
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20"><i class="fas fa-check-circle mr-1 text-[10px]"></i> ' . ucfirst($status) . '</span>';
        case 'pending':
        case 'processing':
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20"><i class="fas fa-clock mr-1 text-[10px]"></i> ' . ucfirst($status) . '</span>';
        case 'expired':
        case 'failed':
        case 'rejected':
        case 'cancelled':
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20"><i class="fas fa-times-circle mr-1 text-[10px]"></i> ' . ucfirst($status) . '</span>';
        default:
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-500/10 text-slate-400 border border-slate-500/20">' . ucfirst($status) . '</span>';
    }
}

/**
 * Mengambil ringkasan data statistik Dashboard (Overview)
 */
function getDashboardStats() {
    $db = getDb();
    
    // Default fallback demo data
    $stats = [
        'total_revenue'       => 18750000,
        'revenue_this_month'  => 4820000,
        'revenue_growth'      => '+18.4%',
        'total_users'         => 3420,
        'users_growth'        => '+12.5%',
        'active_vip_users'    => 488,
        'vip_growth'          => '+24.1%',
        'total_transactions'  => 612,
        'pending_requests'    => 9,
        'pending_withdrawals' => 4,
        'is_live_data'        => false
    ];

    if ($db) {
        try {
            // Hitung total pendapatan dari vip_purchases berstatus paid
            $q1 = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM vip_purchases WHERE status = 'paid'");
            $totalRev = $q1->fetchColumn();

            // Hitung pendapatan bulan ini
            $q2 = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM vip_purchases WHERE status = 'paid' AND MONTH(paid_at) = MONTH(CURRENT_DATE()) AND YEAR(paid_at) = YEAR(CURRENT_DATE())");
            $monthRev = $q2->fetchColumn();

            // Hitung total user
            $q3 = $db->query("SELECT COUNT(*) FROM users");
            $totalUsers = $q3->fetchColumn();

            // Hitung user VIP aktif (vip_until > NOW())
            $q4 = $db->query("SELECT COUNT(*) FROM users WHERE vip_until IS NOT NULL AND vip_until > NOW()");
            $activeVip = $q4->fetchColumn();

            // Total transaksi VIP
            $q5 = $db->query("SELECT COUNT(*) FROM vip_purchases WHERE status = 'paid'");
            $totalTx = $q5->fetchColumn();

            // Hitung pending request
            $q6 = $db->query("SELECT COUNT(*) FROM film_requests WHERE status = 'pending'");
            $pendingReq = $q6->fetchColumn();

            // Hitung pending withdrawals
            $q7 = $db->query("SELECT COUNT(*) FROM affiliate_withdrawals WHERE status = 'pending'");
            $pendingWd = $q7->fetchColumn();

            // Jika database ada data dan berjalan, gunakan data database
            if ($totalUsers > 0 || $totalRev > 0) {
                $stats['total_revenue']       = (float)$totalRev;
                $stats['revenue_this_month']  = (float)$monthRev;
                $stats['total_users']         = (int)$totalUsers;
                $stats['active_vip_users']    = (int)$activeVip;
                $stats['total_transactions']  = (int)$totalTx;
                $stats['pending_requests']    = (int)$pendingReq;
                $stats['pending_withdrawals'] = (int)$pendingWd;
                $stats['is_live_data']        = true;
            }
        } catch (Exception $e) {
            // Tetap gunakan demo data jika query gagal / tabel belum ada
        }
    }

    return $stats;
}

/**
 * Mengambil transaksi VIP terbaru
 */
function getRecentVipPurchases($limit = 6) {
    $db = getDb();
    if ($db) {
        try {
            $sql = "SELECT vp.*, u.username, u.first_name, u.telegram_user_id, p.name as plan_name, qp.gateway_ref_id 
                    FROM vip_purchases vp
                    JOIN users u ON vp.user_id = u.id
                    LEFT JOIN vip_plans p ON vp.plan_id = p.id
                    LEFT JOIN qris_payments qp ON qp.vip_purchase_id = vp.id
                    ORDER BY vp.created_at DESC LIMIT :lim";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll();
            if (!empty($results)) {
                return $results;
            }
        } catch (Exception $e) {
            // fallback to demo
        }
    }

    // Demo data transaksi terbaru
    return [
        [
            'id' => 101,
            'username' => 'andipratama',
            'first_name' => 'Andi Pratama',
            'telegram_user_id' => '102938481',
            'plan_name' => 'VIP 30 Hari',
            'amount' => 35000,
            'payment_method' => 'QRIS',
            'status' => 'paid',
            'created_at' => date('Y-m-d H:i:s', strtotime('-15 minutes')),
            'paid_at' => date('Y-m-d H:i:s', strtotime('-14 minutes')),
            'gateway_ref_id' => 'QRIS-ORD-98214'
        ],
        [
            'id' => 102,
            'username' => 'citrakusuma',
            'first_name' => 'Citra Dewi',
            'telegram_user_id' => '592817261',
            'plan_name' => 'VIP 90 Hari (Hemat)',
            'amount' => 89000,
            'payment_method' => 'QRIS',
            'status' => 'paid',
            'created_at' => date('Y-m-d H:i:s', strtotime('-45 minutes')),
            'paid_at' => date('Y-m-d H:i:s', strtotime('-44 minutes')),
            'gateway_ref_id' => 'QRIS-ORD-98213'
        ],
        [
            'id' => 103,
            'username' => 'budi_santoso',
            'first_name' => 'Budi Santoso',
            'telegram_user_id' => '827162938',
            'plan_name' => 'VIP 7 Hari (Trial)',
            'amount' => 12000,
            'payment_method' => 'QRIS',
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
            'paid_at' => null,
            'gateway_ref_id' => 'QRIS-ORD-98212'
        ],
        [
            'id' => 104,
            'username' => 'dianaputri',
            'first_name' => 'Diana Putri',
            'telegram_user_id' => '671829304',
            'plan_name' => 'VIP 1 Tahun (Ultra)',
            'amount' => 249000,
            'payment_method' => 'QRIS',
            'status' => 'paid',
            'created_at' => date('Y-m-d H:i:s', strtotime('-3 hours')),
            'paid_at' => date('Y-m-d H:i:s', strtotime('-3 hours')),
            'gateway_ref_id' => 'QRIS-ORD-98211'
        ],
        [
            'id' => 105,
            'username' => 'reza_fauzi',
            'first_name' => 'Reza Fauzi',
            'telegram_user_id' => '449102837',
            'plan_name' => 'VIP 30 Hari',
            'amount' => 35000,
            'payment_method' => 'QRIS',
            'status' => 'expired',
            'created_at' => date('Y-m-d H:i:s', strtotime('-5 hours')),
            'paid_at' => null,
            'gateway_ref_id' => 'QRIS-ORD-98210'
        ],
        [
            'id' => 106,
            'username' => 'maya_sari',
            'first_name' => 'Maya Sari',
            'telegram_user_id' => '992817263',
            'plan_name' => 'VIP 30 Hari',
            'amount' => 35000,
            'payment_method' => 'QRIS',
            'status' => 'paid',
            'created_at' => date('Y-m-d H:i:s', strtotime('-7 hours')),
            'paid_at' => date('Y-m-d H:i:s', strtotime('-6 hours 58 minutes')),
            'gateway_ref_id' => 'QRIS-ORD-98209'
        ]
    ];
}

/**
 * Mengambil daftar seluruh Pelanggan Telegram
 */
function getTelegramUsersList($search = '', $filter = 'all', $limit = 50) {
    $db = getDb();
    if ($db) {
        try {
            $where = [];
            $params = [];

            if (!empty($search)) {
                $where[] = "(username LIKE :s OR first_name LIKE :s OR telegram_user_id LIKE :s)";
                $params[':s'] = "%$search%";
            }

            if ($filter === 'vip') {
                $where[] = "vip_until IS NOT NULL AND vip_until > NOW()";
            } elseif ($filter === 'regular') {
                $where[] = "(vip_until IS NULL OR vip_until <= NOW())";
            } elseif ($filter === 'affiliate') {
                $where[] = "is_affiliate = 1";
            } elseif ($filter === 'banned') {
                $where[] = "is_banned = 1";
            }

            $sql = "SELECT * FROM users";
            if (!empty($where)) {
                $sql .= " WHERE " . implode(' AND ', $where);
            }
            $sql .= " ORDER BY created_at DESC LIMIT " . (int)$limit;

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $users = $stmt->fetchAll();

            if (!empty($users)) {
                return $users;
            }
        } catch (Exception $e) {
            // fallback
        }
    }

    // Demo Data Pengguna Masuk
    $demoUsers = [
        [
            'id' => 1,
            'telegram_user_id' => '102938481',
            'username' => 'andipratama',
            'first_name' => 'Andi',
            'last_name' => 'Pratama',
            'language_code' => 'id',
            'vip_until' => date('Y-m-d H:i:s', strtotime('+28 days')),
            'is_banned' => 0,
            'is_affiliate' => 1,
            'referral_code' => 'ANDI88',
            'coin_balance' => 45000,
            'total_referral_value' => 350000,
            'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            'last_active_at' => date('Y-m-d H:i:s', strtotime('-10 minutes'))
        ],
        [
            'id' => 2,
            'telegram_user_id' => '592817261',
            'username' => 'citrakusuma',
            'first_name' => 'Citra',
            'last_name' => 'Dewi',
            'language_code' => 'id',
            'vip_until' => date('Y-m-d H:i:s', strtotime('+85 days')),
            'is_banned' => 0,
            'is_affiliate' => 1,
            'referral_code' => 'CITRA77',
            'coin_balance' => 120000,
            'total_referral_value' => 890000,
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 week')),
            'last_active_at' => date('Y-m-d H:i:s', strtotime('-30 minutes'))
        ],
        [
            'id' => 3,
            'telegram_user_id' => '827162938',
            'username' => 'budi_santoso',
            'first_name' => 'Budi',
            'last_name' => 'Santoso',
            'language_code' => 'id',
            'vip_until' => null, // Free
            'is_banned' => 0,
            'is_affiliate' => 0,
            'referral_code' => null,
            'coin_balance' => 0,
            'total_referral_value' => 0,
            'created_at' => date('Y-m-d H:i:s', strtotime('-3 hours')),
            'last_active_at' => date('Y-m-d H:i:s', strtotime('-15 minutes'))
        ],
        [
            'id' => 4,
            'telegram_user_id' => '671829304',
            'username' => 'dianaputri',
            'first_name' => 'Diana',
            'last_name' => 'Putri',
            'language_code' => 'id',
            'vip_until' => date('Y-m-d H:i:s', strtotime('+350 days')),
            'is_banned' => 0,
            'is_affiliate' => 1,
            'referral_code' => 'DIANAP',
            'coin_balance' => 85000,
            'total_referral_value' => 520000,
            'created_at' => date('Y-m-d H:i:s', strtotime('-2 weeks')),
            'last_active_at' => date('Y-m-d H:i:s', strtotime('-1 hour'))
        ],
        [
            'id' => 5,
            'telegram_user_id' => '449102837',
            'username' => 'reza_fauzi',
            'first_name' => 'Reza',
            'last_name' => 'Fauzi',
            'language_code' => 'id',
            'vip_until' => null,
            'is_banned' => 0,
            'is_affiliate' => 0,
            'referral_code' => null,
            'coin_balance' => 0,
            'total_referral_value' => 0,
            'created_at' => date('Y-m-d H:i:s', strtotime('-5 hours')),
            'last_active_at' => date('Y-m-d H:i:s', strtotime('-2 hours'))
        ],
        [
            'id' => 6,
            'telegram_user_id' => '992817263',
            'username' => 'maya_sari',
            'first_name' => 'Maya',
            'last_name' => 'Sari',
            'language_code' => 'id',
            'vip_until' => date('Y-m-d H:i:s', strtotime('+22 days')),
            'is_banned' => 0,
            'is_affiliate' => 1,
            'referral_code' => 'MAYA88',
            'coin_balance' => 30000,
            'total_referral_value' => 210000,
            'created_at' => date('Y-m-d H:i:s', strtotime('-8 days')),
            'last_active_at' => date('Y-m-d H:i:s', strtotime('-4 hours'))
        ],
        [
            'id' => 7,
            'telegram_user_id' => '119283746',
            'username' => 'spam_account_99',
            'first_name' => 'SpamBot',
            'last_name' => 'Test',
            'language_code' => 'en',
            'vip_until' => null,
            'is_banned' => 1,
            'is_affiliate' => 0,
            'referral_code' => null,
            'coin_balance' => 0,
            'total_referral_value' => 0,
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 month')),
            'last_active_at' => date('Y-m-d H:i:s', strtotime('-20 days'))
        ]
    ];

    // Filter array jika mode demo
    if (!empty($search)) {
        $demoUsers = array_filter($demoUsers, function($u) use ($search) {
            return stripos($u['username'], $search) !== false ||
                   stripos($u['first_name'], $search) !== false ||
                   stripos($u['telegram_user_id'], $search) !== false;
        });
    }

    if ($filter === 'vip') {
        $demoUsers = array_filter($demoUsers, fn($u) => !empty($u['vip_until']) && strtotime($u['vip_until']) > time());
    } elseif ($filter === 'regular') {
        $demoUsers = array_filter($demoUsers, fn($u) => empty($u['vip_until']) || strtotime($u['vip_until']) <= time());
    } elseif ($filter === 'affiliate') {
        $demoUsers = array_filter($demoUsers, fn($u) => $u['is_affiliate'] == 1);
    } elseif ($filter === 'banned') {
        $demoUsers = array_filter($demoUsers, fn($u) => $u['is_banned'] == 1);
    }

    return array_values($demoUsers);
}

/**
 * Data Katalog Drama
 */
function getDramasCatalog() {
    $db = getDb();
    if ($db) {
        try {
            $stmt = $db->query("SELECT d.*, 
                (SELECT COUNT(*) FROM episodes WHERE drama_id = d.id) as ep_count,
                (SELECT GROUP_CONCAT(c.name SEPARATOR ', ') FROM categories c JOIN drama_categories dc ON dc.category_id = c.id WHERE dc.drama_id = d.id) as categories
                FROM dramas d ORDER BY d.created_at DESC");
            $res = $stmt->fetchAll();
            if (!empty($res)) return $res;
        } catch (Exception $e) {}
    }

    return [
        [
            'id' => 1,
            'title' => 'Queen of Tears',
            'slug' => 'queen-of-tears',
            'description' => 'Kisah cinta ajaib dan romantis antara pasangan suami istri yang menghadapi krisis perkawinan rumit.',
            'poster_url' => 'https://images.unsplash.com/photo-1578632767115-351597cf2477?auto=format&fit=crop&w=400&q=80',
            'country_origin' => 'Korea Selatan',
            'release_year' => 2024,
            'total_episodes' => 16,
            'free_episodes_count' => 1,
            'status' => 'completed',
            'views_count' => 14520,
            'categories' => 'Romance, Drama, Comedy',
            'is_published' => 1
        ],
        [
            'id' => 2,
            'title' => 'Marry My Husband',
            'slug' => 'marry-my-husband',
            'description' => 'Seorang wanita yang sakit parah dikhianati dan dibunuh oleh suaminya dan sahabatnya, namun kembali ke masa 10 tahun lalu.',
            'poster_url' => 'https://images.unsplash.com/photo-1536440136628-849c177e76a1?auto=format&fit=crop&w=400&q=80',
            'country_origin' => 'Korea Selatan',
            'release_year' => 2024,
            'total_episodes' => 16,
            'free_episodes_count' => 2,
            'status' => 'completed',
            'views_count' => 18940,
            'categories' => 'Fantasy, Revenge, Romance',
            'is_published' => 1
        ],
        [
            'id' => 3,
            'title' => 'Hidden Love',
            'slug' => 'hidden-love',
            'description' => 'Sang Zhi jatuh cinta pada Duan Jia Xu, teman kakak laki-lakinya yang sering mengunjungi rumahnya saat remaja.',
            'poster_url' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=400&q=80',
            'country_origin' => 'China',
            'release_year' => 2023,
            'total_episodes' => 25,
            'free_episodes_count' => 1,
            'status' => 'completed',
            'views_count' => 21350,
            'categories' => 'Romance, Youth, School',
            'is_published' => 1
        ],
        [
            'id' => 4,
            'title' => 'Lovely Runner',
            'slug' => 'lovely-runner',
            'description' => 'Penggemar berat idol ternama melakukan perjalanan waktu untuk menyelamatkan sang idola dari nasib tragis.',
            'poster_url' => 'https://images.unsplash.com/photo-1485846234645-a62644f84728?auto=format&fit=crop&w=400&q=80',
            'country_origin' => 'Korea Selatan',
            'release_year' => 2024,
            'total_episodes' => 16,
            'free_episodes_count' => 1,
            'status' => 'ongoing',
            'views_count' => 12400,
            'categories' => 'Fantasy, Romance, Time Travel',
            'is_published' => 1
        ]
    ];
}

/**
 * Data Penarikan Saldo Affiliate
 */
function getAffiliateWithdrawalsList() {
    $db = getDb();
    if ($db) {
        try {
            $sql = "SELECT aw.*, u.username, u.first_name, u.telegram_user_id 
                    FROM affiliate_withdrawals aw
                    JOIN users u ON aw.user_id = u.id
                    ORDER BY aw.requested_at DESC";
            $res = $db->query($sql)->fetchAll();
            if (!empty($res)) return $res;
        } catch (Exception $e) {}
    }

    return [
        [
            'id' => 1,
            'username' => 'citrakusuma',
            'first_name' => 'Citra Dewi',
            'telegram_user_id' => '592817261',
            'amount_coin' => 100000,
            'amount_rupiah' => 100000,
            'fee_percent' => 5,
            'fee_amount' => 5000,
            'amount_after_fee' => 95000,
            'bank_or_ewallet_name' => 'DANA',
            'account_number' => '081234567890',
            'account_holder_name' => 'Citra Dewi',
            'status' => 'pending',
            'requested_at' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            'processed_at' => null
        ],
        [
            'id' => 2,
            'username' => 'dianaputri',
            'first_name' => 'Diana Putri',
            'telegram_user_id' => '671829304',
            'amount_coin' => 50000,
            'amount_rupiah' => 50000,
            'fee_percent' => 5,
            'fee_amount' => 2500,
            'amount_after_fee' => 47500,
            'bank_or_ewallet_name' => 'BCA',
            'account_number' => '8271928371',
            'account_holder_name' => 'Diana Putri',
            'status' => 'pending',
            'requested_at' => date('Y-m-d H:i:s', strtotime('-4 hours')),
            'processed_at' => null
        ],
        [
            'id' => 3,
            'username' => 'andipratama',
            'first_name' => 'Andi Pratama',
            'telegram_user_id' => '102938481',
            'amount_coin' => 250000,
            'amount_rupiah' => 250000,
            'fee_percent' => 5,
            'fee_amount' => 12500,
            'amount_after_fee' => 237500,
            'bank_or_ewallet_name' => 'GoPay',
            'account_number' => '085712348765',
            'account_holder_name' => 'Andi Pratama',
            'status' => 'completed',
            'requested_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            'processed_at' => date('Y-m-d H:i:s', strtotime('-2 days +1 hour'))
        ]
    ];
}

/**
 * Data Request Film dari Pelanggan
 */
function getFilmRequestsList() {
    $db = getDb();
    if ($db) {
        try {
            $sql = "SELECT fr.*, u.username, u.first_name, u.telegram_user_id 
                    FROM film_requests fr
                    JOIN users u ON fr.user_id = u.id
                    ORDER BY fr.is_priority DESC, fr.created_at DESC";
            $res = $db->query($sql)->fetchAll();
            if (!empty($res)) return $res;
        } catch (Exception $e) {}
    }

    return [
        [
            'id' => 1,
            'username' => 'andipratama',
            'first_name' => 'Andi Pratama',
            'telegram_user_id' => '102938481',
            'requested_title' => 'Chief Detective 1958',
            'source_app' => 'Disney+ / MBC',
            'notes' => 'Tolong min tambahin yang episode 9-10 sub Indo ya',
            'is_priority' => 1, // VIP User Priority
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours'))
        ],
        [
            'id' => 2,
            'username' => 'budi_santoso',
            'first_name' => 'Budi Santoso',
            'telegram_user_id' => '827162938',
            'requested_title' => 'The Atypical Family',
            'source_app' => 'JTBC / Netflix',
            'notes' => 'Drama baru Jang Ki Yong',
            'is_priority' => 0,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s', strtotime('-5 hours'))
        ],
        [
            'id' => 3,
            'username' => 'citrakusuma',
            'first_name' => 'Citra Dewi',
            'telegram_user_id' => '592817261',
            'requested_title' => 'Crash Landing on You',
            'source_app' => 'tvN',
            'notes' => 'Pengen rewatch versi resolusi 1080p jernih',
            'is_priority' => 1,
            'status' => 'completed',
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
        ]
    ];
}
