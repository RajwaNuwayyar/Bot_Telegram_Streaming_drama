<?php
// db_queries.php
require_once __DIR__ . '/../../database/koneksi.php';

/**
 * Normalisasi URL Poster Drama
 * Jika Telegram file_id (dari upload channel/bot dengan tag #poster), diarahkan ke api/poster.php?fid=...
 */
function getPosterUrl($rawUrl, $fallbackIndex = 0) {
    if (empty($rawUrl)) {
        // Daftar backdrop poster berkualitas tinggi untuk fallback default
        $defaultPosters = [
            "https://images.unsplash.com/photo-1578632767115-351597cf2477?auto=format&fit=crop&w=600&q=80",
            "https://images.unsplash.com/photo-1563089145-599997674d42?auto=format&fit=crop&w=600&q=80",
            "https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=600&q=80",
            "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=600&q=80",
            "https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=600&q=80",
            "https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=600&q=80",
        ];
        $idx = abs((int)$fallbackIndex) % count($defaultPosters);
        return $defaultPosters[$idx];
    }

    // Jika URL langsung (HTTP/HTTPS) atau data URI
    if (preg_match('/^https?:\/\//i', $rawUrl) || strpos($rawUrl, 'data:image') === 0 || strpos($rawUrl, '/') === 0 || strpos($rawUrl, './') === 0) {
        return $rawUrl;
    }

    // Jika Telegram file_id (disimpan saat admin upload foto ke channel dengan tag #poster)
    return "api/poster.php?fid=" . urlencode($rawUrl);
}



/**
 * Format waktu relatif bahasa Indonesia
 */
function timeAgoIndo($datetime) {
    if (empty($datetime)) return 'Baru saja';
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;

    if ($diff < 60) {
        return 'Baru saja';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' menit lalu';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' jam lalu';
    } elseif ($diff < 172800) {
        return 'Kemarin';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' hari lalu';
    } elseif ($diff < 2592000) {
        $weeks = floor($diff / 604800);
        return $weeks . ' minggu lalu';
    } else {
        return date('d M Y', $timestamp);
    }
}

/**
 * Ambil daftar kategori aktif yang memiliki drama
 */
function getCategories($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT c.id, c.name, c.slug, COUNT(dc.drama_id) as total_dramas
            FROM categories c
            LEFT JOIN drama_categories dc ON c.id = dc.category_id
            GROUP BY c.id, c.name, c.slug
            ORDER BY total_dramas DESC, c.name ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

/**
 * Ambil Drama untuk Featured Banners di Home
 */
function getFeaturedBanners($pdo, $limit = 5) {
    try {
        $stmt = $pdo->prepare("
            SELECT d.id, d.title, d.slug, d.description, d.poster_url,
                   COALESCE(NULLIF(d.total_episodes, 0), (SELECT COUNT(*) FROM episodes e WHERE e.drama_id = d.id), 0) as total_episodes,
                   d.views_count,
                   COALESCE((SELECT c.name FROM categories c 
                    INNER JOIN drama_categories dc ON dc.category_id = c.id 
                    WHERE dc.drama_id = d.id LIMIT 1), 'Drama') as category_name,
                   (SELECT e.id FROM episodes e WHERE e.drama_id = d.id ORDER BY e.episode_number ASC LIMIT 1) as first_episode_id
            FROM dramas d
            WHERE d.is_published = 1
            ORDER BY d.views_count DESC, d.id DESC
            LIMIT ?
        ");
        $stmt->bindParam(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

/**
 * Ambil daftar drama dengan filter kategori atau pencarian
 */
function getDramas($pdo, $limit = 20, $category_id = null, $search = null) {
    try {
        $sql = "
            SELECT d.id, d.title, d.slug, d.description, d.poster_url,
                   COALESCE(NULLIF(d.total_episodes, 0), (SELECT COUNT(*) FROM episodes e WHERE e.drama_id = d.id), 0) as total_episodes,
                   d.views_count,
                   COALESCE((SELECT c.name FROM categories c 
                    INNER JOIN drama_categories dc ON dc.category_id = c.id 
                    WHERE dc.drama_id = d.id LIMIT 1), 'Drama') as category_name,
                   (SELECT c.slug FROM categories c 
                    INNER JOIN drama_categories dc ON dc.category_id = c.id 
                    WHERE dc.drama_id = d.id LIMIT 1) as category_slug,
                   (SELECT e.id FROM episodes e WHERE e.drama_id = d.id ORDER BY e.episode_number ASC LIMIT 1) as first_episode_id
            FROM dramas d
            WHERE d.is_published = 1
        ";
        $params = [];

        if ($category_id) {
            $sql .= " AND EXISTS (SELECT 1 FROM drama_categories dc WHERE dc.drama_id = d.id AND dc.category_id = ?) ";
            $params[] = $category_id;
        }

        if ($search) {
            $sql .= " AND (
                d.title LIKE ? 
                OR d.description LIKE ? 
                OR EXISTS (
                    SELECT 1 FROM drama_categories dc_s 
                    JOIN categories c_s ON c_s.id = dc_s.category_id 
                    WHERE dc_s.drama_id = d.id AND (c_s.name LIKE ? OR c_s.slug LIKE ?)
                )
            ) ";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $sql .= " ORDER BY d.views_count DESC, d.id DESC LIMIT " . (int)$limit;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

/**
 * Ambil Riwayat Nonton (Watch History) Pengguna dari Database
 */
function getWatchHistory($pdo, $user_id, $filter = 'all') {
    try {
        $sql = "
            SELECT wh.id as history_id, wh.progress_seconds, wh.is_completed, wh.last_watched_at,
                   e.id as episode_id, e.episode_number, e.title as episode_title, e.duration_seconds,
                   d.id as drama_id, d.title as drama_title, d.poster_url,
                   COALESCE(NULLIF(d.total_episodes, 0), (SELECT COUNT(*) FROM episodes ep WHERE ep.drama_id = d.id), 0) as total_episodes,
                   COALESCE((SELECT c.name FROM categories c 
                    INNER JOIN drama_categories dc ON dc.category_id = c.id 
                    WHERE dc.drama_id = d.id LIMIT 1), 'Drama') as category_name
            FROM watch_history wh
            INNER JOIN episodes e ON wh.episode_id = e.id
            INNER JOIN dramas d ON e.drama_id = d.id
            WHERE wh.user_id = ?
        ";
        $params = [$user_id];

        if ($filter === 'progress') {
            $sql .= " AND wh.is_completed = 0";
        } elseif ($filter === 'completed') {
            $sql .= " AND wh.is_completed = 1";
        }

        $sql .= " ORDER BY wh.last_watched_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

/**
 * Hapus Semua Riwayat Nonton Pengguna
 */
function clearUserWatchHistory($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM watch_history WHERE user_id = ?");
        return $stmt->execute([$user_id]);
    } catch(PDOException $e) {
        return false;
    }
}

function getUserByTelegramId($pdo, $telegram_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE telegram_user_id = ?");
        $stmt->execute([$telegram_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return null;
    }
}

function getUserStats($pdo, $user_id) {
    try {
        $stats = ['watched' => 0, 'completed' => 0];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM watch_history WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $stats['watched'] = (int)$stmt->fetchColumn();
        
        $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM watch_history WHERE user_id = ? AND is_completed = 1");
        $stmt2->execute([$user_id]);
        $stats['completed'] = (int)$stmt2->fetchColumn();
        
        return $stats;
    } catch(PDOException $e) {
        return ['watched' => 0, 'completed' => 0];
    }
}
?>
