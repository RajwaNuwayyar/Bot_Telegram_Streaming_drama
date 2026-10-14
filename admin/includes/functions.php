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
    $status = strtolower($status ?? '');
    switch ($status) {
        case 'paid':
        case 'completed':
        case 'active':
        case 'vip':
        case 'terkirim':
            $label = ($status === 'terkirim') ? 'Terkirim' : ucfirst($status);
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20"><i class="fas fa-check-circle mr-1 text-[10px]"></i> ' . $label . '</span>';
        case 'pending':
        case 'processing':
        case 'diproses':
            $label = ($status === 'diproses') ? 'Diproses' : ucfirst($status);
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20"><i class="fas fa-clock mr-1 text-[10px]"></i> ' . $label . '</span>';
        case 'expired':
        case 'failed':
        case 'rejected':
        case 'ditolak':
        case 'cancelled':
            $label = ($status === 'rejected' || $status === 'ditolak') ? 'Ditolak' : ucfirst($status);
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20"><i class="fas fa-times-circle mr-1 text-[10px]"></i> ' . $label . '</span>';
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

            // Hitung pending withdrawals (status pending atau diproses)
            $q7 = $db->query("SELECT COUNT(*) FROM affiliate_withdrawals WHERE status IN ('pending', 'diproses')");
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

                // Hitung pertumbuhan omset dibanding bulan lalu
                $qPrevMonth = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM vip_purchases WHERE status = 'paid' AND MONTH(paid_at) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)) AND YEAR(paid_at) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))");
                $prevMonthRev = (float)$qPrevMonth->fetchColumn();
                if ($prevMonthRev > 0) {
                    $growthRev = (($monthRev - $prevMonthRev) / $prevMonthRev) * 100;
                    $stats['revenue_growth'] = ($growthRev >= 0 ? '+' : '') . number_format($growthRev, 1) . '%';
                } else {
                    $stats['revenue_growth'] = $monthRev > 0 ? '+100%' : '0%';
                }

                // Hitung pertumbuhan user baru bulan ini
                $qUsersThisMonth = $db->query("SELECT COUNT(*) FROM users WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
                $usersThisMonth = (int)$qUsersThisMonth->fetchColumn();
                $stats['users_growth'] = '+' . $usersThisMonth . ' user baru';

                // Hitung transaksi VIP bulan ini
                $qVipThisMonth = $db->query("SELECT COUNT(*) FROM vip_purchases WHERE status = 'paid' AND MONTH(paid_at) = MONTH(CURRENT_DATE()) AND YEAR(paid_at) = YEAR(CURRENT_DATE())");
                $vipThisMonth = (int)$qVipThisMonth->fetchColumn();
                $stats['vip_growth'] = '+' . $vipThisMonth . ' bulan ini';
            }
        } catch (Exception $e) {
            // Tetap gunakan demo data jika query gagal / tabel belum ada
        }
    }

    return $stats;
}

/**
 * Mengambil data tren pendapatan bulanan riil untuk grafik Chart.js
 */
function getMonthlyRevenueTrend($numMonths = 6) {
    $db = getDb();
    $bulanIndo = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
        7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
    ];

    $result = [
        'labels' => [],
        'data'   => []
    ];

    $monthsMap = [];
    for ($i = $numMonths - 1; $i >= 0; $i--) {
        $time = strtotime("-$i months");
        $ym = date('Y-m', $time);
        $m = (int)date('m', $time);
        $label = $bulanIndo[$m] ?? date('M', $time);
        if ($i === 0) {
            $label .= ' (Berjalan)';
        }
        $monthsMap[$ym] = ['label' => $label, 'total' => 0];
    }

    if ($db) {
        try {
            $sql = "
                SELECT DATE_FORMAT(paid_at, '%Y-%m') as ym, COALESCE(SUM(amount), 0) as total 
                FROM vip_purchases 
                WHERE status = 'paid' AND paid_at >= DATE_SUB(CURRENT_DATE(), INTERVAL :m MONTH)
                GROUP BY ym
            ";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':m', (int)$numMonths, PDO::PARAM_INT);
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (isset($monthsMap[$row['ym']])) {
                    $monthsMap[$row['ym']]['total'] = (float)$row['total'];
                }
            }
        } catch (Exception $e) {}
    }

    foreach ($monthsMap as $item) {
        $result['labels'][] = $item['label'];
        $result['data'][] = $item['total'];
    }

    return $result;
}

/**
 * Mengambil data komposisi pengguna riil (Free vs VIP vs Banned)
 */
