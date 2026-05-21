<?php
session_start();

// Destroy all session data
$_SESSION = array();

// Delete session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Destroy session
session_destroy();

// Redirect to home
header('Location: index.php');
exit;
