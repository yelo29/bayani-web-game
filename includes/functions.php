<?php
date_default_timezone_set('Asia/Manila');
require_once 'db.php';

function getCategories(): array {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT id, name, description, icon, color FROM categories ORDER BY id ASC");
    return $stmt->fetchAll();
}

function getRegions(): array {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT * FROM regions GROUP BY id ORDER BY min_level ASC");
    return $stmt->fetchAll();
}

function getEnemies(int $regionId): array {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM enemies WHERE region_id = ? ORDER BY id ASC");
    $stmt->execute([$regionId]);
    return $stmt->fetchAll();
}

function getEquippedItem(int $userId, string $type): ?array {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT i.name, i.power, i.type, i.description, i.rarity
        FROM inventory inv
        JOIN items i ON inv.item_id = i.id
        WHERE inv.user_id = ? AND inv.equipped = 1 AND i.type = ?
        LIMIT 1
    ");
    $stmt->execute([$userId, $type]);
    return $stmt->fetch() ?: null;
}

function getQuestions(int $categoryId, int $limit = 10): array {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT id, category_id, question, option_a, option_b, option_c, option_d, correct_option, fun_fact, difficulty
        FROM questions
        WHERE category_id = ?
        ORDER BY RAND()
        LIMIT ?
    ");
    $stmt->execute([$categoryId, $limit]);
    return $stmt->fetchAll();
}

