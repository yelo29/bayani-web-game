<?php
// Helper Functions

require_once 'db.php';

function getCategories(): array {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT id, name, description, icon, color FROM categories ORDER BY id ASC");
    return $stmt->fetchAll();
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
    $stmt = $pdo->prepare("
        INSERT INTO scores (player_name, category_id, score, total_questions, time_taken)
        VALUES (?, ?, ?, ?, ?)
    ");
    return $stmt->execute([$name, $categoryId, $score, $total, $timeTaken]);
}

function getLeaderboard(int $categoryId = null, int $limit = 10, int $offset = 0): array {
    $pdo = getDB();
    
    if ($categoryId === null) {
        $stmt = $pdo->prepare("
            SELECT s.*, c.name as category_name
            FROM scores s
            LEFT JOIN categories c ON s.category_id = c.id
            ORDER BY s.score DESC, s.time_taken ASC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
    } else {
        $stmt = $pdo->prepare("
            SELECT s.*, c.name as category_name
            FROM scores s
            LEFT JOIN categories c ON s.category_id = c.id
            WHERE s.category_id = ?
            ORDER BY s.score DESC, s.time_taken ASC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$categoryId, $limit, $offset]);
    }
    
    return $stmt->fetchAll();
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
