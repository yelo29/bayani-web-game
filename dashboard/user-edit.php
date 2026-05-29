<?php
error_reporting(0);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$page_title = 'Edit User';
$db = getDB();

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$user_id) {
    header('Location: /dashboard/users.php');
    exit;
}

// Get user data
$stmt = $db->prepare("SELECT *, COALESCE(is_banned, 0) as is_banned, COALESCE(is_admin, 0) as is_admin FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: /dashboard/users.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_stats') {
        $level = (int)$_POST['level'];
        $xp = (int)$_POST['xp'];
        $coins = (int)$_POST['coins'];
        $player_hp = (int)$_POST['player_hp'];
        $player_max_hp = (int)$_POST['player_max_hp'];
        $base_attack = (int)$_POST['base_attack'];
        $base_defense = (int)$_POST['base_defense'];
        $base_speed = (int)$_POST['base_speed'];
        $base_magic = (int)$_POST['base_magic'];
        $hero_class = $_POST['hero_class'] ?? 'mandirigma';
        
        $stmt = $db->prepare("
            UPDATE users 
            SET level = ?, xp = ?, coins = ?, player_hp = ?, player_max_hp = ?,
                base_attack = ?, base_defense = ?, base_speed = ?, base_magic = ?,
                hero_class = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $stmt->execute([$level, $xp, $coins, $player_hp, $player_max_hp, 
                        $base_attack, $base_defense, $base_speed, $base_magic, 
                        $hero_class, $user_id]);
        
        $success = 'User stats updated successfully';
        $user = array_merge($user, [
            'level' => $level,
            'xp' => $xp,
            'coins' => $coins,
            'player_hp' => $player_hp,
            'player_max_hp' => $player_max_hp,
            'base_attack' => $base_attack,
            'base_defense' => $base_defense,
            'base_speed' => $base_speed,
            'base_magic' => $base_magic,
            'hero_class' => $hero_class
        ]);
    } elseif ($action === 'ban_user') {
        $ban_reason = $_POST['ban_reason'] ?? '';
        $stmt = $db->prepare("UPDATE users SET is_banned = 1, ban_reason = ? WHERE id = ?");
        $stmt->execute([$ban_reason, $user_id]);
        $success = 'User banned successfully';
        $user['is_banned'] = 1;
        $user['ban_reason'] = $ban_reason;
    } elseif ($action === 'unban_user') {
        $stmt = $db->prepare("UPDATE users SET is_banned = 0, ban_reason = NULL WHERE id = ?");
        $stmt->execute([$user_id]);
        $success = 'User unbanned successfully';
        $user['is_banned'] = 0;
        $user['ban_reason'] = null;
    } elseif ($action === 'reset_password') {
        $new_password = $_POST['new_password'] ?? '';
        if (strlen($new_password) < 6) {
            $error = 'Password must be at least 6 characters';
        } else {
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->execute([$password_hash, $user_id]);
            $success = 'Password reset successfully';
        }
    } elseif ($action === 'delete_user') {
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        header('Location: /dashboard/users.php?deleted=1');
        exit;
    }
}