function getUserCompositionStats() {
    $db = getDb();
    $data = [
        'regular_free' => 0,
        'vip_active'   => 0,
        'banned'       => 0,
        'conversion'   => '0%',
        'arpu'         => 'Rp 0'
    ];

    if ($db) {
        try {
            $comp = $db->query("
                SELECT 
                    COUNT(*) as total_users,
                    COUNT(CASE WHEN is_banned = 1 THEN 1 END) as banned_count,
                    COUNT(CASE WHEN is_banned = 0 AND vip_until IS NOT NULL AND vip_until > NOW() THEN 1 END) as vip_active_count,
                    COUNT(CASE WHEN is_banned = 0 AND (vip_until IS NULL OR vip_until <= NOW()) THEN 1 END) as regular_free_count
                FROM users
            ")->fetch(PDO::FETCH_ASSOC);

            $totalRev = (float)$db->query("SELECT COALESCE(SUM(amount), 0) FROM vip_purchases WHERE status = 'paid'")->fetchColumn();

            $total = (int)($comp['total_users'] ?? 0);
            $vip = (int)($comp['vip_active_count'] ?? 0);
            $free = (int)($comp['regular_free_count'] ?? 0);
            $banned = (int)($comp['banned_count'] ?? 0);

            $data['regular_free'] = $free;
            $data['vip_active']   = $vip;
            $data['banned']       = $banned;

            if ($total > 0) {
                $data['conversion'] = round(($vip / $total) * 100, 1) . '%';
            }
            if ($vip > 0) {
                $data['arpu'] = formatRupiah(round($totalRev / $vip));
            } elseif ($total > 0) {
                $data['arpu'] = formatRupiah(round($totalRev / $total));
            }
            return $data;
        } catch (Exception $e) {}
    }

    // Demo fallback
    return [
        'regular_free' => 2932,
        'vip_active'   => 488,
        'banned'       => 12,
        'conversion'   => '14.3%',
        'arpu'         => 'Rp 38.400'
    ];
}

/**
 * Mengambil ringkasan metrik keuangan & VIP riil dari database
 */
function getVipFinancialMetrics() {
    $db = getDb();
    $metrics = [
        'total_revenue'      => 0,
        'today_revenue'      => 0,
        'week_revenue'       => 0,
        'month_revenue'      => 0,
        'total_paid_sales'   => 0,
        'today_paid_sales'   => 0,
        'is_live'            => false
    ];

    if ($db) {
        try {
            $sql = "
                SELECT 
                    COALESCE(SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END), 0) as total_rev,
                    COALESCE(SUM(CASE WHEN status = 'paid' AND DATE(paid_at) = CURRENT_DATE() THEN amount ELSE 0 END), 0) as today_rev,
                    COALESCE(SUM(CASE WHEN status = 'paid' AND YEARWEEK(paid_at, 1) = YEARWEEK(CURRENT_DATE(), 1) THEN amount ELSE 0 END), 0) as week_rev,
                    COALESCE(SUM(CASE WHEN status = 'paid' AND MONTH(paid_at) = MONTH(CURRENT_DATE()) AND YEAR(paid_at) = YEAR(CURRENT_DATE()) THEN amount ELSE 0 END), 0) as month_rev,
                    COUNT(CASE WHEN status = 'paid' THEN 1 END) as total_paid_sales,
                    COUNT(CASE WHEN status = 'paid' AND DATE(paid_at) = CURRENT_DATE() THEN 1 END) as today_paid_sales
                FROM vip_purchases
            ";
            $res = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
            if ($res) {
                $metrics['total_revenue']    = (float)$res['total_rev'];
                $metrics['today_revenue']    = (float)$res['today_rev'];
                $metrics['week_revenue']     = (float)$res['week_rev'];
                $metrics['month_revenue']    = (float)$res['month_rev'];
                $metrics['total_paid_sales'] = (int)$res['total_paid_sales'];
                $metrics['today_paid_sales'] = (int)$res['today_paid_sales'];
                $metrics['is_live']          = true;
                return $metrics;
            }
        } catch (Exception $e) {}
    }

    // Fallback demo data
    return [
        'total_revenue'      => 18750000,
        'today_revenue'      => 245000,
        'week_revenue'       => 1680000,
        'month_revenue'      => 4820000,
        'total_paid_sales'   => 612,
        'today_paid_sales'   => 7,
        'is_live'            => false
    ];
}

/**
 * Mengambil daftar seluruh paket VIP riil dari database (tabel vip_plans)
 */
