<?php
ob_start();
session_start();
require_once 'includes/functions.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit;
}

$error = '';

// Show banned message if redirected due to ban
if (isset($_GET['banned'])) {
    $error = 'Your account has been banned. Please contact support if you believe this is an error.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($login) || empty($password)) {
        $error = 'Please enter both email/username and password.';
    } else {
        $pdo = getDB();
        // Check by username or email
        $stmt = $pdo->prepare("SELECT id, username, email, password_hash, hero_class, xp, level, coins, player_hp, player_max_hp, base_attack, base_defense, base_speed, base_magic, battle_warning_dismissed, COALESCE(is_banned, 0) as is_banned, ban_reason FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$login, $login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Check if user is banned
            if ($user['is_banned']) {
                $error = $user['ban_reason'] ? 'Your account has been banned. Reason: ' . htmlspecialchars($user['ban_reason']) : 'Your account has been banned.';
            } else {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['hero_class'] = $user['hero_class'];
                $_SESSION['xp'] = $user['xp'];
                $_SESSION['level'] = $user['level'];
                $_SESSION['coins'] = $user['coins'] ?? 0;
                $_SESSION['player_hp'] = $user['player_hp'] ?? 100;
                $_SESSION['player_max_hp'] = $user['player_max_hp'] ?? 100;
                $_SESSION['base_attack'] = $user['base_attack'] ?? 10;
                $_SESSION['base_defense'] = $user['base_defense'] ?? 5;
                $_SESSION['base_speed'] = $user['base_speed'] ?? 10;
                $_SESSION['base_magic'] = $user['base_magic'] ?? 5;
                $_SESSION['battle_warning_dismissed'] = $user['battle_warning_dismissed'] ?? 0;

                // Redirect to profile if hero chosen, otherwise to hero selection
                $redirectUrl = $user['hero_class'] ? 'profile.php' : 'choose-hero.php';
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                header("Location: $protocol://$host/$redirectUrl");
                exit;
            }
        } else {
            $error = 'Invalid email/username or password.';
        }
    }
}

require_once 'includes/header.php';
?>

<main class="min-h-screen bg-gray-50 py-12 px-4">
    <div class="max-w-md mx-auto">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold font-serif text-[#0038A8] mb-2">
                    Welcome Back
                </h1>
                <p class="text-gray-600">Login to continue your journey</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 border-2 border-red-500 rounded-xl p-4 mb-6 text-center">
                    <i class="fas fa-exclamation-circle text-red-500 text-xl mb-2"></i>
                    <p class="text-red-700 font-medium"><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-2 text-[#0038A8]"></i>Username or Email
                    </label>
                    <input type="text" name="login" 
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#0038A8] focus:outline-none transition"
                           placeholder="Enter username or email"
                           value="<?php echo htmlspecialchars($_POST['login'] ?? ''); ?>"
                           required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2 text-[#0038A8]"></i>Password
                    </label>
                    <input type="password" name="password" 
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#0038A8] focus:outline-none transition"
                           placeholder="Enter your password"
                           required>
                </div>

                <button type="submit" 
                        class="w-full bg-[#0038A8] text-white py-4 rounded-xl font-bold hover:bg-[#002870] transition transform hover:scale-105 shadow-lg">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login
                </button>
            </form>

            <div class="text-center mt-6">
                <p class="text-gray-600">
                    Don't have an account? 
                    <a href="register.php" class="text-[#0038A8] font-bold hover:underline">Register here</a>
                </p>
            </div>
        </div>

        <div class="text-center mt-6">
            <a href="index.php" class="text-gray-600 hover:text-[#0038A8] transition">
                <i class="fas fa-arrow-left mr-2"></i>Back to Home
            </a>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
