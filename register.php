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
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate inputs
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'All fields are required.';
    } elseif (strlen($username) < 3) {
        $error = 'Username must be at least 3 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // Check if username or email already exists
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);

        if ($stmt->fetch()) {
            $error = 'Username or email already exists.';
        } else {
            // Create user
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");

            if ($stmt->execute([$username, $email, $password_hash])) {
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['username'] = $username;
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                header("Location: $protocol://$host/choose-hero.php");
                exit;
            } else {
                $error = 'Registration failed. Please try again.';
            }
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
                    Join Bayani World
                </h1>
                <p class="text-gray-600">Create your account and start your journey</p>
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
                        <i class="fas fa-user mr-2 text-[#0038A8]"></i>Username
                    </label>
                    <input type="text" name="username" 
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#0038A8] focus:outline-none transition"
                           placeholder="Choose a username"
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                           required
                           minlength="3">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2 text-[#0038A8]"></i>Email
                    </label>
                    <input type="email" name="email" 
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#0038A8] focus:outline-none transition"
                           placeholder="your@email.com"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2 text-[#0038A8]"></i>Password
                    </label>
                    <input type="password" name="password" 
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#0038A8] focus:outline-none transition"
                           placeholder="Min. 6 characters"
                           required
                           minlength="6">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2 text-[#0038A8]"></i>Confirm Password
                    </label>
                    <input type="password" name="confirm_password" 
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#0038A8] focus:outline-none transition"
                           placeholder="Re-enter password"
                           required
                           minlength="6">
                </div>

                <button type="submit" 
                        class="w-full bg-[#0038A8] text-white py-4 rounded-xl font-bold hover:bg-[#002870] transition transform hover:scale-105 shadow-lg">
                    <i class="fas fa-user-plus mr-2"></i>Create Account
                </button>
            </form>

            <div class="text-center mt-6">
                <p class="text-gray-600">
                    Already have an account? 
                    <a href="login.php" class="text-[#0038A8] font-bold hover:underline">Login here</a>
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