function getVipPlansList() {
    $db = getDb();
    if ($db) {
        try {
            $sql = "
                SELECT p.*, 
                    COUNT(CASE WHEN vp.status = 'paid' THEN 1 END) as sales_count,
                    COALESCE(SUM(CASE WHEN vp.status = 'paid' THEN vp.amount ELSE 0 END), 0) as total_generated
                FROM vip_plans p
                LEFT JOIN vip_purchases vp ON vp.plan_id = p.id
                GROUP BY p.id
                ORDER BY p.sort_order ASC, p.price ASC
            ";
            $plans = $db->query($sql)->fetchAll();
            if (!empty($plans)) {
                foreach ($plans as &$p) {
                    $d = (int)$p['duration_days'];
                    if ($d <= 1) {
                        $p['badge'] = 'Trial Harian';
                        $p['color'] = 'from-blue-500/20 to-cyan-500/10';
                    } elseif ($d <= 3) {
                        $p['badge'] = 'Hemat 3 Hari';
                        $p['color'] = 'from-sky-500/20 to-teal-500/10';
                    } elseif ($d <= 7) {
                        $p['badge'] = 'Mingguan';
                        $p['color'] = 'from-teal-500/20 to-emerald-500/10';
                    } elseif ($d <= 15) {
                        $p['badge'] = 'Setengah Bulan';
                        $p['color'] = 'from-indigo-500/20 to-blue-500/10';
                    } elseif ($d <= 30) {
                        $p['badge'] = 'Paling Populer';
                        $p['color'] = 'from-[#00D0B6]/25 to-teal-500/10';
                    } elseif ($d <= 90) {
                        $p['badge'] = 'Hemat 3 Bulan';
                        $p['color'] = 'from-purple-500/20 to-indigo-500/10';
                    } else {
                        $p['badge'] = 'Ultra VIP Tahunan';
                        $p['color'] = 'from-amber-500/20 to-yellow-500/10';
                    }
                }
                return $plans;
            }
        } catch (Exception $e) {}
    }

    // Demo fallback
    return [
        ['id' => 1, 'name' => 'VIP 7 Hari', 'badge' => 'Trial', 'duration_days' => 7, 'price' => 12000, 'sales_count' => 184, 'color' => 'from-blue-500/20 to-cyan-500/10'],
        ['id' => 2, 'name' => 'VIP 30 Hari', 'badge' => 'Paling Populer', 'duration_days' => 30, 'price' => 35000, 'sales_count' => 312, 'color' => 'from-[#00D0B6]/25 to-teal-500/10'],
        ['id' => 3, 'name' => 'VIP 90 Hari', 'badge' => 'Hemat 25%', 'duration_days' => 90, 'price' => 89000, 'sales_count' => 76, 'color' => 'from-purple-500/20 to-indigo-500/10'],
        ['id' => 4, 'name' => 'VIP 1 Tahun', 'badge' => 'Ultra VIP', 'duration_days' => 365, 'price' => 249000, 'sales_count' => 40, 'color' => 'from-amber-500/20 to-yellow-500/10']
    ];
}

/**
 * Menghitung jumlah transaksi per status untuk badge filter
 */
