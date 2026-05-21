<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bayani World - Play. Learn. Be a Hero.</title>
    <meta name="description" content="An educational quiz game about Philippine history, heroes, and culture.">
    
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/custom.css">
</head>
<body class="font-sans">
    <!-- Navigation -->
    <nav class="bg-[#0038A8] text-white py-4 px-6 shadow-lg">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold font-serif">Bayani World</a>
            <div class="flex gap-4 items-center">
                <a href="index.php" class="hover:text-yellow-400 transition">Home</a>
                <a href="leaderboard.php" class="hover:text-yellow-400 transition">Leaderboard</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="mundo.php" class="hover:text-yellow-400 transition">Mundo</a>
                    <a href="tindahan.php" class="hover:text-yellow-400 transition">Tindahan</a>
                    <a href="inventaryo.php" class="hover:text-yellow-400 transition">Inventaryo</a>
                <?php endif; ?>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="profile.php" class="hover:text-yellow-400 transition">Profile</a>
                    <div class="flex items-center gap-2 ml-4 pl-4 border-l border-white/30">
                        <span class="font-medium"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <?php if (isset($_SESSION['hero_class'])): ?>
                            <span class="px-2 py-1 bg-yellow-400 text-[#0038A8] rounded-full text-xs font-bold uppercase">
                                <?php echo htmlspecialchars($_SESSION['hero_class']); ?>
                            </span>
                        <?php endif; ?>
                        <span class="text-yellow-400 font-bold">🪙 <?php echo $_SESSION['coins'] ?? 0; ?></span>
                        <a href="logout.php" class="hover:text-yellow-400 transition ml-2">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="hover:text-yellow-400 transition">Login</a>
                    <a href="register.php" class="bg-yellow-400 text-[#0038A8] px-4 py-2 rounded-full font-bold hover:bg-yellow-300 transition">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
