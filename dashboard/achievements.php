<?php
error_reporting(0);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$page_title = 'Achievements';
$db = getDB();

// Handle POST - Add/Edit achievement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_achievement') {
    $achievement_id = isset($_POST['achievement_id']) ? (int)$_POST['achievement_id'] : 0;
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $condition_type = $_POST['condition_type'];
    $condition_value = (int)$_POST['condition_value'];
    $xp_reward = (int)$_POST['xp_reward'];
    $coin_reward = (int)$_POST['coin_reward'];
    
    if ($name && $description) {
        if ($achievement_id) {
            $stmt = $db->prepare("UPDATE achievement_definitions SET name = ?, description = ?, condition_type = ?, condition_value = ?, xp_reward = ?, coin_reward = ? WHERE id = ?");
            $stmt->execute([$name, $description, $condition_type, $condition_value, $xp_reward, $coin_reward, $achievement_id]);
        } else {
            $stmt = $db->prepare("INSERT INTO achievement_definitions (name, description, condition_type, condition_value, xp_reward, coin_reward) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $condition_type, $condition_value, $xp_reward, $coin_reward]);
        }
        header('Location: /dashboard/achievements.php?success=1');
        exit;
    }
}

// Handle GET - Delete achievement
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM achievement_definitions WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    header('Location: /dashboard/achievements.php?deleted=1');
    exit;
}

// Handle GET - Edit achievement
$edit_achievement = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM achievement_definitions WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit_achievement = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get all achievements
$achievements = $db->query("SELECT * FROM achievement_definitions ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Get achievement statistics
$stats = [];
$stmt = $db->query("
    SELECT achievement_name, COUNT(*) as earned_count 
    FROM achievements 
    GROUP BY achievement_name
");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $stats[$row['achievement_name']] = $row['earned_count'];
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="space-y-6">
    <!-- Add/Edit Achievement Form -->
    <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
        <h3 class="text-base lg:text-lg font-bold text-white mb-4">
            <i class="fas fa-<?php echo $edit_achievement ? 'edit' : 'plus-circle'; ?> mr-2"></i>
            <?php echo $edit_achievement ? 'Edit Achievement' : 'Add New Achievement'; ?>
        </h3>
        <form method="POST">
            <input type="hidden" name="action" value="save_achievement">
            <input type="hidden" name="achievement_id" value="<?php echo $edit_achievement['id'] ?? 0; ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Achievement Name</label>
                    <input type="text" name="name" required value="<?php echo htmlspecialchars($edit_achievement['name'] ?? ''); ?>" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm" placeholder="e.g., First Victory">
                </div>
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Condition Type</label>
                    <select name="condition_type" required class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                        <option value="level" <?php echo ($edit_achievement['condition_type'] ?? '') === 'level' ? 'selected' : ''; ?>>Reach Level</option>
                        <option value="battles_won" <?php echo ($edit_achievement['condition_type'] ?? '') === 'battles_won' ? 'selected' : ''; ?>>Battles Won</option>
                        <option value="quizzes_completed" <?php echo ($edit_achievement['condition_type'] ?? '') === 'quizzes_completed' ? 'selected' : ''; ?>>Quizzes Completed</option>
                        <option value="total_xp" <?php echo ($edit_achievement['condition_type'] ?? '') === 'total_xp' ? 'selected' : ''; ?>>Total XP Earned</option>
                        <option value="coins_earned" <?php echo ($edit_achievement['condition_type'] ?? '') === 'coins_earned' ? 'selected' : ''; ?>>Total Coins Earned</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Condition Value</label>
                    <input type="number" name="condition_value" value="<?php echo $edit_achievement['condition_value'] ?? 1; ?>" min="1" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                </div>
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">XP Reward</label>
                    <input type="number" name="xp_reward" value="<?php echo $edit_achievement['xp_reward'] ?? 100; ?>" min="0" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                </div>
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Coin Reward</label>
                    <input type="number" name="coin_reward" value="<?php echo $edit_achievement['coin_reward'] ?? 50; ?>" min="0" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                </div>
                <div class="md:col-span-2 lg:col-span-3">
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Description</label>
                    <textarea name="description" required rows="2" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm" placeholder="Achievement description..."><?php echo htmlspecialchars($edit_achievement['description'] ?? ''); ?></textarea>
                </div>
            </div>
            <div class="flex gap-2 mt-4">
                <button type="submit" class="bg-[#0038A8] hover:bg-[#0047b3] text-white px-4 lg:px-6 py-2 rounded-lg transition text-sm">
                    <i class="fas fa-save mr-2"></i>Save Achievement
                </button>
                <?php if ($edit_achievement): ?>
                    <a href="/dashboard/achievements.php" class="bg-gray-600 hover:bg-gray-500 text-white px-4 lg:px-6 py-2 rounded-lg transition text-sm">
                        Cancel
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Achievements Table -->
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Condition</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">XP Reward</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Coin Reward</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Times Earned</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <?php foreach ($achievements as $achievement): ?>
                        <tr class="hover:bg-gray-700/50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo $achievement['id']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white"><?php echo htmlspecialchars($achievement['name']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-300"><?php echo htmlspecialchars(substr($achievement['description'], 0, 50)) . (strlen($achievement['description']) > 50 ? '...' : ''); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                <?php echo ucfirst(str_replace('_', ' ', $achievement['condition_type'])); ?>: <?php echo $achievement['condition_value']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo $achievement['xp_reward']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo $achievement['coin_reward']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo $stats[$achievement['name']] ?? 0; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="?edit=<?php echo $achievement['id']; ?>" class="text-[#0038A8] hover:text-[#0047b3] mr-2 transition">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?delete=<?php echo $achievement['id']; ?>" onclick="return confirm('Are you sure you want to delete this achievement?');" class="text-red-400 hover:text-red-300 transition">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
