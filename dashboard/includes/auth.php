<?php
error_reporting(0);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== 1) {
    header('Location: /dashboard/login.php');
    exit;
}

// Check if admin user is banned
require_once __DIR__ . '/../../includes/db.php';
$db = getDB();
$stmt = $db->prepare("SELECT COALESCE(is_banned, 0) as is_banned FROM users WHERE username = ?");
$stmt->execute([$_SESSION['admin_username']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin && $admin['is_banned']) {
    session_destroy();
    header('Location: /dashboard/login.php?banned=1');
    exit;
}
