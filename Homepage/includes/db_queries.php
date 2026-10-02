<?php
// db_queries.php
require_once __DIR__ . '/../../database/koneksi.php';

function getDramas($pdo, $limit = 10) {
    try {
        $stmt = $pdo->prepare("
            SELECT d.id, d.title, d.poster_url, d.total_episodes, d.views_count,
                   (SELECT e.id FROM episodes e WHERE e.drama_id = d.id ORDER BY e.episode_number ASC LIMIT 1) as first_episode_id
            FROM dramas d 
            WHERE d.is_published = 1 
            ORDER BY d.created_at DESC 
            LIMIT ?
        ");
        $stmt->bindParam(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
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
        $stats['watched'] = $stmt->fetchColumn();
        
        $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM watch_history WHERE user_id = ? AND is_completed = 1");
        $stmt2->execute([$user_id]);
        $stats['completed'] = $stmt2->fetchColumn();
        
        return $stats;
    } catch(PDOException $e) {
        return ['watched' => 0, 'completed' => 0];
    }
}
?>
