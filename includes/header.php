<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and banned - force logout
if (isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/db.php';
    $db = getDB();
    $stmt = $db->prepare("SELECT COALESCE(is_banned, 0) as is_banned FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['is_banned']) {
        // Destroy session and redirect to login
        session_destroy();
        header('Location: /login.php?banned=1');
        exit;
    }
}

require_once __DIR__ . '/translations.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bayani World - Play. Learn. Be a Hero.</title>
    <meta name="description" content="An educational quiz game about Philippine history, heroes, and culture.">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/assets/icons/icon_192x192.png">

    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0038A8">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <link rel="apple-touch-icon" href="/assets/icons/icon_192x192.png">

    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/custom.css">

    <style>
        /* Page fade in animation */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        body {
            animation: fadeIn 0.5s ease-in;
        }

        /* Hamburger menu animation */
        .hamburger-line {
            transition: all 0.3s ease;
        }
        .hamburger-open .line1 {
            transform: rotate(45deg) translate(5px, 5px);
        }
        .hamburger-open .line2 {
            opacity: 0;
        }
        .hamburger-open .line3 {
            transform: rotate(-45deg) translate(5px, -5px);
        }

        /* Mobile menu slide down */
        .mobile-menu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }
        .mobile-menu.open {
            max-height: 500px;
        }

        /* Dropdown animations */
        .dropdown-menu {
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.2s ease;
        }
        .dropdown:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        /* HP bar shake animation */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        .shake {
            animation: shake 0.3s ease-in-out;
        }

        /* Loading spinner */
        .spinner {
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            display: inline-block;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="font-sans">
    <!-- Navigation -->
    <nav class="bg-[#0038A8] text-white py-3 px-4 shadow-lg sticky top-0 z-40">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <!-- Logo -->
            <a href="/index.php" class="text-2xl font-bold font-serif">Bayani World</a>

            <!-- Desktop Navigation -->
            <div class="hidden md:flex gap-6 items-center">
                <a href="/index.php" class="hover:text-yellow-400 transition text-base"><?php echo t('home'); ?></a>

                <!-- Leaderboard Dropdown -->
                <div class="dropdown relative">
                    <button class="hover:text-yellow-400 transition text-base flex items-center gap-1">
                        <?php echo t('leaderboard'); ?> <i class="fas fa-chevron-down text-xs"></i>
                    </button>
                    <div class="dropdown-menu absolute top-full left-0 mt-2 bg-yellow-100 rounded-xl shadow-xl min-w-[200px] overflow-hidden">
                        <a href="/leaderboard.php" class="block px-4 py-3 text-[#0038A8] hover:bg-yellow-200 transition">
                            <i class="fas fa-book-open mr-2"></i> <?php echo t('quiz_leaderboard'); ?>
                        </a>
                        <a href="/leaderboard.php?type=battle" class="block px-4 py-3 text-[#0038A8] hover:bg-yellow-200 transition">
                            <i class="fa-solid fa-person-military-rifle mr-2"></i> <?php echo t('battle_leaderboard'); ?>
                        </a>
                        <div class="border-t border-yellow-300 my-1"></div>
                        <a href="/leaderboard.php?type=agham" class="block px-4 py-3 text-[#0038A8] hover:bg-yellow-200 transition">
                            <i class="fas fa-flask mr-2"></i> Agham
                        </a>
                        <a href="/leaderboard.php?type=buhay" class="block px-4 py-3 text-[#0038A8] hover:bg-yellow-200 transition">
                            <i class="fas fa-map mr-2"></i> Buhay
                        </a>
                        <a href="/leaderboard.php?type=handa" class="block px-4 py-3 text-[#0038A8] hover:bg-yellow-200 transition">
                            <i class="fas fa-shield-alt mr-2"></i> Handa
                        </a>
                        <a href="/leaderboard.php?type=wikain" class="block px-4 py-3 text-[#0038A8] hover:bg-yellow-200 transition">
                            <i class="fas fa-book mr-2"></i> Wikain
                        </a>
                    </div>
                </div>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/inventaryo.php" class="hover:text-yellow-400 transition text-base"><?php echo t('inventory'); ?></a>
                    <a href="/tindahan.php" class="hover:text-yellow-400 transition text-base"><?php echo t('shop'); ?></a>
                    <a href="/profile.php" class="hover:text-yellow-400 transition text-base"><?php echo t('profile'); ?></a>
                <?php endif; ?>

                <!-- Language Toggle -->
                <form method="POST" action="/set_language.php" class="inline">
                    <button type="submit" class="hover:text-yellow-400 transition text-base flex items-center gap-1">
                        <i class="fas fa-globe"></i>
                        <?php echo ($_SESSION['lang'] ?? 'fil') === 'fil' ? 'Filipino' : 'English'; ?>
                    </button>
                </form>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="flex items-center gap-3 ml-4 pl-4 border-l border-white/30">
                        <span class="font-medium text-sm"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <?php if (isset($_SESSION['hero_class'])): ?>
                            <span class="px-2 py-1 bg-yellow-400 text-[#0038A8] rounded-full text-xs font-bold uppercase">
                                <?php echo htmlspecialchars($_SESSION['hero_class']); ?>
                            </span>
                        <?php endif; ?>
                        <span class="text-yellow-400 font-bold text-sm">🪙 <?php echo $_SESSION['coins'] ?? 0; ?></span>
                        <a href="/logout.php" class="hover:text-yellow-400 transition">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </div>
                <?php else: ?>
                    <a href="/login.php" class="hover:text-yellow-400 transition text-base"><?php echo t('login'); ?></a>
                    <a href="/register.php" class="bg-yellow-400 text-[#0038A8] px-4 py-2 rounded-full font-bold text-sm hover:bg-yellow-300 transition"><?php echo t('register'); ?></a>
                <?php endif; ?>
            </div>

            <!-- Hamburger Menu Button (Mobile) -->
            <button id="hamburgerBtn" class="md:hidden text-white text-2xl focus:outline-none">
                <div class="w-6 h-5 flex flex-col justify-between">
                    <span class="hamburger-line line1 w-full h-0.5 bg-white"></span>
                    <span class="hamburger-line line2 w-full h-0.5 bg-white"></span>
                    <span class="hamburger-line line3 w-full h-0.5 bg-white"></span>
                </div>
            </button>
        </div>

        <!-- Mobile Menu -->
        <div id="mobileMenu" class="mobile-menu md:hidden bg-[#0038A8] border-t border-white/10">
            <div class="px-4 py-4 space-y-3">
                <a href="/index.php" class="block py-3 px-4 hover:bg-white/10 rounded-lg transition"><?php echo t('home'); ?></a>

                <!-- Leaderboard Section -->
                <div class="py-2">
                    <p class="text-yellow-400 font-bold mb-2 px-4"><?php echo t('leaderboard'); ?> </p>
                    <a href="/leaderboard.php" class="block py-3 px-4 hover:bg-white/10 rounded-lg transition pl-8">
                        <i class="fas fa-book-open mr-2"></i> <?php echo t('quiz_leaderboard'); ?>
                    </a>
                    <a href="/leaderboard.php?type=battle" class="block py-3 px-4 hover:bg-white/10 rounded-lg transition pl-8">
                        <i class="fas fa-swords mr-2"></i> <?php echo t('battle_leaderboard'); ?>
                    </a>
                    <a href="/leaderboard.php?type=agham" class="block py-3 px-4 hover:bg-white/10 rounded-lg transition pl-8">
                        <i class="fas fa-flask mr-2"></i> Agham
                    </a>
                    <a href="/leaderboard.php?type=buhay" class="block py-3 px-4 hover:bg-white/10 rounded-lg transition pl-8">
                        <i class="fas fa-map mr-2"></i> Buhay
                    </a>
                    <a href="/leaderboard.php?type=handa" class="block py-3 px-4 hover:bg-white/10 rounded-lg transition pl-8">
                        <i class="fas fa-shield-alt mr-2"></i> Handa
                    </a>
                    <a href="/leaderboard.php?type=wikain" class="block py-3 px-4 hover:bg-white/10 rounded-lg transition pl-8">
                        <i class="fas fa-book mr-2"></i> Wikain
                    </a>
                </div>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/inventaryo.php" class="block py-3 px-4 hover:bg-white/10 rounded-lg transition"><?php echo t('inventory'); ?></a>
                    <a href="/tindahan.php" class="block py-3 px-4 hover:bg-white/10 rounded-lg transition"><?php echo t('shop'); ?></a>
                    <a href="/profile.php" class="block py-3 px-4 hover:bg-white/10 rounded-lg transition"><?php echo t('profile'); ?></a>
                <?php endif; ?>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="border-t border-white/10 pt-4 mt-4">
                        <div class="flex items-center gap-3 px-4 mb-3">
                            <span class="font-medium"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                            <?php if (isset($_SESSION['hero_class'])): ?>
                                <span class="px-2 py-1 bg-yellow-400 text-[#0038A8] rounded-full text-xs font-bold uppercase">
                                    <?php echo htmlspecialchars($_SESSION['hero_class']); ?>
                                </span>
                            <?php endif; ?>
                            <span class="text-yellow-400 font-bold">🪙 <?php echo $_SESSION['coins'] ?? 0; ?></span>
                        </div>
                        <a href="/logout.php" class="block py-3 px-4 hover:bg-white/10 rounded-lg transition text-red-300">
                            <i class="fas fa-sign-out-alt mr-2"></i> <?php echo t('logout'); ?>
                        </a>
                    </div>
                <?php else: ?>
                    <a href="/login.php" class="block py-3 px-4 hover:bg-white/10 rounded-lg transition"><?php echo t('login'); ?></a>
                    <a href="/register.php" class="block py-3 px-4 bg-yellow-400 text-[#0038A8] rounded-lg font-bold text-center"><?php echo t('register'); ?></a>
                <?php endif; ?>

                <!-- Language Toggle -->
                <div class="border-t border-white/10 pt-4 mt-4">
                    <form method="POST" action="/set_language.php">
                        <button type="submit" class="w-full py-3 px-4 hover:bg-white/10 rounded-lg transition text-left">
                            <i class="fas fa-globe mr-2"></i>
                            <?php echo ($_SESSION['lang'] ?? 'fil') === 'fil' ? '🌐 Filipino' : '🌐 English'; ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <script>
        // Hamburger menu toggle
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const mobileMenu = document.getElementById('mobileMenu');

        hamburgerBtn.addEventListener('click', () => {
            hamburgerBtn.classList.toggle('hamburger-open');
            mobileMenu.classList.toggle('open');
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!hamburgerBtn.contains(e.target) && !mobileMenu.contains(e.target)) {
                hamburgerBtn.classList.remove('hamburger-open');
                mobileMenu.classList.remove('open');
            }
        });

        // Add loading spinner to forms
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"], input[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<span class="spinner"></span> Loading...';
                }
            });
        });
    </script>
