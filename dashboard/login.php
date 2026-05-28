<?php
error_reporting(0);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Already logged in as admin
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === 1) {
    header('Location: /dashboard/index.php');
    exit;
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $db = getDB();
        // FIXED: column is password_hash not password
        $stmt = $db->prepare("SELECT id, username, password_hash, is_admin FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash']) && $user['is_admin'] == 1) {
            $_SESSION['is_admin'] = 1;
            $_SESSION['admin_username'] = $user['username'];
            header('Location: /dashboard/index.php');
            exit;
        } else {
            $error = 'Invalid credentials or not an admin account';
        }
    } else {
        $error = 'Please enter username and password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Bayani World</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-900 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-gray-800 rounded-2xl shadow-2xl p-6 lg:p-8">
            <div class="text-center mb-6 lg:mb-8">
                <h1 class="text-2xl lg:text-3xl font-bold text-[#0038A8] mb-2 truncate">
                    <i class="fas fa-shield-halved mr-2"></i>Bayani World
                </h1>
                <p class="text-gray-400 text-sm lg:text-base">Admin Dashboard</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-900/30 border border-red-600 rounded-lg p-4 mb-6">
                    <p class="text-red-400 text-center">
                        <i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error); ?>
                    </p>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-6">
                    <label class="block text-gray-300 text-sm font-bold mb-2">
                        <i class="fas fa-user mr-2"></i>Username
                    </label>
                    <input
                        type="text"
                        name="username"
                        required
                        autocomplete="username"
                        class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-3 focus:outline-none focus:border-[#0038A8] focus:ring-1 focus:ring-[#0038A8]"
                        placeholder="Enter your username"
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                    >
                </div>

                <div class="mb-6">
                    <label class="block text-gray-300 text-sm font-bold mb-2">
                        <i class="fas fa-lock mr-2"></i>Password
                    </label>
                    <input
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-3 focus:outline-none focus:border-[#0038A8] focus:ring-1 focus:ring-[#0038A8]"
                        placeholder="Enter your password"
                    >
                </div>

                <button
                    type="submit"
                    class="w-full bg-[#0038A8] hover:bg-[#0047b3] text-white font-bold py-3 px-4 rounded-lg transition duration-300"
                >
                    <i class="fas fa-sign-in-alt mr-2"></i>Login
                </button>
            </form>

            <div class="text-center mt-6">
                <a href="/" class="text-gray-400 hover:text-[#0038A8] transition">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Bayani World
                </a>
            </div>
        </div>

        <p class="text-center text-gray-500 text-sm mt-6">
            Bayani World Admin Dashboard © <?php echo date('Y'); ?>
        </p>
    </div>
</body>
</html>