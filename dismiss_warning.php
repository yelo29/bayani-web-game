<?php
session_start();
require_once 'includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $pdo = getDB();
    $stmt = $pdo->prepare("UPDATE users SET battle_warning_dismissed = 1 WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $_SESSION['battle_warning_dismissed'] = 1;
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
}
