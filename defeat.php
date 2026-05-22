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

// Restore player HP to 50%
$playerMaxHp = $_SESSION['battle_player_max_hp'] ?? 100;
$restoredHp = ceil($playerMaxHp * 0.5);

// Update user HP in database and reset streak
$stmt = $pdo->prepare("UPDATE users SET player_hp = ?, current_streak = 0 WHERE id = ?");
$stmt->execute([$restoredHp, $_SESSION['user_id']]);

// Update session
$_SESSION['player_hp'] = $restoredHp;
$_SESSION['current_streak'] = 0;

// Log battle (lost)
$now = new DateTime('now', new DateTimeZone('UTC'));
$createdAt = $now->format('Y-m-d H:i:s');
$stmt = $pdo->prepare("
    INSERT INTO battle_log (user_id, enemy_id, won, xp_earned, created_at)
    VALUES (?, ?, 0, 0, ?)
");
$stmt->execute([$_SESSION['user_id'], $enemyId, $createdAt]);

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

<main class="min-h-screen bg-gradient-to-br from-red-800 to-red-900 py-8 px-4">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-2xl shadow-2xl p-8 text-center">
            <!-- Defeat Icon -->
            <div class="w-32 h-32 bg-[#CE1126] rounded-full mx-auto mb-6 flex items-center justify-center">
                <i class="fas fa-skull text-white text-6xl"></i>
            </div>
            
            <h1 class="text-4xl font-bold font-serif text-[#CE1126] mb-4">Natalo ka...</h1>
            <p class="text-2xl text-gray-800 mb-2">
                Hinuli ka ni <span class="font-bold text-[#CE1126]"><?php echo htmlspecialchars($enemy['name']); ?></span>
            </p>
            <p class="text-lg text-gray-600 mb-6">Huwag sumuko! Subukan ulit!</p>
            
            <!-- HP Restored -->
            <div class="bg-red-50 rounded-xl p-6 mb-6">
                <i class="fas fa-heart text-red-500 text-3xl mb-2"></i>
                <p class="text-red-700 font-bold mb-1">HP Restored</p>
                <p class="text-gray-800">
                    Naibalik ang HP mo sa <span class="font-bold"><?php echo $restoredHp; ?>/<?php echo $playerMaxHp; ?></span>
                </p>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="battle.php?region_id=<?php echo $regionId; ?>&enemy_id=<?php echo $enemyId; ?>" 
                   class="flex-1 bg-[#0038A8] text-white px-6 py-4 rounded-xl font-bold text-center hover:bg-[#002870] transition">
                    <i class="fas fa-redo mr-2"></i> Subukan Ulit
                </a>
                <a href="mundo.php" 
                   class="flex-1 bg-gray-200 text-gray-800 px-6 py-4 rounded-xl font-bold text-center hover:bg-gray-300 transition">
                    <i class="fas fa-map mr-2"></i> Mundo
                </a>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
