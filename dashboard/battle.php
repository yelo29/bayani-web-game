<?php
error_reporting(0);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$page_title = 'Battle Content';
$db = getDB();

// Handle POST - Add/Edit Region
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_region') {
    $region_id = isset($_POST['region_id']) ? (int)$_POST['region_id'] : 0;
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $min_level = (int)$_POST['min_level'];
    $icon = trim($_POST['icon']);
    
    if ($name && $description) {
        if ($region_id) {
            $stmt = $db->prepare("UPDATE regions SET name = ?, description = ?, min_level = ?, icon = ? WHERE id = ?");
            $stmt->execute([$name, $description, $min_level, $icon, $region_id]);
        } else {
            $stmt = $db->prepare("INSERT INTO regions (name, description, min_level, icon) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $description, $min_level, $icon]);
        }
        header('Location: /dashboard/battle.php?success=region');
        exit;
    }
}

// Handle POST - Add/Edit Enemy
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_enemy') {
    $enemy_id = isset($_POST['enemy_id']) ? (int)$_POST['enemy_id'] : 0;
    $region_id = (int)$_POST['region_id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $hp = (int)$_POST['hp'];
    $attack = (int)$_POST['attack'];
    $defense = (int)$_POST['defense'];
    $speed = (int)$_POST['speed'];
    $xp_reward = (int)$_POST['xp_reward'];
    $image = trim($_POST['image']);
    
    if ($name && $region_id) {
        if ($enemy_id) {
            $stmt = $db->prepare("UPDATE enemies SET region_id = ?, name = ?, description = ?, hp = ?, attack = ?, defense = ?, speed = ?, xp_reward = ?, image = ? WHERE id = ?");
            $stmt->execute([$region_id, $name, $description, $hp, $attack, $defense, $speed, $xp_reward, $image, $enemy_id]);
        } else {
            $stmt = $db->prepare("INSERT INTO enemies (region_id, name, description, hp, attack, defense, speed, xp_reward, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$region_id, $name, $description, $hp, $attack, $defense, $speed, $xp_reward, $image]);
        }
        header('Location: /dashboard/battle.php?success=enemy');
        exit;
    }
}

// Handle GET - Delete Region
if (isset($_GET['delete_region']) && is_numeric($_GET['delete_region'])) {
    $stmt = $db->prepare("DELETE FROM regions WHERE id = ?");
    $stmt->execute([(int)$_GET['delete_region']]);
    header('Location: /dashboard/battle.php?deleted=region');
    exit;
}

// Handle GET - Delete Enemy
if (isset($_GET['delete_enemy']) && is_numeric($_GET['delete_enemy'])) {
    $stmt = $db->prepare("DELETE FROM enemies WHERE id = ?");
    $stmt->execute([(int)$_GET['delete_enemy']]);
    header('Location: /dashboard/battle.php?deleted=enemy');
    exit;
}

// Get all regions
$regions = $db->query("SELECT * FROM regions ORDER BY min_level ASC")->fetchAll(PDO::FETCH_ASSOC);

// Get all enemies with region names
$enemies = $db->query("
    SELECT e.*, r.name as region_name 
    FROM enemies e 
    JOIN regions r ON e.region_id = r.id 
    ORDER BY r.min_level ASC, e.id ASC
")->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/includes/header.php';
?>

<div class="space-y-6">
    <!-- Regions Section -->
    <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
        <h3 class="text-base lg:text-lg font-bold text-white mb-4">
            <i class="fas fa-map mr-2 text-[#0038A8]"></i>Battle Regions
        </h3>
        
        <!-- Add Region Form -->
        <form method="POST" class="mb-6 p-4 bg-gray-700/50 rounded-lg">
            <input type="hidden" name="action" value="save_region">
            <input type="hidden" name="region_id" value="0">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Region Name</label>
                    <input type="text" name="name" required class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-3 lg:px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm" placeholder="e.g., Maynila">
                </div>
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Min Level</label>
                    <input type="number" name="min_level" value="1" min="1" max="10" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-3 lg:px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                </div>
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Icon (FontAwesome class)</label>
                    <input type="text" name="icon" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-3 lg:px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm" placeholder="e.g., fa-city">
                </div>
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">&nbsp;</label>
                    <button type="submit" class="w-full bg-[#0038A8] hover:bg-[#0047b3] text-white px-4 py-2 rounded-lg transition text-sm">
                        <i class="fas fa-plus mr-2"></i>Add Region
                    </button>
                </div>
                <div class="md:col-span-2 lg:col-span-3">
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Description</label>
                    <input type="text" name="description" required class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-3 lg:px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm" placeholder="Brief description of the region">
                </div>
            </div>
        </form>

        <!-- Regions List -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Min Level</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Icon</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <?php foreach ($regions as $region): ?>
                        <tr class="hover:bg-gray-700/50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo $region['id']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white"><?php echo htmlspecialchars($region['name']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-300"><?php echo htmlspecialchars(substr($region['description'], 0, 50)) . (strlen($region['description']) > 50 ? '...' : ''); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo $region['min_level']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                <?php if ($region['icon']): ?>
                                    <i class="fas <?php echo htmlspecialchars($region['icon']); ?> mr-2"></i>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($region['icon']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="?delete_region=<?php echo $region['id']; ?>" onclick="return confirm('Are you sure you want to delete this region? All enemies in this region will also be deleted.');" class="text-red-400 hover:text-red-300 transition">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Enemies Section -->
    <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
        <h3 class="text-base lg:text-lg font-bold text-white mb-4">
            <i class="fas fa-dragon mr-2 text-[#0038A8]"></i>Enemies
        </h3>
        
        <!-- Add Enemy Form -->
        <form method="POST" class="mb-6 p-4 bg-gray-700/50 rounded-lg">
            <input type="hidden" name="action" value="save_enemy">
            <input type="hidden" name="enemy_id" value="0">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Region</label>
                    <select name="region_id" required class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-3 lg:px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                        <?php foreach ($regions as $region): ?>
                            <option value="<?php echo $region['id']; ?>"><?php echo htmlspecialchars($region['name']); ?> (Level <?php echo $region['min_level']; ?>+)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Enemy Name</label>
                    <input type="text" name="name" required class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-3 lg:px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm" placeholder="e.g., Spanish Soldier">
                </div>
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">HP</label>
                    <input type="number" name="hp" value="100" min="1" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-3 lg:px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                </div>
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Attack</label>
                    <input type="number" name="attack" value="10" min="1" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-3 lg:px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                </div>
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Defense</label>
                    <input type="number" name="defense" value="5" min="1" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-3 lg:px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                </div>
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Speed</label>
                    <input type="number" name="speed" value="10" min="1" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-3 lg:px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                </div>
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">XP Reward</label>
                    <input type="number" name="xp_reward" value="50" min="1" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-3 lg:px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                </div>
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">&nbsp;</label>
                    <button type="submit" class="w-full bg-[#0038A8] hover:bg-[#0047b3] text-white px-4 py-2 rounded-lg transition text-sm">
                        <i class="fas fa-plus mr-2"></i>Add Enemy
                    </button>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Description</label>
                    <input type="text" name="description" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-3 lg:px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm" placeholder="Brief description of the enemy">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Image URL</label>
                    <input type="text" name="image" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-3 lg:px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm" placeholder="Image URL (optional)">
                </div>
            </div>
        </form>

        <!-- Enemies List -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Region</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">HP</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Attack</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Defense</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Speed</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">XP</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <?php foreach ($enemies as $enemy): ?>
                        <tr class="hover:bg-gray-700/50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo $enemy['id']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo htmlspecialchars($enemy['region_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white"><?php echo htmlspecialchars($enemy['name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo $enemy['hp']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo $enemy['attack']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo $enemy['defense']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo $enemy['speed']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo $enemy['xp_reward']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="?delete_enemy=<?php echo $enemy['id']; ?>" onclick="return confirm('Are you sure you want to delete this enemy?');" class="text-red-400 hover:text-red-300 transition">
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