// Get user inventory
$stmt = $db->prepare("
    SELECT i.id, i.name, i.type, i.power, i.description, i.rarity, inv.equipped
    FROM inventory inv
    JOIN items i ON inv.item_id = i.id
    WHERE inv.user_id = ?
    ORDER BY inv.equipped DESC, i.type, i.name
");
$stmt->execute([$user_id]);
$inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user achievements
$stmt = $db->prepare("
    SELECT * FROM achievements 
    WHERE user_id = ? 
    ORDER BY earned_at DESC
    LIMIT 10
");
$stmt->execute([$user_id]);
$achievements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent quiz scores
$stmt = $db->prepare("
    SELECT s.*, c.name as category_name
    FROM scores s
    LEFT JOIN categories c ON s.category_id = c.id
    WHERE s.user_id = ?
    ORDER BY s.created_at DESC
    LIMIT 5
");
$stmt->execute([$user_id]);
$recent_quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get battle stats
$stmt = $db->prepare("
    SELECT 
        COUNT(*) as total_battles,
        SUM(won) as battles_won,
        SUM(1 - won) as battles_lost,
        AVG(xp_earned) as avg_xp
    FROM battle_log
    WHERE user_id = ?
");
$stmt->execute([$user_id]);
$battleStats = $stmt->fetch();
$winRate = $battleStats['total_battles'] > 0 ? round(($battleStats['battles_won'] / $battleStats['total_battles']) * 100, 1) : 0;

require_once __DIR__ . '/includes/header.php';
?>

<div class="space-y-6">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="/dashboard/users.php" class="text-gray-400 hover:text-white transition">
            <i class="fas fa-arrow-left mr-2"></i>Back to Users
        </a>
    </div>

    <?php if (isset($success)): ?>
        <div class="bg-green-900/30 border border-green-600 rounded-lg p-4 mb-6">
            <p class="text-green-400 text-center">
                <i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($success); ?>
            </p>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="bg-red-900/30 border border-red-600 rounded-lg p-4 mb-6">
            <p class="text-red-400 text-center">
                <i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error); ?>
            </p>
        </div>
    <?php endif; ?>

    <!-- User Info Card -->
    <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-white mb-2"><?php echo htmlspecialchars($user['username']); ?></h2>
                <p class="text-gray-400 text-sm"><?php echo htmlspecialchars($user['email']); ?></p>
                <p class="text-gray-500 text-xs mt-1">Member since: <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
            </div>
            <div class="flex gap-2">
                <?php if ($user['is_banned']): ?>
                    <span class="px-3 py-1 bg-red-500/20 text-red-400 rounded-full text-xs font-medium">
                        <i class="fas fa-ban mr-1"></i>Banned
                    </span>
                <?php else: ?>
                    <span class="px-3 py-1 bg-green-500/20 text-green-400 rounded-full text-xs font-medium">
                        <i class="fas fa-check mr-1"></i>Active
                    </span>
                <?php endif; ?>
                <?php if ($user['is_admin']): ?>
                    <span class="px-3 py-1 bg-purple-500/20 text-purple-400 rounded-full text-xs font-medium">
                        <i class="fas fa-shield mr-1"></i>Admin
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-gray-700/50 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-[#0038A8]"><?php echo $user['level']; ?></p>
                <p class="text-gray-400 text-xs">Level</p>
            </div>
            <div class="bg-gray-700/50 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-yellow-500"><?php echo number_format($user['xp']); ?></p>
                <p class="text-gray-400 text-xs">XP</p>
            </div>
            <div class="bg-gray-700/50 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-green-500"><?php echo number_format($user['coins']); ?></p>
                <p class="text-gray-400 text-xs">Coins</p>
            </div>
            <div class="bg-gray-700/50 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-red-500"><?php echo $user['player_hp']; ?>/<?php echo $user['player_max_hp']; ?></p>
                <p class="text-gray-400 text-xs">HP</p>
            </div>
        </div>
    </div>

    <!-- Edit Stats Form -->
    <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
        <h3 class="text-lg font-bold text-white mb-4">
            <i class="fas fa-edit mr-2 text-[#0038A8]"></i>Edit User Stats
        </h3>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="action" value="update_stats">
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-300 text-sm font-bold mb-2">Hero Class</label>
                    <select name="hero_class" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8]">
                        <option value="mandirigma" <?php echo $user['hero_class'] === 'mandirigma' ? 'selected' : ''; ?>>Mandirigma</option>
                        <option value="lakambini" <?php echo $user['hero_class'] === 'lakambini' ? 'selected' : ''; ?>>Lakambini</option>
                        <option value="mangkukulam" <?php echo $user['hero_class'] === 'mangkukulam' ? 'selected' : ''; ?>>Mangkukulam</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-300 text-sm font-bold mb-2">Level</label>
                    <input type="number" name="level" value="<?php echo $user['level']; ?>" min="1" max="10" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8]">
                </div>
                <div>
                    <label class="block text-gray-300 text-sm font-bold mb-2">XP</label>
                    <input type="number" name="xp" value="<?php echo $user['xp']; ?>" min="0" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8]">
                </div>
                <div>
                    <label class="block text-gray-300 text-sm font-bold mb-2">Coins</label>
                    <input type="number" name="coins" value="<?php echo $user['coins']; ?>" min="0" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8]">
                </div>
                <div>
                    <label class="block text-gray-300 text-sm font-bold mb-2">Current HP</label>
                    <input type="number" name="player_hp" value="<?php echo $user['player_hp']; ?>" min="0" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8]">
                </div>
                <div>
                    <label class="block text-gray-300 text-sm font-bold mb-2">Max HP</label>
                    <input type="number" name="player_max_hp" value="<?php echo $user['player_max_hp']; ?>" min="1" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8]">
                </div>
                <div>
                    <label class="block text-gray-300 text-sm font-bold mb-2">Base Attack</label>
                    <input type="number" name="base_attack" value="<?php echo $user['base_attack']; ?>" min="0" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8]">
                </div>
                <div>
                    <label class="block text-gray-300 text-sm font-bold mb-2">Base Defense</label>
                    <input type="number" name="base_defense" value="<?php echo $user['base_defense']; ?>" min="0" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8]">
                </div>
                <div>
                    <label class="block text-gray-300 text-sm font-bold mb-2">Base Speed</label>
                    <input type="number" name="base_speed" value="<?php echo $user['base_speed']; ?>" min="0" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8]">
                </div>
                <div>
                    <label class="block text-gray-300 text-sm font-bold mb-2">Base Magic</label>
                    <input type="number" name="base_magic" value="<?php echo $user['base_magic']; ?>" min="0" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8]">
                </div>
            </div>

            <button type="submit" class="bg-[#0038A8] hover:bg-[#0047b3] text-white font-bold py-2 px-4 rounded-lg transition">
                <i class="fas fa-save mr-2"></i>Update Stats
            </button>
        </form>
    </div>

    <!-- Account Actions -->
    <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
        <h3 class="text-lg font-bold text-white mb-4">
            <i class="fas fa-cog mr-2 text-[#0038A8]"></i>Account Actions
        </h3>
        
        <div class="space-y-4">
            <!-- Ban/Unban -->
            <?php if ($user['is_banned']): ?>
                <form method="POST" class="flex items-center gap-4">
                    <input type="hidden" name="action" value="unban_user">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition">
                        <i class="fas fa-unlock mr-2"></i>Unban User
                    </button>
                    <?php if ($user['ban_reason']): ?>
                        <span class="text-gray-400 text-sm">Reason: <?php echo htmlspecialchars($user['ban_reason']); ?></span>
                    <?php endif; ?>
                </form>
            <?php else: ?>
                <form method="POST" class="flex items-center gap-4">
                    <input type="hidden" name="action" value="ban_user">
                    <input type="text" name="ban_reason" placeholder="Ban reason (optional)" class="bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8]">
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition">
                        <i class="fas fa-ban mr-2"></i>Ban User
                    </button>
                </form>
            <?php endif; ?>

            <!-- Reset Password -->
            <form method="POST" class="flex items-center gap-4">
                <input type="hidden" name="action" value="reset_password">
                <input type="password" name="new_password" placeholder="New password (min 6 chars)" class="bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8]">
                <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded-lg transition">
                    <i class="fas fa-key mr-2"></i>Reset Password
                </button>
            </form>

            <!-- Delete User -->
            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');" class="flex items-center gap-4">
                <input type="hidden" name="action" value="delete_user">
                <button type="submit" class="bg-red-800 hover:bg-red-900 text-white font-bold py-2 px-4 rounded-lg transition">
                    <i class="fas fa-trash mr-2"></i>Delete User Account
                </button>
            </form>
        </div>
    </div>

    <!-- Inventory -->
    <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
        <h3 class="text-lg font-bold text-white mb-4">
            <i class="fas fa-box mr-2 text-[#0038A8]"></i>Inventory (<?php echo count($inventory); ?> items)
        </h3>
        
        <?php if (empty($inventory)): ?>
            <p class="text-gray-400">No items in inventory</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase">Power</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase">Rarity</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <?php foreach ($inventory as $item): ?>
                            <tr>
                                <td class="px-4 py-3 text-sm text-white"><?php echo htmlspecialchars($item['name']); ?></td>
                                <td class="px-4 py-3 text-sm text-gray-300 capitalize"><?php echo htmlspecialchars($item['type']); ?></td>
                                <td class="px-4 py-3 text-sm text-gray-300"><?php echo $item['power']; ?></td>
                                <td class="px-4 py-3 text-sm text-gray-300 capitalize"><?php echo htmlspecialchars($item['rarity']); ?></td>
                                <td class="px-4 py-3">
                                    <?php if ($item['equipped']): ?>
                                        <span class="px-2 py-1 bg-green-500/20 text-green-400 rounded text-xs">Equipped</span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 bg-gray-500/20 text-gray-400 rounded text-xs">In Inventory</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Battle Stats -->
    <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
        <h3 class="text-lg font-bold text-white mb-4">
            <i class="fas fa-swords mr-2 text-[#0038A8]"></i>Battle Statistics
        </h3>
        
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-gray-700/50 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-white"><?php echo $battleStats['total_battles']; ?></p>
                <p class="text-gray-400 text-xs">Total Battles</p>
            </div>
            <div class="bg-gray-700/50 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-green-500"><?php echo $battleStats['battles_won']; ?></p>
                <p class="text-gray-400 text-xs">Wins</p>
            </div>
            <div class="bg-gray-700/50 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-red-500"><?php echo $battleStats['battles_lost']; ?></p>
                <p class="text-gray-400 text-xs">Losses</p>
            </div>
            <div class="bg-gray-700/50 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-yellow-500"><?php echo $winRate; ?>%</p>
                <p class="text-gray-400 text-xs">Win Rate</p>
            </div>
        </div>
    </div>

    <!-- Recent Quizzes -->
    <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
        <h3 class="text-lg font-bold text-white mb-4">
            <i class="fas fa-book mr-2 text-[#0038A8]"></i>Recent Quiz Scores
        </h3>
        
        <?php if (empty($recent_quizzes)): ?>
            <p class="text-gray-400">No quiz history</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase">Category</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase">Score</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase">Total</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase">Time</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <?php foreach ($recent_quizzes as $quiz): ?>
                            <tr>
                                <td class="px-4 py-3 text-sm text-white"><?php echo htmlspecialchars($quiz['category_name'] ?? 'N/A'); ?></td>
                                <td class="px-4 py-3 text-sm text-gray-300"><?php echo $quiz['score']; ?></td>
                                <td class="px-4 py-3 text-sm text-gray-300"><?php echo $quiz['total_questions']; ?></td>
                                <td class="px-4 py-3 text-sm text-gray-300"><?php echo gmdate('i:s', $quiz['time_taken']); ?></td>
                                <td class="px-4 py-3 text-sm text-gray-300"><?php echo date('M j, Y', strtotime($quiz['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Achievements -->
    <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
        <h3 class="text-lg font-bold text-white mb-4">
            <i class="fas fa-trophy mr-2 text-[#0038A8]"></i>Achievements (<?php echo count($achievements); ?>)
        </h3>
        
        <?php if (empty($achievements)): ?>
            <p class="text-gray-400">No achievements earned</p>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($achievements as $achievement): ?>
                    <div class="bg-gray-700/50 rounded-lg p-4">
                        <h4 class="text-white font-bold"><?php echo htmlspecialchars($achievement['achievement_name']); ?></h4>
                        <p class="text-gray-400 text-sm"><?php echo htmlspecialchars($achievement['achievement_description']); ?></p>
                        <p class="text-gray-500 text-xs mt-1">Earned: <?php echo date('F j, Y g:i A', strtotime($achievement['earned_at'])); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
