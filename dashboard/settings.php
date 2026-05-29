<?php
error_reporting(0);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$page_title = 'Game Settings';
$db = getDB();

// Handle GET - Delete level
if (isset($_GET['delete_level']) && is_numeric($_GET['delete_level'])) {
    $stmt = $db->prepare("DELETE FROM level_settings WHERE level = ?");
    $stmt->execute([(int)$_GET['delete_level']]);
    header('Location: /dashboard/settings.php?deleted=level');
    exit;
}

// Handle POST - Save settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'save_game_settings') {
        $xp_multiplier = (float)$_POST['xp_multiplier'];
        $coin_multiplier = (float)$_POST['coin_multiplier'];
        $quiz_timer = (int)$_POST['quiz_timer'];
        $battle_timer = (int)$_POST['battle_timer'];
        $max_level = (int)$_POST['max_level'];
        
        // Update or insert settings
        $stmt = $db->prepare("INSERT INTO game_settings (setting_key, setting_value) VALUES ('xp_multiplier', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$xp_multiplier, $xp_multiplier]);
        
        $stmt = $db->prepare("INSERT INTO game_settings (setting_key, setting_value) VALUES ('coin_multiplier', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$coin_multiplier, $coin_multiplier]);
        
        $stmt = $db->prepare("INSERT INTO game_settings (setting_key, setting_value) VALUES ('quiz_timer', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$quiz_timer, $quiz_timer]);
        
        $stmt = $db->prepare("INSERT INTO game_settings (setting_key, setting_value) VALUES ('battle_timer', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$battle_timer, $battle_timer]);
        
        $stmt = $db->prepare("INSERT INTO game_settings (setting_key, setting_value) VALUES ('max_level', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$max_level, $max_level]);
        
        header('Location: /dashboard/settings.php?success=1');
        exit;
    }
    
    if ($_POST['action'] === 'save_level_settings') {
        $level = (int)$_POST['level'];
        $xp_required = (int)$_POST['xp_required'];
        $stat_bonus = (int)$_POST['stat_bonus'];
        $coin_reward = (int)$_POST['coin_reward'];
        
        $stmt = $db->prepare("INSERT INTO level_settings (level, xp_required, stat_bonus, coin_reward) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE xp_required = ?, stat_bonus = ?, coin_reward = ?");
        $stmt->execute([$level, $xp_required, $stat_bonus, $coin_reward, $xp_required, $stat_bonus, $coin_reward]);
        
        header('Location: /dashboard/settings.php?success=level');
        exit;
    }
    
    if ($_POST['action'] === 'save_class_bonus') {
        $hero_class = $_POST['hero_class'];
        $attack_bonus = (int)$_POST['attack_bonus'];
        $defense_bonus = (int)$_POST['defense_bonus'];
        $speed_bonus = (int)$_POST['speed_bonus'];
        $magic_bonus = (int)$_POST['magic_bonus'];
        
        $stmt = $db->prepare("INSERT INTO class_bonuses (hero_class, attack_bonus, defense_bonus, speed_bonus, magic_bonus) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE attack_bonus = ?, defense_bonus = ?, speed_bonus = ?, magic_bonus = ?");
        $stmt->execute([$hero_class, $attack_bonus, $defense_bonus, $speed_bonus, $magic_bonus, $attack_bonus, $defense_bonus, $speed_bonus, $magic_bonus]);
        
        header('Location: /dashboard/settings.php?success=class');
        exit;
    }
}

// Get current settings
$settings = [];
$stmt = $db->query("SELECT setting_key, setting_value FROM game_settings");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Get level settings
$level_settings = $db->query("SELECT * FROM level_settings ORDER BY level ASC")->fetchAll(PDO::FETCH_ASSOC);

// Get class bonuses
$class_bonuses = [];
$stmt = $db->query("SELECT * FROM class_bonuses");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $class_bonuses[$row['hero_class']] = $row;
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="space-y-6">
    <?php if (isset($_GET['success'])): ?>
        <div class="bg-green-900/30 border border-green-600 rounded-lg p-4 mb-6">
            <p class="text-green-400 text-center">
                <i class="fas fa-check-circle mr-2"></i>Settings saved successfully
            </p>
        </div>
    <?php endif; ?>

    <!-- Game Settings -->
    <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
        <h3 class="text-base lg:text-lg font-bold text-white mb-4">
            <i class="fas fa-cogs mr-2 text-[#0038A8]"></i>Game Settings
        </h3>
        
        <form method="POST">
            <input type="hidden" name="action" value="save_game_settings">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">XP Multiplier</label>
                    <input type="number" name="xp_multiplier" value="<?php echo $settings['xp_multiplier'] ?? 1.0; ?>" step="0.1" min="0.1" max="10" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                    <p class="text-gray-500 text-xs mt-1">Global XP multiplier for all activities</p>
                </div>
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Coin Multiplier</label>
                    <input type="number" name="coin_multiplier" value="<?php echo $settings['coin_multiplier'] ?? 1.0; ?>" step="0.1" min="0.1" max="10" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                    <p class="text-gray-500 text-xs mt-1">Global coin multiplier for rewards</p>
                </div>
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Quiz Timer (seconds)</label>
                    <input type="number" name="quiz_timer" value="<?php echo $settings['quiz_timer'] ?? 30; ?>" min="10" max="300" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                    <p class="text-gray-500 text-xs mt-1">Time per question in quiz mode</p>
                </div>
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Battle Timer (seconds)</label>
                    <input type="number" name="battle_timer" value="<?php echo $settings['battle_timer'] ?? 15; ?>" min="5" max="60" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                    <p class="text-gray-500 text-xs mt-1">Time per turn in battle mode</p>
                </div>
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Max Level</label>
                    <input type="number" name="max_level" value="<?php echo $settings['max_level'] ?? 10; ?>" min="1" max="100" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                    <p class="text-gray-500 text-xs mt-1">Maximum player level</p>
                </div>
            </div>
            <button type="submit" class="mt-4 bg-[#0038A8] hover:bg-[#0047b3] text-white px-4 lg:px-6 py-2 rounded-lg transition text-sm">
                <i class="fas fa-save mr-2"></i>Save Settings
            </button>
        </form>
    </div>

    <!-- Level Settings -->
    <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
        <h3 class="text-base lg:text-lg font-bold text-white mb-4">
            <i class="fas fa-level-up-alt mr-2 text-[#0038A8]"></i>Level System
        </h3>
        
        <!-- Add/Edit Level Form -->
        <form method="POST" class="mb-6 p-4 bg-gray-700/50 rounded-lg">
            <input type="hidden" name="action" value="save_level_settings">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Level</label>
                    <input type="number" name="level" required min="1" max="100" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                </div>
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">XP Required</label>
                    <input type="number" name="xp_required" required min="0" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                </div>
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Stat Bonus</label>
                    <input type="number" name="stat_bonus" value="5" min="0" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                </div>
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Coin Reward</label>
                    <input type="number" name="coin_reward" value="100" min="0" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                </div>
            </div>
            <button type="submit" class="mt-4 bg-[#0038A8] hover:bg-[#0047b3] text-white px-4 py-2 rounded-lg transition text-sm">
                <i class="fas fa-plus mr-2"></i>Add/Update Level
            </button>
        </form>

        <!-- Level Settings Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Level</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">XP Required</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Stat Bonus</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Coin Reward</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <?php foreach ($level_settings as $level): ?>
                        <tr class="hover:bg-gray-700/50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-white"><?php echo $level['level']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo number_format($level['xp_required']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">+<?php echo $level['stat_bonus']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo number_format($level['coin_reward']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="?delete_level=<?php echo $level['level']; ?>" onclick="return confirm('Are you sure you want to delete this level setting?');" class="text-red-400 hover:text-red-300 transition">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Hero Class Bonuses -->
    <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
        <h3 class="text-base lg:text-lg font-bold text-white mb-4">
            <i class="fas fa-users mr-2 text-[#0038A8]"></i>Hero Class Bonuses
        </h3>
        
        <?php foreach (['mandirigma', 'lakambini', 'mangkukulam'] as $class): ?>
            <form method="POST" class="mb-4 p-4 bg-gray-700/50 rounded-lg">
                <input type="hidden" name="action" value="save_class_bonus">
                <input type="hidden" name="hero_class" value="<?php echo $class; ?>">
                <h4 class="text-white font-bold mb-3 capitalize"><?php echo $class; ?></h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Attack Bonus</label>
                        <input type="number" name="attack_bonus" value="<?php echo $class_bonuses[$class]['attack_bonus'] ?? 0; ?>" min="0" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                    </div>
                    <div>
                        <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Defense Bonus</label>
                        <input type="number" name="defense_bonus" value="<?php echo $class_bonuses[$class]['defense_bonus'] ?? 0; ?>" min="0" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                    </div>
                    <div>
                        <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Speed Bonus</label>
                        <input type="number" name="speed_bonus" value="<?php echo $class_bonuses[$class]['speed_bonus'] ?? 0; ?>" min="0" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                    </div>
                    <div>
                        <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Magic Bonus</label>
                        <input type="number" name="magic_bonus" value="<?php echo $class_bonuses[$class]['magic_bonus'] ?? 0; ?>" min="0" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                    </div>
                </div>
                <button type="submit" class="mt-4 bg-[#0038A8] hover:bg-[#0047b3] text-white px-4 py-2 rounded-lg transition text-sm">
                    <i class="fas fa-save mr-2"></i>Save Bonuses
                </button>
            </form>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
