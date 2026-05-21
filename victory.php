<?php
session_start();
require_once 'includes/functions.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if battle was active
if (!isset($_SESSION['battle_started'])) {
    header('Location: mundo.php');
    exit;
}

$pdo = getDB();
$enemyId = $_SESSION['battle_enemy_id'];
$regionId = $_SESSION['battle_region_id'];

// Get enemy data
$stmt = $pdo->prepare("SELECT * FROM enemies WHERE id = ?");
$stmt->execute([$enemyId]);
$enemy = $stmt->fetch();

if (!$enemy) {
    header('Location: mundo.php');
    exit;
}

// Get user data for coins
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Award XP with scroll bonus
$baseXp = $enemy['xp_reward'];
$scroll = getEquippedItem($_SESSION['user_id'], 'scroll');
$xpMultiplier = $scroll ? (1 + $scroll['power'] / 100) : 1;
$xpEarned = round($baseXp * $xpMultiplier);
updateUserXP($_SESSION['user_id'], $xpEarned);

// Award coins (50 for win, 100 for perfect battle)
$coinsEarned = 50;
// Check if perfect battle (all 5 rounds correct)
$correctAnswers = 0;
$totalRounds = 5;
// We can track this in session during battle, for now just award 50
$stmt = $pdo->prepare("UPDATE users SET coins = coins + ? WHERE id = ?");
$stmt->execute([$coinsEarned, $_SESSION['user_id']]);