function getVipStatusCounts() {
    $db = getDb();
    $counts = ['all' => 0, 'paid' => 0, 'pending' => 0, 'expired' => 0, 'failed' => 0];
    if ($db) {
        try {
            $res = $db->query("SELECT LOWER(status) as st, COUNT(*) as c FROM vip_purchases GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
            foreach ($res as $st => $c) {
                if (isset($counts[$st])) $counts[$st] = (int)$c;
                $counts['all'] += (int)$c;
            }
            return $counts;
        } catch (Exception $e) {}
    }

    return ['all' => 6, 'paid' => 4, 'pending' => 1, 'expired' => 1, 'failed' => 0];
}

/**
 * Mengambil riwayat transaksi VIP riil dari database dengan pencarian & filter
 */
function getRecentVipPurchases($limit = 50, $filter = 'all', $search = '') {
    $db = getDb();
    if ($db) {
        try {
            $where = [];
            $params = [];

            if (!empty($search)) {
                $where[] = "(u.username LIKE :s OR u.first_name LIKE :s OR u.telegram_user_id LIKE :s OR qp.gateway_ref_id LIKE :s OR vp.id = :sid)";
                $params[':s'] = "%$search%";
                $params[':sid'] = is_numeric($search) ? (int)$search : 0;
            }

            if ($filter !== 'all' && !empty($filter)) {
                $where[] = "LOWER(vp.status) = :st";
                $params[':st'] = strtolower($filter);
            }

            $sql = "SELECT vp.*, u.username, u.first_name, u.telegram_user_id, p.name as plan_name, qp.gateway_ref_id 
                    FROM vip_purchases vp
                    JOIN users u ON vp.user_id = u.id
                    LEFT JOIN vip_plans p ON vp.plan_id = p.id
                    LEFT JOIN qris_payments qp ON qp.vip_purchase_id = vp.id";
            if (!empty($where)) {
                $sql .= " WHERE " . implode(' AND ', $where);
            }
            $sql .= " ORDER BY vp.created_at DESC LIMIT " . (int)$limit;

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll();
            return $results;
        } catch (Exception $e) {}
    }

    // Demo data transaksi jika database tidak terhubung
    $demo = [
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

    if ($filter !== 'all') {
        $demo = array_filter($demo, fn($d) => strtolower($d['status']) === strtolower($filter));
    }

    return array_values($demo);
}

/**
 * Mengambil jumlah pengguna per kategori tab dari database riil
 */
function getUserTabCounts() {
    $db = getDb();
    $counts = [
        'all'       => 0,
        'vip'       => 0,
        'regular'   => 0,
        'affiliate' => 0,
        'banned'    => 0
    ];

    if ($db) {
        try {
            $sql = "
                SELECT 
                    COUNT(*) as total_users,
                    COUNT(CASE WHEN is_banned = 1 THEN 1 END) as banned_count,
                    COUNT(CASE WHEN vip_until IS NOT NULL AND vip_until > NOW() THEN 1 END) as vip_active_count,
                    COUNT(CASE WHEN (vip_until IS NULL OR vip_until <= NOW()) AND is_banned = 0 THEN 1 END) as regular_free_count,
                    COUNT(CASE WHEN is_affiliate = 1 OR coin_balance > 0 THEN 1 END) as affiliate_count
                FROM users
            ";
            $res = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
            if ($res) {
                $counts['all']       = (int)$res['total_users'];
                $counts['vip']       = (int)$res['vip_active_count'];
                $counts['regular']   = (int)$res['regular_free_count'];
                $counts['affiliate'] = (int)$res['affiliate_count'];
                $counts['banned']    = (int)$res['banned_count'];
                return $counts;
            }
        } catch (Exception $e) {}
    }

    return ['all' => 7, 'vip' => 4, 'regular' => 2, 'affiliate' => 4, 'banned' => 1];
}

/**
 * Eksekusi aksi manajemen pengguna (Tambah VIP, Toggle Ban, Input User Manual)
 */
function manageUserAction($action, $userId, $extra = []) {
    $db = getDb();
    if (!$db) return ['success' => false, 'message' => 'Database tidak terhubung'];

    try {
        if ($action === 'add_vip') {
            $days = isset($extra['days']) ? (int)$extra['days'] : 30;
            $stmt = $db->prepare("
                UPDATE users 
                SET vip_until = CASE 
                    WHEN vip_until IS NOT NULL AND vip_until > NOW() THEN DATE_ADD(vip_until, INTERVAL :days DAY)
                    ELSE DATE_ADD(NOW(), INTERVAL :days DAY)
                END 
                WHERE id = :id
            ");
            $stmt->execute([':days' => $days, ':id' => $userId]);
            return ['success' => true, 'message' => "Masa VIP user #{$userId} berhasil diperpanjang {$days} hari!"];
        } elseif ($action === 'toggle_ban') {
            $stmt = $db->prepare("UPDATE users SET is_banned = CASE WHEN is_banned = 1 THEN 0 ELSE 1 END WHERE id = :id");
            $stmt->execute([':id' => $userId]);

            $check = $db->prepare("SELECT is_banned, first_name FROM users WHERE id = :id");
            $check->execute([':id' => $userId]);
            $u = $check->fetch();
            $statusStr = ($u['is_banned'] == 1) ? 'di-Banned' : 'di-Unban (Aktif)';
            return ['success' => true, 'message' => "Pengguna {$u['first_name']} (#{$userId}) berhasil {$statusStr}."];
        } elseif ($action === 'add_user') {
            $tgId = (int)($extra['telegram_user_id'] ?? 0);
            $firstName = trim($extra['first_name'] ?? '');
            $username = trim($extra['username'] ?? '');
            $sub = $extra['subscription'] ?? 'regular';

            if ($tgId <= 0 || empty($firstName)) {
                return ['success' => false, 'message' => 'Telegram User ID dan Nama Depan wajib diisi!'];
            }

            $vipUntil = null;
            if ($sub === 'vip_7') $vipUntil = date('Y-m-d H:i:s', strtotime('+7 days'));
            elseif ($sub === 'vip_30') $vipUntil = date('Y-m-d H:i:s', strtotime('+30 days'));
            elseif ($sub === 'vip_90') $vipUntil = date('Y-m-d H:i:s', strtotime('+90 days'));

            $stmt = $db->prepare("
                INSERT INTO users (telegram_user_id, first_name, username, vip_until, created_at, last_active_at)
                VALUES (:tg, :fn, :un, :vip, NOW(), NOW())
                ON DUPLICATE KEY UPDATE first_name = :fn, username = :un, vip_until = COALESCE(:vip, vip_until)
            ");
            $stmt->execute([
                ':tg' => $tgId,
                ':fn' => $firstName,
                ':un' => $username,
                ':vip' => $vipUntil
            ]);
            return ['success' => true, 'message' => "Data pengguna Telegram {$firstName} ({$tgId}) berhasil disimpan ke database!"];
        }

        return ['success' => false, 'message' => 'Aksi tidak dikenal'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Mengambil daftar seluruh Pelanggan Telegram
 */
function getTelegramUsersList($search = '', $filter = 'all', $limit = 100) {
    $db = getDb();
    if ($db) {
        try {
            $where = [];
            $params = [];

            if (!empty($search)) {
                $where[] = "(username LIKE :s OR first_name LIKE :s OR last_name LIKE :s OR telegram_user_id LIKE :s)";
                $params[':s'] = "%$search%";
            }

            if ($filter === 'vip') {
                $where[] = "vip_until IS NOT NULL AND vip_until > NOW()";
            } elseif ($filter === 'regular') {
                $where[] = "(vip_until IS NULL OR vip_until <= NOW()) AND is_banned = 0";
            } elseif ($filter === 'affiliate') {
                $where[] = "(is_affiliate = 1 OR coin_balance > 0)";
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
            return $stmt->fetchAll();
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
 * Statistik Ringkas Katalog Drama
 */
function getDramaStats() {
    $db = getDb();
    $stats = [
        'total_dramas' => 0,
        'total_episodes_uploaded' => 0,
        'ongoing_count' => 0,
        'completed_count' => 0
    ];

    if ($db) {
        try {
            $res = $db->query("SELECT 
                COUNT(*) as total_dramas,
                COUNT(CASE WHEN status = 'ongoing' THEN 1 END) as ongoing_count,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_count,
                (SELECT COUNT(*) FROM episodes) as total_episodes_uploaded
            FROM dramas")->fetch(PDO::FETCH_ASSOC);
            if ($res) return $res;
        } catch (Exception $e) {}
    }

    return ['total_dramas' => 4, 'total_episodes_uploaded' => 20, 'ongoing_count' => 4, 'completed_count' => 0];
}

/**
 * Data Seluruh Kategori Drama untuk Form Tambah / Edit
 */
function getDramaCategoriesList() {
    $db = getDb();
    if ($db) {
        try {
            return $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}
    }
    return [];
}

/**
 * Mengambil daftar episode untuk drama tertentu
 */
function getDramaEpisodes($dramaId) {
    $db = getDb();
    if ($db) {
        try {
            $stmt = $db->prepare("SELECT * FROM episodes WHERE drama_id = :id ORDER BY episode_number ASC");
            $stmt->execute([':id' => $dramaId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}
    }
    return [];
}

/**
 * Data Katalog Drama Riil dari Database
 */
function getDramasCatalog($search = '', $filter = 'all') {
    $db = getDb();
    if ($db) {
        try {
            $where = [];
            $params = [];

            if (!empty($search)) {
                $where[] = "(d.title LIKE :s OR d.description LIKE :s OR d.country_origin LIKE :s)";
                $params[':s'] = "%$search%";
            }

            if ($filter !== 'all' && !empty($filter)) {
                $where[] = "d.status = :st";
                $params[':st'] = $filter;
            }

            $sql = "SELECT d.*, 
                (SELECT COUNT(*) FROM episodes WHERE drama_id = d.id) as ep_count,
                (SELECT GROUP_CONCAT(c.name SEPARATOR ', ') FROM categories c JOIN drama_categories dc ON dc.category_id = c.id WHERE dc.drama_id = d.id) as categories
                FROM dramas d";

            if (!empty($where)) {
                $sql .= " WHERE " . implode(' AND ', $where);
            }

            $sql .= " ORDER BY d.created_at DESC";

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}
    }

    $demo = [
        [
            'id' => 1,
            'title' => 'Queen of Tears',
            'slug' => 'queen-of-tears',
            'description' => 'Kisah cinta ajaib dan romantis antara pasangan suami istri yang menghadapi krisis perkawinan rumit.',
            'poster_url' => 'https://images.unsplash.com/photo-1578632767115-351597cf2477?auto=format&fit=crop&w=400&q=80',
            'country_origin' => 'Korea Selatan',
            'release_year' => 2024,
            'total_episodes' => 16,
            'ep_count' => 16,
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
            'ep_count' => 16,
            'free_episodes_count' => 2,
            'status' => 'completed',
            'views_count' => 18940,
            'categories' => 'Fantasy, Revenge, Romance',
            'is_published' => 1
        ]
    ];

    return $demo;
}

/**
 * Eksekusi Aksi Manajemen Drama & Episode
 */
function manageDramaAction($action, $data) {
    $db = getDb();
    if (!$db) return ['success' => false, 'message' => 'Database tidak terhubung'];

    try {
        if ($action === 'add_drama') {
            $title = trim($data['title'] ?? '');
            if (empty($title)) return ['success' => false, 'message' => 'Judul drama wajib diisi!'];

            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
            if (empty($slug)) $slug = 'drama-' . time();

            // Cek keunikan slug
            $chk = $db->prepare("SELECT id FROM dramas WHERE slug = :s");
            $chk->execute([':s' => $slug]);
            if ($chk->fetch()) {
                $slug .= '-' . rand(100, 999);
            }

            $country = trim($data['country_origin'] ?? 'Korea');
            $year = !empty($data['release_year']) ? (int)$data['release_year'] : (int)date('Y');
            $poster = trim($data['poster_url'] ?? '');
            $totalEp = !empty($data['total_episodes']) ? (int)$data['total_episodes'] : 0;
            $freeEp = isset($data['free_episodes_count']) ? (int)$data['free_episodes_count'] : 1;
            $desc = trim($data['description'] ?? '');
            $status = in_array($data['status'] ?? '', ['ongoing', 'completed', 'hiatus']) ? $data['status'] : 'ongoing';

            $stmt = $db->prepare("INSERT INTO dramas 
                (title, slug, description, poster_url, country_origin, release_year, total_episodes, free_episodes_count, status, is_published, created_at)
                VALUES (:t, :s, :d, :p, :c, :y, :te, :fe, :st, 1, NOW())");
            $stmt->execute([
                ':t' => $title,
                ':s' => $slug,
                ':d' => $desc,
                ':p' => $poster,
                ':c' => $country,
                ':y' => $year,
                ':te' => $totalEp,
                ':fe' => $freeEp,
                ':st' => $status
            ]);
            $dramaId = $db->lastInsertId();

            // Hubungkan kategori jika dipilih
            if (!empty($data['category_ids']) && is_array($data['category_ids'])) {
                $catStmt = $db->prepare("INSERT IGNORE INTO drama_categories (drama_id, category_id) VALUES (:did, :cid)");
                foreach ($data['category_ids'] as $cid) {
                    $catStmt->execute([':did' => $dramaId, ':cid' => (int)$cid]);
                }
            }

            return ['success' => true, 'message' => "Drama '{$title}' berhasil ditambahkan ke katalog!"];
        }

        elseif ($action === 'delete_drama') {
            $id = (int)($data['drama_id'] ?? 0);
            if ($id <= 0) return ['success' => false, 'message' => 'ID Drama tidak valid'];

            $stmt = $db->prepare("DELETE FROM dramas WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return ['success' => true, 'message' => "Drama #{$id} dan seluruh episodenya berhasil dihapus!"];
        }

        elseif ($action === 'add_episode') {
            $dramaId = (int)($data['drama_id'] ?? 0);
            $epNum = (int)($data['episode_number'] ?? 1);
            $channelId = (int)($data['telegram_channel_id'] ?? 0);
            $msgId = (int)($data['telegram_message_id'] ?? 0);
            $fileId = trim($data['telegram_file_id'] ?? '');
            $epTitle = trim($data['title'] ?? "Episode {$epNum}");

            if ($dramaId <= 0 || $epNum <= 0 || $channelId == 0 || $msgId <= 0) {
                return ['success' => false, 'message' => 'Nomor episode, Channel ID, dan Message ID wajib diisi dengan benar!'];
            }

            $stmt = $db->prepare("INSERT INTO episodes 
                (drama_id, episode_number, title, telegram_channel_id, telegram_message_id, telegram_file_id, created_at)
                VALUES (:did, :ep, :title, :chid, :mid, :fid, NOW())
                ON DUPLICATE KEY UPDATE 
                    title = :title, 
                    telegram_channel_id = :chid, 
                    telegram_message_id = :mid, 
                    telegram_file_id = :fid");
            $stmt->execute([
                ':did'   => $dramaId,
                ':ep'    => $epNum,
                ':title' => $epTitle,
                ':chid'  => $channelId,
                ':mid'   => $msgId,
                ':fid'   => $fileId
            ]);

            // Sinkronkan total_episodes jika nomor episode melampaui total_episodes
            $updDrama = $db->prepare("UPDATE dramas SET total_episodes = GREATEST(total_episodes, :ep), updated_at = NOW() WHERE id = :did");
            $updDrama->execute([':ep' => $epNum, ':did' => $dramaId]);

            return ['success' => true, 'message' => "Episode {$epNum} berhasil disinkronkan ke channel Telegram!"];
        }

        elseif ($action === 'delete_episode') {
            $epId = (int)($data['episode_id'] ?? 0);
            if ($epId <= 0) return ['success' => false, 'message' => 'ID Episode tidak valid'];

            $stmt = $db->prepare("DELETE FROM episodes WHERE id = :id");
            $stmt->execute([':id' => $epId]);
            return ['success' => true, 'message' => "Episode #{$epId} berhasil dihapus!"];
        }

        return ['success' => false, 'message' => 'Aksi tidak dikenal'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Mengambil ringkasan statistik Affiliate riil dari database
 */
function getAffiliateStats() {
    $db = getDb();
    $stats = [
        'pending_count'     => 0,
        'withdrawn_total'   => 0,
        'withdrawn_count'   => 0,
        'circulating_coins' => 0,
        'affiliates_count'  => 0,
        'is_live'           => false
    ];

    if ($db) {
        try {
            // Permintaan pending / diproses
            $qPending = $db->query("SELECT COUNT(*) FROM affiliate_withdrawals WHERE status IN ('pending', 'diproses')");
            $stats['pending_count'] = (int)$qPending->fetchColumn();

            // Total komisi ditarik (status completed / terkirim)
            $qDone = $db->query("SELECT COUNT(*) as count, COALESCE(SUM(amount_after_fee), 0) as total FROM affiliate_withdrawals WHERE status IN ('completed', 'terkirim')");
            $rowDone = $qDone->fetch(PDO::FETCH_ASSOC);
            $stats['withdrawn_total'] = (float)($rowDone['total'] ?? 0);
            $stats['withdrawn_count'] = (int)($rowDone['count'] ?? 0);

            // Saldo koin beredar di semua user
            $qCoins = $db->query("SELECT COALESCE(SUM(coin_balance), 0) FROM users");
            $stats['circulating_coins'] = (int)$qCoins->fetchColumn();

            // Total mitra affiliate
            $qMitra = $db->query("SELECT COUNT(*) FROM users WHERE is_affiliate = 1 OR coin_balance > 0");
            $stats['affiliates_count'] = (int)$qMitra->fetchColumn();

            $stats['is_live'] = true;
            return $stats;
        } catch (Exception $e) {}
    }

    // Fallback demo data jika DB tidak terhubung
    return [
        'pending_count'     => 2,
        'withdrawn_total'   => 1450000,
        'withdrawn_count'   => 18,
        'circulating_coins' => 275000,
        'affiliates_count'  => 48,
        'is_live'           => false
    ];
}

/**
 * Data Penarikan Saldo Affiliate Riil dari Database
 */
function getAffiliateWithdrawalsList($filter = 'all', $search = '') {
    $db = getDb();
    if ($db) {
        try {
            $where = [];
            $params = [];

            if (!empty($search)) {
                $where[] = "(u.username LIKE :s OR u.first_name LIKE :s OR aw.account_number LIKE :s OR aw.account_holder_name LIKE :s OR aw.id = :sid)";
                $params[':s'] = "%$search%";
                $params[':sid'] = is_numeric($search) ? (int)$search : 0;
            }

            if ($filter === 'pending' || $filter === 'diproses') {
                $where[] = "aw.status IN ('pending', 'diproses')";
            } elseif ($filter === 'completed' || $filter === 'terkirim') {
                $where[] = "aw.status IN ('completed', 'terkirim')";
            } elseif ($filter === 'rejected' || $filter === 'ditolak') {
                $where[] = "aw.status IN ('rejected', 'ditolak')";
            }

            $sql = "SELECT aw.*, u.username, u.first_name, u.telegram_user_id 
                    FROM affiliate_withdrawals aw
                    JOIN users u ON aw.user_id = u.id";
            if (!empty($where)) {
                $sql .= " WHERE " . implode(' AND ', $where);
            }
            $sql .= " ORDER BY aw.requested_at DESC";

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $res = $stmt->fetchAll();
            return $res;
        } catch (Exception $e) {}
    }

    // Demo fallback jika database mati
    $demo = [
        [
            'id' => 1,
            'user_id' => 1,
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
            'status' => 'diproses',
            'requested_at' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            'processed_at' => null,
            'admin_notes' => null
        ],
        [
            'id' => 2,
            'user_id' => 2,
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
            'status' => 'diproses',
            'requested_at' => date('Y-m-d H:i:s', strtotime('-4 hours')),
            'processed_at' => null,
            'admin_notes' => null
        ],
        [
            'id' => 3,
            'user_id' => 3,
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
            'status' => 'terkirim',
            'requested_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            'processed_at' => date('Y-m-d H:i:s', strtotime('-2 days +1 hour')),
            'admin_notes' => 'Terkirim via GoPay'
        ]
    ];

    if ($filter === 'pending' || $filter === 'diproses') {
        $demo = array_filter($demo, fn($d) => in_array($d['status'], ['pending', 'diproses']));
    } elseif ($filter === 'completed' || $filter === 'terkirim') {
        $demo = array_filter($demo, fn($d) => in_array($d['status'], ['completed', 'terkirim']));
    } elseif ($filter === 'rejected' || $filter === 'ditolak') {
        $demo = array_filter($demo, fn($d) => in_array($d['status'], ['rejected', 'ditolak']));
    }

    return array_values($demo);
}

/**
 * Memproses verifikasi penarikan affiliate riil (Setujui / Tolak)
 */
function processAffiliateWithdrawal($id, $action, $adminNotes = '') {
    $db = getDb();
    if (!$db) return ['success' => false, 'message' => 'Database tidak terhubung'];

    try {
        $stmt = $db->prepare("SELECT * FROM affiliate_withdrawals WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $wd = $stmt->fetch();
        if (!$wd) {
            return ['success' => false, 'message' => 'Data penarikan tidak ditemukan'];
        }

        if ($action === 'approve') {
            $upd = $db->prepare("UPDATE affiliate_withdrawals SET status = 'terkirim', processed_at = NOW(), admin_notes = :notes WHERE id = :id");
            $upd->execute([
                ':notes' => !empty($adminNotes) ? $adminNotes : 'Disetujui dan ditransfer via Admin Dashboard',
                ':id' => $id
            ]);
            return ['success' => true, 'message' => "Penarikan #WD_{$id} berhasil disetujui (Status: Terkirim)"];
        } elseif ($action === 'reject') {
            // Tolak penarikan: kembalikan saldo koin ke user di dalam transaksi
            $db->beginTransaction();
            $upd = $db->prepare("UPDATE affiliate_withdrawals SET status = 'rejected', processed_at = NOW(), admin_notes = :notes WHERE id = :id");
            $upd->execute([
                ':notes' => !empty($adminNotes) ? $adminNotes : 'Ditolak via Admin Dashboard',
                ':id' => $id
            ]);

            // Refund koin ke users
            $ref = $db->prepare("UPDATE users SET coin_balance = coin_balance + :coin WHERE id = :uid");
            $ref->execute([
                ':coin' => $wd['amount_coin'],
                ':uid'  => $wd['user_id']
            ]);
            $db->commit();
            return ['success' => true, 'message' => "Penarikan #WD_{$id} telah ditolak dan koin sebesar " . number_format($wd['amount_coin']) . " dikembalikan ke saldo user."];
        }

        return ['success' => false, 'message' => 'Aksi tidak dikenal'];
    } catch (Exception $e) {
        if ($db->inTransaction()) $db->rollBack();
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Statistik Ringkas Request Film
 */
function getFilmRequestStats() {
    $db = getDb();
    $stats = [
        'total_requests'    => 0,
        'pending_count'     => 0,
        'priority_count'    => 0,
        'completed_count'   => 0,
        'rejected_count'    => 0
    ];

    if ($db) {
        try {
            $res = $db->query("SELECT 
                COUNT(*) as total_requests,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
                COUNT(CASE WHEN is_priority = 1 THEN 1 END) as priority_count,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_count,
                COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_count
            FROM film_requests")->fetch(PDO::FETCH_ASSOC);
            if ($res) return $res;
        } catch (Exception $e) {}
    }

    return ['total_requests' => 3, 'pending_count' => 3, 'priority_count' => 1, 'completed_count' => 0, 'rejected_count' => 0];
}

/**
 * Data Request Film dari Pelanggan Riil
 */
function getFilmRequestsList($filter = 'all', $search = '') {
    $db = getDb();
    if ($db) {
        try {
            $where = [];
            $params = [];

            if (!empty($search)) {
                $where[] = "(fr.requested_title LIKE :s OR fr.notes LIKE :s OR u.first_name LIKE :s OR u.telegram_user_id LIKE :s)";
                $params[':s'] = "%$search%";
            }

            if ($filter === 'priority') {
                $where[] = "fr.is_priority = 1";
            } elseif ($filter !== 'all' && !empty($filter)) {
                $where[] = "fr.status = :st";
                $params[':st'] = $filter;
            }

            $sql = "SELECT fr.*, u.username, u.first_name, u.telegram_user_id, d.title as linked_drama_title 
                    FROM film_requests fr
                    JOIN users u ON fr.user_id = u.id
                    LEFT JOIN dramas d ON fr.linked_drama_id = d.id";

            if (!empty($where)) {
                $sql .= " WHERE " . implode(' AND ', $where);
            }

            $sql .= " ORDER BY fr.is_priority DESC, fr.created_at DESC";

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}
    }

    $demo = [
        [
            'id' => 1,
            'username' => 'andipratama',
            'first_name' => 'Andi Pratama',
            'telegram_user_id' => '102938481',
            'requested_title' => 'Chief Detective 1958',
            'source_app' => 'Disney+ / MBC',
            'notes' => 'Tolong min tambahin yang episode 9-10 sub Indo ya',
            'is_priority' => 1,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours'))
        ]
    ];

    return $demo;
}

/**
 * Memproses Status Permintaan Film (Selesai Ditambahkan / Tolak)
 */
function processFilmRequest($id, $action, $adminNotes = '', $linkedDramaId = null) {
    $db = getDb();
    if (!$db) return ['success' => false, 'message' => 'Database tidak terhubung'];

    try {
        $id = (int)$id;
        $chk = $db->prepare("SELECT * FROM film_requests WHERE id = :id");
        $chk->execute([':id' => $id]);
        $req = $chk->fetch();
        if (!$req) return ['success' => false, 'message' => 'Data permintaan tidak ditemukan'];

        if ($action === 'complete') {
            $did = !empty($linkedDramaId) ? (int)$linkedDramaId : null;
            $stmt = $db->prepare("UPDATE film_requests SET 
                status = 'completed', 
                processed_at = NOW(), 
                admin_notes = :notes, 
                linked_drama_id = :did 
                WHERE id = :id");
            $stmt->execute([
                ':notes' => !empty($adminNotes) ? $adminNotes : 'Drama telah berhasil ditambahkan ke channel',
                ':did'   => $did,
                ':id'    => $id
            ]);
            return ['success' => true, 'message' => "Permintaan '{$req['requested_title']}' berhasil ditandai selesai (Telah Diunggah)!"];
        } elseif ($action === 'reject') {
            $stmt = $db->prepare("UPDATE film_requests SET 
                status = 'rejected', 
                processed_at = NOW(), 
                admin_notes = :notes 
                WHERE id = :id");
            $stmt->execute([
                ':notes' => !empty($adminNotes) ? $adminNotes : 'Permintaan belum dapat dipenuhi saat ini',
                ':id'    => $id
            ]);
            return ['success' => true, 'message' => "Permintaan '{$req['requested_title']}' telah ditolak."];
        } elseif ($action === 'pending') {
            $stmt = $db->prepare("UPDATE film_requests SET 
                status = 'pending', 
                processed_at = NULL, 
                admin_notes = NULL 
                WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return ['success' => true, 'message' => "Status permintaan dikembalikan ke Antrean (Pending)."];
        }

        return ['success' => false, 'message' => 'Aksi tidak dikenal'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}
