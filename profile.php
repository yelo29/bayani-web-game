<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/header.php';
require_once 'includes/functions.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Refresh session data from database
refreshSessionData();

// Get user data
$userId = $_SESSION['user_id'];
$userData = getUserData($userId);
$userStats = getUserStats($userId);
$achievements = getUserAchievements($userId);
$pdo = getDB();

// Get equipped items
$equippedWeapon = null;
$equippedArmor = null;
$equippedScroll = null;
$stmt = $pdo->prepare("
    SELECT i.name, i.power, i.type, i.description
    FROM inventory inv
    JOIN items i ON inv.item_id = i.id
    WHERE inv.user_id = ? AND inv.equipped = 1 AND i.type = 'weapon'
    LIMIT 1
");
$stmt->execute([$userId]);
$equippedWeapon = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT i.name, i.power, i.type, i.description
    FROM inventory inv
    JOIN items i ON inv.item_id = i.id
    WHERE inv.user_id = ? AND inv.equipped = 1 AND i.type = 'armor'
    LIMIT 1
");
$stmt->execute([$userId]);
$equippedArmor = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT i.name, i.power, i.type, i.description
    FROM inventory inv
    JOIN items i ON inv.item_id = i.id
    WHERE inv.user_id = ? AND inv.equipped = 1 AND i.type = 'scroll'
    LIMIT 1
");
$stmt->execute([$userId]);
$equippedScroll = $stmt->fetch();

// Calculate total stats with class-specific base stats
$playerLevel = $userData['level'] ?? 1;
$heroClass = $userData['hero_class'] ?? null;

// Use class-specific base stats
$baseAttack = ($userData['base_attack'] ?? 10) + ($playerLevel * 5);
$baseDefense = ($userData['base_defense'] ?? 5) + ($playerLevel * 2);
$baseSpeed = ($userData['base_speed'] ?? 10) + ($playerLevel * 1);
$baseMagic = ($userData['base_magic'] ?? 5) + ($playerLevel * 5);

$weaponBonus = $equippedWeapon ? $equippedWeapon['power'] : 0;
$armorBonus = $equippedArmor ? $equippedArmor['power'] : 0;
$scrollBonus = $equippedScroll ? $equippedScroll['power'] : 0;

// Mangkukulam uses magic instead of attack for damage
// Weapon power adds to magic for Mangkukulam, not attack
if ($heroClass === 'mangkukulam') {
    $magicBonus = $weaponBonus;
    $weaponBonus = 0;
    $totalAttack = $baseMagic + $magicBonus;
} else {
    $magicBonus = 0;
    $totalAttack = $baseAttack + $weaponBonus;
}
$totalDefense = $baseDefense + $armorBonus;
$totalSpeed = $baseSpeed;
$totalMagic = $baseMagic + $magicBonus;

// Get battle stats
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_battles,
        SUM(won) as battles_won,
        SUM(1 - won) as battles_lost,
        AVG(xp_earned) as avg_xp
    FROM battle_log
    WHERE user_id = ?
");
$stmt->execute([$userId]);
$battleStats = $stmt->fetch();
$winRate = $battleStats['total_battles'] > 0 ? round(($battleStats['battles_won'] / $battleStats['total_battles']) * 100, 1) : 0;

// Get recent battle history
$stmt = $pdo->prepare("
    SELECT bl.*, e.name as enemy_name, e.era
    FROM battle_log bl
    JOIN enemies e ON bl.enemy_id = e.id
    WHERE bl.user_id = ?
    ORDER BY bl.created_at DESC
    LIMIT 5
");
$stmt->execute([$userId]);
$recentBattles = $stmt->fetchAll();

// Get quiz stats
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_quizzes,
        MAX(score) as best_score,
        AVG(score) as avg_score
    FROM scores
    WHERE user_id = ?
");
$stmt->execute([$userId]);
$quizStats = $stmt->fetch();

// Hero class info
$heroClasses = [
    'mandirigma' => ['name' => 'Mandirigma', 'title' => 'Warrior', 'color' => '#CE1126', 'icon' => 'fa-shield-alt'],
    'lakambini' => ['name' => 'Lakambini', 'title' => 'Scholar', 'color' => '#0038A8', 'icon' => 'fa-book'],
    'mangkukulam' => ['name' => 'Mangkukulam', 'title' => 'Mystic', 'color' => '#FCD116', 'icon' => 'fa-bolt']
];

$currentHero = $heroClasses[$userData['hero_class']] ?? null;
?>

<main class="min-h-screen bg-gray-50 py-8 px-4">
    <div class="max-w-5xl mx-auto">
        <!-- Profile Header -->
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-6">
            <div class="flex flex-col md:flex-row items-center gap-8">
                <div class="w-32 h-32 rounded-full flex items-center justify-center text-white text-5xl" style="background: <?php echo $currentHero ? $currentHero['color'] : '#0038A8'; ?>;">
                    <i class="fas <?php echo $currentHero ? $currentHero['icon'] : 'fa-user'; ?>"></i>
                </div>
                <div class="flex-1 text-center md:text-left">
                    <h1 class="text-4xl font-bold font-serif text-[#0038A8] mb-2">
                        <?php echo htmlspecialchars($userData['username']); ?>
                    </h1>
                    <?php if ($currentHero): ?>
                        <p class="text-xl text-gray-600 mb-2">
                            <span class="px-3 py-1 rounded-full text-white font-bold" style="background: <?php echo $currentHero['color']; ?>;">
                                <?php echo $currentHero['name']; ?> - <?php echo $currentHero['title']; ?>
                            </span>
                        </p>
                    <?php endif; ?>
                    <p class="text-gray-500">Member since <?php
                    try {
                        $date = new DateTime($userData['created_at'], new DateTimeZone('UTC'));
                        $date->setTimezone(new DateTimeZone('Asia/Manila'));
                        echo $date->format('F d, Y h:i A');
                    } catch (Exception $e) {
                        echo 'Invalid date';
                    }
                    ?></p>
                </div>
                <div class="text-center">
                    <p class="text-5xl font-bold text-[#0038A8]"><?php echo $userData['level']; ?></p>
                    <p class="text-gray-600">Level</p>
                </div>
            </div>
        </div>

        <!-- XP Progress -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <h3 class="text-xl font-bold text-[#0038A8] mb-4">Experience Progress</h3>
            <div class="flex items-center justify-between mb-4">
                <div class="text-center">
                    <p class="text-3xl font-bold text-green-500"><?php echo $userData['xp']; ?></p>
                    <p class="text-sm text-gray-600">Total XP</p>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-bold text-[#0038A8]"><?php echo $userData['level']; ?></p>
                    <p class="text-sm text-gray-600">Current Level</p>
                </div>
                <div class="text-center">
                    <?php
                    $nextLevelXP = getXPForNextLevel($userData['level']);
                    $prevLevelXP = $userData['level'] === 1 ? 0 : getXPForNextLevel($userData['level'] - 1);
                    $xpToNext = $userData['level'] >= 10 ? 0 : $nextLevelXP - $userData['xp'];
                    ?>
                    <p class="text-3xl font-bold text-yellow-500"><?php echo $xpToNext; ?></p>
                    <p class="text-sm text-gray-600">XP to Next Level</p>
                </div>
            </div>
            <?php
            $progress = $userData['level'] >= 10 ? 100 : (($userData['xp'] - $prevLevelXP) / ($nextLevelXP - $prevLevelXP)) * 100;
            ?>
            <div class="w-full bg-gray-200 rounded-full h-6">
                <div class="bg-gradient-to-r from-yellow-400 to-yellow-600 h-6 rounded-full transition-all duration-1000" style="width: <?php echo $progress; ?>%"></div>
            </div>
            <p class="text-sm text-gray-600 mt-2 text-center">
                <?php if ($userData['level'] >= 10): ?>
                    🏆 Bayani ng Bayan - Maximum Level Achieved!
                <?php else: ?>
                    Level <?php echo $userData['level']; ?> → Level <?php echo $userData['level'] + 1; ?>
                <?php endif; ?>
            </p>
        </div>

        <!-- HP Bar -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <h3 class="text-xl font-bold text-[#0038A8] mb-4">Health Points</h3>
            <div class="flex items-center justify-between mb-4">
                <div class="text-center">
                    <p class="text-3xl font-bold text-red-500"><?php echo $userData['player_hp'] ?? 100; ?></p>
                    <p class="text-sm text-gray-600">Current HP</p>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-bold text-gray-800"><?php echo $userData['player_max_hp'] ?? 100; ?></p>
                    <p class="text-sm text-gray-600">Max HP</p>
                </div>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-6">
                <div class="bg-gradient-to-r from-red-400 to-red-600 h-6 rounded-full transition-all duration-1000" style="width: <?php echo (($userData['player_hp'] ?? 100) / ($userData['player_max_hp'] ?? 100)) * 100; ?>%"></div>
            </div>
        </div>

        <!-- Equipped Items -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <h3 class="text-xl font-bold text-[#0038A8] mb-4">Equipped Items</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Weapon -->
                <div class="bg-red-50 border-2 border-red-200 rounded-xl p-4">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center text-white">
                            <i class="fa-solid fa-person-military-rifle text-xl"></i>
                        </div>
                        <div>
                            <p class="font-bold text-gray-800"><?php echo $equippedWeapon ? htmlspecialchars($equippedWeapon['name']) : 'Walang Armas'; ?></p>
                            <p class="text-sm text-gray-600">Weapon</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600">
                        <?php if ($heroClass === 'mangkukulam'): ?>
                            Magic Power: <span class="font-bold text-purple-600">+<?php echo $magicBonus; ?></span>
                        <?php else: ?>
                            Power: <span class="font-bold text-red-600">+<?php echo $weaponBonus; ?></span>
                        <?php endif; ?>
                    </p>
                </div>
                <!-- Armor -->
                <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-4">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center text-white">
                            <i class="fas fa-shield-alt text-xl"></i>
                        </div>
                        <div>
                            <p class="font-bold text-gray-800"><?php echo $equippedArmor ? htmlspecialchars($equippedArmor['name']) : 'Walang Armor'; ?></p>
                            <p class="text-sm text-gray-600">Armor</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600">
                        Defense: <span class="font-bold text-blue-600">+<?php echo $armorBonus; ?></span>
                    </p>
                </div>
                <!-- Scroll -->
                <div class="bg-yellow-50 border-2 border-yellow-200 rounded-xl p-4">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center text-white">
                            <i class="fas fa-scroll text-xl"></i>
                        </div>
                        <div>
                            <p class="font-bold text-gray-800"><?php echo $equippedScroll ? htmlspecialchars($equippedScroll['name']) : 'Walang Scroll'; ?></p>
                            <p class="text-sm text-gray-600">Scroll</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600">
                        XP Bonus: <span class="font-bold text-yellow-600">+<?php echo $scrollBonus; ?>%</span>
                    </p>
                </div>
            </div>
            <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center p-3 bg-orange-50 rounded-xl">
                    <p class="text-2xl font-bold text-orange-600"><?php echo $totalAttack; ?></p>
                    <p class="text-sm text-gray-600">Attack</p>
                    <?php if ($heroClass === 'mangkukulam'): ?>
                        <p class="text-xs text-gray-500 mt-1">Magic Base: <?php echo $baseMagic; ?> + Weapon: <?php echo $magicBonus; ?></p>
                    <?php else: ?>
                        <p class="text-xs text-gray-500 mt-1">Base: <?php echo $baseAttack; ?> + Weapon: <?php echo $weaponBonus; ?></p>
                    <?php endif; ?>
                </div>
                <div class="text-center p-3 bg-blue-50 rounded-xl">
                    <p class="text-2xl font-bold text-blue-600"><?php echo $totalDefense; ?></p>
                    <p class="text-sm text-gray-600">Defense</p>
                    <p class="text-xs text-gray-500 mt-1">Base: <?php echo $baseDefense; ?> + Armor: <?php echo $armorBonus; ?></p>
                </div>
                <div class="text-center p-3 bg-green-50 rounded-xl">
                    <p class="text-2xl font-bold text-green-600"><?php echo $totalSpeed; ?></p>
                    <p class="text-sm text-gray-600">Speed</p>
                    <p class="text-xs text-gray-500 mt-1">Base: <?php echo $baseSpeed; ?></p>
                </div>
                <div class="text-center p-3 bg-purple-50 rounded-xl">
                    <p class="text-2xl font-bold text-purple-600"><?php echo $totalMagic; ?></p>
                    <p class="text-sm text-gray-600">Magic</p>
                    <?php if ($heroClass === 'mangkukulam'): ?>
                        <p class="text-xs text-gray-500 mt-1">Base: <?php echo $baseMagic; ?> + Weapon: <?php echo $magicBonus; ?></p>
                    <?php else: ?>
                        <p class="text-xs text-gray-500 mt-1">Base: <?php echo $baseMagic; ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Battle Stats -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <h3 class="text-xl font-bold text-[#0038A8] mb-4">Battle Statistics</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="text-center p-4 bg-gray-50 rounded-xl">
                    <p class="text-3xl font-bold text-gray-800"><?php echo $battleStats['total_battles']; ?></p>
                    <p class="text-sm text-gray-600">Total Battles</p>
                </div>
                <div class="text-center p-4 bg-green-50 rounded-xl">
                    <p class="text-3xl font-bold text-green-600"><?php echo $battleStats['battles_won']; ?></p>
                    <p class="text-sm text-gray-600">Battles Won</p>
                </div>
                <div class="text-center p-4 bg-red-50 rounded-xl">
                    <p class="text-3xl font-bold text-red-600"><?php echo $battleStats['battles_lost']; ?></p>
                    <p class="text-sm text-gray-600">Battles Lost</p>
                </div>
                <div class="text-center p-4 bg-yellow-50 rounded-xl">
                    <p class="text-3xl font-bold text-yellow-600"><?php echo $winRate; ?>%</p>
                    <p class="text-sm text-gray-600">Win Rate</p>
                </div>
            </div>
            <h4 class="font-bold text-gray-800 mb-3">Recent Battle History</h4>
            <?php if (empty($recentBattles)): ?>
                <p class="text-gray-500 text-center py-4">No battles yet. Start fighting in Mundo!</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($recentBattles as $battle): ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full <?php echo $battle['won'] ? 'bg-green-500' : 'bg-red-500'; ?> flex items-center justify-center text-white">
                                    <i class="fas <?php echo $battle['won'] ? 'fa-trophy' : 'fa-skull'; ?>"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($battle['enemy_name']); ?> (<?php echo htmlspecialchars($battle['era']); ?>)</p>
                                    <p class="text-sm text-gray-500"><?php
                                    try {
                                        $date = new DateTime($battle['created_at'], new DateTimeZone('UTC'));
                                        $date->setTimezone(new DateTimeZone('Asia/Manila'));
                                        echo $date->format('F d, Y h:i A');
                                    } catch (Exception $e) {
                                        echo 'Invalid date';
                                    }
                                    ?></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold <?php echo $battle['won'] ? 'text-green-600' : 'text-red-600'; ?>">
                                    <?php echo $battle['won'] ? 'Victory' : 'Defeat'; ?>
                                </p>
                                <p class="text-sm text-gray-500">+<?php echo $battle['xp_earned']; ?> XP</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Quiz Stats -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <h3 class="text-xl font-bold text-[#0038A8] mb-4">Quiz Statistics</h3>
            <div class="grid grid-cols-3 gap-4">
                <div class="text-center p-4 bg-gray-50 rounded-xl">
                    <p class="text-3xl font-bold text-gray-800"><?php echo $quizStats['total_quizzes']; ?></p>
                    <p class="text-sm text-gray-600">Quizzes Taken</p>
                </div>
                <div class="text-center p-4 bg-yellow-50 rounded-xl">
                    <p class="text-3xl font-bold text-yellow-600"><?php echo $quizStats['best_score']; ?>/10</p>
                    <p class="text-sm text-gray-600">Best Score</p>
                </div>
                <div class="text-center p-4 bg-blue-50 rounded-xl">
                    <p class="text-3xl font-bold text-blue-600"><?php echo round($quizStats['avg_score'], 1); ?>/10</p>
                    <p class="text-sm text-gray-600">Average Score</p>
                </div>
            </div>
        </div>

        <!-- Achievements -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <h3 class="text-xl font-bold text-[#0038A8] mb-4">Achievements</h3>
            <?php if (empty($achievements)): ?>
                <p class="text-gray-500 text-center py-8">No achievements yet. Keep playing to earn badges!</p>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($achievements as $achievement): ?>
                        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 border-2 border-yellow-400 rounded-xl p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-full bg-yellow-400 flex items-center justify-center text-white">
                                    <i class="fas fa-star"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-800"><?php echo htmlspecialchars($achievement['achievement_name']); ?></p>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($achievement['achievement_description']); ?></p>
                                    <p class="text-xs text-gray-500 mt-1"><?php
                                    try {
                                        $date = new DateTime($achievement['earned_at'], new DateTimeZone('UTC'));
                                        $date->setTimezone(new DateTimeZone('Asia/Manila'));
                                        echo $date->format('F d, Y h:i A');
                                    } catch (Exception $e) {
                                        echo 'Invalid date';
                                    }
                                    ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4">
            <a href="index.php" class="flex-1 bg-[#0038A8] text-white px-6 py-4 rounded-xl font-bold text-center hover:bg-[#002870] transition">
                <i class="fas fa-home mr-2"></i>Back to Home
            </a>
            <a href="leaderboard.php" class="flex-1 bg-gray-200 text-gray-800 px-6 py-4 rounded-xl font-bold text-center hover:bg-gray-300 transition">
                <i class="fas fa-trophy mr-2"></i>View Leaderboard
            </a>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