// Increment streak
$stmt = $pdo->prepare("SELECT current_streak, best_streak FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$userStreak = $stmt->fetch();

$newCurrentStreak = ($userStreak['current_streak'] ?? 0) + 1;
$newBestStreak = max($userStreak['best_streak'] ?? 0, $newCurrentStreak);

$stmt = $pdo->prepare("UPDATE users SET current_streak = ?, best_streak = ? WHERE id = ?");
$stmt->execute([$newCurrentStreak, $newBestStreak, $_SESSION['user_id']]);

// Update session
$_SESSION['current_streak'] = $newCurrentStreak;
$_SESSION['best_streak'] = $newBestStreak;
$_SESSION['coins'] = ($user['coins'] ?? 0) + $coinsEarned;

// Random item drop (30% chance)
$itemDropped = null;
$dropChance = rand(1, 100);
if ($dropChance <= 30) {
    // Get random item
    $stmt = $pdo->prepare("
        SELECT * FROM items 
        WHERE region_id IS NULL OR region_id = ?
        ORDER BY RAND() 
        LIMIT 1
    ");
    $stmt->execute([$regionId]);
    $item = $stmt->fetch();
    
    if ($item) {
        // Add to inventory
        $stmt = $pdo->prepare("
            INSERT INTO inventory (user_id, item_id, equipped)
            VALUES (?, ?, 0)
        ");
        $stmt->execute([$_SESSION['user_id'], $item['id']]);
        $itemDropped = $item;
    }
}

// Log battle
$stmt = $pdo->prepare("
    INSERT INTO battle_log (user_id, enemy_id, won, xp_earned)
    VALUES (?, ?, 1, ?)
");
$stmt->execute([$_SESSION['user_id'], $enemyId, $xpEarned]);

// Update region progress
$stmt = $pdo->prepare("
    INSERT INTO region_progress (user_id, region_id, battles_won)
    VALUES (?, ?, 1)
    ON DUPLICATE KEY UPDATE battles_won = battles_won + 1
");
$stmt->execute([$_SESSION['user_id'], $regionId]);

// Clear battle session
unset($_SESSION['battle_started']);
unset($_SESSION['battle_region_id']);
unset($_SESSION['battle_enemy_id']);
unset($_SESSION['battle_player_hp']);
unset($_SESSION['battle_player_max_hp']);
unset($_SESSION['battle_enemy_hp']);
unset($_SESSION['battle_enemy_max_hp']);
unset($_SESSION['battle_log']);
unset($_SESSION['battle_used_questions']);

require_once 'includes/header.php';
?>

<main class="min-h-screen bg-gradient-to-br from-yellow-400 to-yellow-600 py-8 px-4">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-2xl shadow-2xl p-8 text-center">
            <!-- Victory Icon -->
            <div class="w-32 h-32 bg-yellow-400 rounded-full mx-auto mb-6 flex items-center justify-center">
                <i class="fas fa-trophy text-white text-6xl"></i>
            </div>
            
            <h1 class="text-4xl font-bold font-serif text-[#0038A8] mb-4">Tagumpay!</h1>
            <p class="text-2xl text-gray-800 mb-2">
                Natalo mo si <span class="font-bold text-[#CE1126]"><?php echo htmlspecialchars($enemy['name']); ?></span>!
            </p>
            
            <!-- XP Earned -->
            <div class="bg-yellow-50 rounded-xl p-6 mb-6">
                <p class="text-5xl font-bold text-yellow-500 mb-2">+<?php echo $xpEarned; ?></p>
                <p class="text-gray-600">XP Earned</p>
                <?php if ($scroll): ?>
                    <p class="text-sm text-yellow-600 mt-2">Scroll Bonus: +<?php echo $scroll['power']; ?>% XP</p>
                <?php endif; ?>
            </div>

            <!-- Coins Earned -->
            <div class="bg-green-50 rounded-xl p-6 mb-6">
                <p class="text-5xl font-bold text-green-500 mb-2">+<?php echo $coinsEarned; ?> 🪙</p>
                <p class="text-gray-600">Coins Earned</p>
            </div>

            <!-- Streak -->
            <div class="bg-orange-50 rounded-xl p-6 mb-6">
                <p class="text-5xl font-bold text-orange-500 mb-2">🔥 <?php echo $newCurrentStreak; ?></p>
                <p class="text-gray-600">Win Streak (Best: <?php echo $newBestStreak; ?>)</p>
            </div>

            <!-- Item Drop -->
            <?php if ($itemDropped): ?>
            <div class="bg-green-50 border-2 border-green-500 rounded-xl p-6 mb-6">
                <i class="fas fa-gift text-green-500 text-3xl mb-2"></i>
                <p class="text-green-700 font-bold mb-1">Item Drop!</p>
                <p class="text-gray-800">
                    Nakakuha ka ng <span class="font-bold"><?php echo htmlspecialchars($itemDropped['name']); ?></span>
                    (<?php echo htmlspecialchars($itemDropped['type']); ?>)
                </p>
                <p class="text-sm text-gray-600 mt-1">Power: <?php echo $itemDropped['power']; ?> | Rarity: <?php echo htmlspecialchars($itemDropped['rarity']); ?></p>
            </div>
            <?php endif; ?>
            
            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="battle.php?region_id=<?php echo $regionId; ?>&enemy_id=<?php echo $enemyId; ?>"
                   class="flex-1 bg-[#0038A8] text-white px-6 py-4 rounded-xl font-bold text-center hover:bg-[#002870] transition">
                    <i class="fas fa-redo mr-2"></i> Laban Ulit
                </a>
                <a href="mundo.php"
                   class="flex-1 bg-gray-200 text-gray-800 px-6 py-4 rounded-xl font-bold text-center hover:bg-gray-300 transition">
                    <i class="fas fa-map mr-2"></i> Mundo
                </a>
                <a href="tindahan.php"
                   class="flex-1 bg-green-500 text-white px-6 py-4 rounded-xl font-bold text-center hover:bg-green-600 transition">
                    <i class="fas fa-shopping-cart mr-2"></i> Tindahan
                </a>
                <a href="inventaryo.php"
                   class="flex-1 bg-yellow-400 text-[#0038A8] px-6 py-4 rounded-xl font-bold text-center hover:bg-yellow-300 transition">
                    <i class="fas fa-box mr-2"></i> Inventaryo
                </a>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