function saveScore(string $name, int $categoryId, int $score, int $total, int $timeTaken): bool {
    $pdo = getDB();
    $now = new DateTime('now', new DateTimeZone('UTC'));
    $createdAt = $now->format('Y-m-d H:i:s');
    $stmt = $pdo->prepare("
        INSERT INTO scores (player_name, category_id, score, total_questions, time_taken, created_at)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    return $stmt->execute([$name, $categoryId, $score, $total, $timeTaken, $createdAt]);
}

function saveScoreWithUser(int $userId, string $username, int $categoryId, int $score, int $total, int $timeTaken, int $xpEarned): bool {
    $pdo = getDB();
    $now = new DateTime('now', new DateTimeZone('UTC'));
    $createdAt = $now->format('Y-m-d H:i:s');
    $stmt = $pdo->prepare("
        INSERT INTO scores (user_id, player_name, category_id, score, total_questions, time_taken, xp_earned, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    return $stmt->execute([$userId, $username, $categoryId, $score, $total, $timeTaken, $xpEarned, $createdAt]);
}

function getLeaderboard(int $categoryId = null, int $limit = 10, int $offset = 0, string $sortBy = 'score'): array {
    $pdo = getDB();

    if ($categoryId === null) {
        if ($sortBy === 'xp') {
            $stmt = $pdo->prepare("
                SELECT u.*, c.name as category_name, s.score, s.total_questions
                FROM users u
                LEFT JOIN scores s ON u.id = s.user_id
                LEFT JOIN categories c ON s.category_id = c.id
                GROUP BY u.id
                ORDER BY u.xp DESC, u.level DESC
            ");
            $stmt->execute();
            $results = $stmt->fetchAll();
        } else {
            $stmt = $pdo->prepare("
                SELECT s.*, COALESCE(s.player_name, u.username) as display_name, u.hero_class, u.level, u.xp, c.name as category_name
                FROM scores s
                LEFT JOIN users u ON s.user_id = u.id
                LEFT JOIN categories c ON s.category_id = c.id
                ORDER BY s.score DESC, s.time_taken ASC
            ");
            $stmt->execute();
            $results = $stmt->fetchAll();
        }
    } else {
        if ($sortBy === 'xp') {
            $stmt = $pdo->prepare("
                SELECT u.*, c.name as category_name, s.score, s.total_questions
                FROM users u
                INNER JOIN scores s ON u.id = s.user_id AND s.category_id = ?
                LEFT JOIN categories c ON s.category_id = c.id
                GROUP BY u.id
                ORDER BY u.xp DESC, u.level DESC
            ");
            $stmt->execute([$categoryId]);
            $results = $stmt->fetchAll();
        } else {
            $stmt = $pdo->prepare("
                SELECT s.*, COALESCE(s.player_name, u.username) as display_name, u.hero_class, u.level, u.xp, c.name as category_name
                FROM scores s
                LEFT JOIN users u ON s.user_id = u.id
                LEFT JOIN categories c ON s.category_id = c.id
                WHERE s.category_id = ?
                ORDER BY s.score DESC, s.time_taken ASC
            ");
            $stmt->execute([$categoryId]);
            $results = $stmt->fetchAll();
        }
    }

    // Apply pagination in PHP to ensure consistent behavior
    return array_slice($results, $offset, $limit);
}

function getTotalPlayers(int $categoryId = null): int {
    $pdo = getDB();

    if ($categoryId === null) {
        $stmt = $pdo->query("SELECT COUNT(DISTINCT player_name) FROM scores");
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(DISTINCT player_name) FROM scores WHERE category_id = ?");
        $stmt->execute([$categoryId]);
    }

    return (int) $stmt->fetchColumn();
}

function getTotalScores(int $categoryId = null, string $sortBy = 'score'): int {
    $pdo = getDB();

    if ($categoryId === null) {
        if ($sortBy === 'xp') {
            $stmt = $pdo->query("SELECT COUNT(DISTINCT u.id) FROM users u LEFT JOIN scores s ON u.id = s.user_id");
        } else {
            $stmt = $pdo->query("SELECT COUNT(*) FROM scores");
        }
    } else {
        if ($sortBy === 'xp') {
            // Count distinct users who have scores in this category
            $stmt = $pdo->prepare("SELECT COUNT(DISTINCT u.id) FROM users u INNER JOIN scores s ON u.id = s.user_id WHERE s.category_id = ?");
            $stmt->execute([$categoryId]);
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM scores WHERE category_id = ?");
            $stmt->execute([$categoryId]);
        }
    }

    return (int) $stmt->fetchColumn();
}

function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function getFunFact(int $questionId): ?string {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT fun_fact FROM questions WHERE id = ?");
    $stmt->execute([$questionId]);
    $result = $stmt->fetch();
    return $result ? $result['fun_fact'] : null;
}

function generateCSRFToken(): string {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function refreshSessionData(): void {
    if (!isset($_SESSION['user_id'])) {
        return;
    }

    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT username, hero_class, xp, level, coins, player_hp, player_max_hp, battle_warning_dismissed FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['username'] = $user['username'];
        $_SESSION['hero_class'] = $user['hero_class'];
        $_SESSION['xp'] = $user['xp'];
        $_SESSION['level'] = $user['level'];
        $_SESSION['coins'] = $user['coins'] ?? 0;
        $_SESSION['player_hp'] = $user['player_hp'] ?? 100;
        $_SESSION['player_max_hp'] = $user['player_max_hp'] ?? 100;
        $_SESSION['battle_warning_dismissed'] = $user['battle_warning_dismissed'] ?? 0;
    }
}

function calculateXP(int $score, int $total, int $timeTaken, string $heroClass, int $categoryId): int {
    $xp = 0;

    // Base XP: 10 XP per correct answer
    $xp += $score * 10;

    // Perfect score bonus: +20 XP
    if ($score === $total) {
        $xp += 20;
    }

    // Speed bonus: +10 XP for finishing under 2 minutes (120 seconds)
    if ($timeTaken < 120) {
        $xp += 10;
    }

    // Hero class bonuses
    if ($heroClass === 'mandirigma' && $categoryId === 1) {
        // Mandirigma bonus on history questions (category 1)
        $xp += $score * 5;
    } elseif ($heroClass === 'lakambini' && $score === $total) {
        // Lakambini bonus on perfect scores (already added, but extra bonus)
        $xp += 10;
    } elseif ($heroClass === 'mangkukulam' && $timeTaken < 120) {
        // Mangkukulam bonus on speed (already added, but extra bonus)
        $xp += 10;
    }

    return $xp;
}

function getLevelFromXP(int $xp): int {
    $levels = [
        0 => 1,
        100 => 2,
        250 => 3,
        500 => 4,
        1000 => 5,
        2000 => 6,
        3500 => 7,
        5500 => 8,
        8000 => 9,
        10000 => 10
    ];

    $level = 1;
    foreach ($levels as $xpThreshold => $lvl) {
        if ($xp >= $xpThreshold) {
            $level = $lvl;
        }
    }

    return $level;
}

function getXPForNextLevel(int $currentLevel): int {
    $levels = [100, 250, 500, 1000, 2000, 3500, 5500, 8000, 10000];
    if ($currentLevel >= 10) return 10000;
    return $levels[$currentLevel - 1] ?? 100;
}

function updateUserXP(int $userId, int $xpEarned): void {
    $pdo = getDB();

    // Get current XP and level
    $stmt = $pdo->prepare("SELECT xp, level FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $userData = $stmt->fetch();
    $currentXP = (int) $userData['xp'];
    $currentLevel = (int) $userData['level'];

    // Calculate new XP and level
    $newXP = $currentXP + $xpEarned;
    $newLevel = getLevelFromXP($newXP);

    // Update user
    $stmt = $pdo->prepare("UPDATE users SET xp = ?, level = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->execute([$newXP, $newLevel, $userId]);

    // Update session
    $_SESSION['xp'] = $newXP;
    $_SESSION['level'] = $newLevel;

    // Check for level-up achievements
    if ($newLevel > $currentLevel) {
        unlockAchievement($userId, "Level $newLevel", "Reached level $newLevel!");
    }
}

function getUserData(int $userId): ?array {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

function getUserStats(int $userId): array {
    $pdo = getDB();

    // Total quizzes taken
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM scores WHERE user_id = ?");
    $stmt->execute([$userId]);
    $totalQuizzes = (int) $stmt->fetchColumn();

    // Best score
    $stmt = $pdo->prepare("SELECT MAX(score) as best_score FROM scores WHERE user_id = ?");
    $stmt->execute([$userId]);
    $bestScore = (int) $stmt->fetchColumn();

    // Recent quiz history
    $stmt = $pdo->prepare("
        SELECT s.*, c.name as category_name
        FROM scores s
        LEFT JOIN categories c ON s.category_id = c.id
        WHERE s.user_id = ?
        ORDER BY s.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$userId]);
    $recentQuizzes = $stmt->fetchAll();

    return [
        'total_quizzes' => $totalQuizzes,
        'best_score' => $bestScore,
        'recent_quizzes' => $recentQuizzes
    ];
}

function unlockAchievement(int $userId, string $achievementName, string $description): void {
    $pdo = getDB();

    // Check if already earned
    $stmt = $pdo->prepare("SELECT id FROM achievements WHERE user_id = ? AND achievement_name = ?");
    $stmt->execute([$userId, $achievementName]);

    if (!$stmt->fetch()) {
        $now = new DateTime('now', new DateTimeZone('UTC'));
        $earnedAt = $now->format('Y-m-d H:i:s');
        $stmt = $pdo->prepare("INSERT INTO achievements (user_id, achievement_name, achievement_description, earned_at) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $achievementName, $description, $earnedAt]);
    }
}

function getUserAchievements(int $userId): array {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM achievements WHERE user_id = ? ORDER BY earned_at DESC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}
