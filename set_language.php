<?php
ob_start();
session_start();
require_once 'includes/translations.php';

$_SESSION['lang'] = ($_SESSION['lang'] ?? 'fil') === 'fil' ? 'en' : 'fil';
header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
exit;
