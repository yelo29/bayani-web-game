<?php
error_reporting(0);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$page_title = 'Items';
$db = getDB();

// Handle POST - Add new item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = trim($_POST['name']);
    $type = $_POST['type'];
    $description = trim($_POST['description']);
    $power = (int)$_POST['power'];
    $rarity = $_POST['rarity'];
    
    if ($name && $type && $description) {
        $stmt = $db->prepare("INSERT INTO items (name, type, description, power, rarity) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $type, $description, $power, $rarity]);
        header('Location: /dashboard/items.php?success=1');
        exit;
    }
}

// Handle GET - Delete item
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM items WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    header('Location: /dashboard/items.php?deleted=1');
    exit;
}

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get total count
$total = $db->query("SELECT COUNT(*) FROM items")->fetchColumn();
$total_pages = ceil($total / $per_page);

// Get items
$query = "SELECT id, name, type, description, power, rarity FROM items ORDER BY id DESC LIMIT ? OFFSET ?";
$stmt = $db->prepare($query);
$stmt->execute([$per_page, $offset]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/includes/header.php';
?>

<div class="space-y-6">
    <!-- Add Item Form -->
    <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
        <h3 class="text-base lg:text-lg font-bold text-white mb-4"><i class="fas fa-plus-circle mr-2"></i>Add New Item</h3>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 lg:gap-4">
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Name</label>
                    <input type="text" name="name" required class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-3 lg:px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm" placeholder="Item name">
                </div>
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Type</label>
                    <select name="type" required class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-3 lg:px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                        <option value="weapon">Weapon</option>
                        <option value="armor">Armor</option>
                        <option value="scroll">Scroll</option>
                        <option value="potion">Potion</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Description</label>
                    <textarea name="description" required rows="2" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-3 lg:px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm" placeholder="Item description..."></textarea>
                </div>
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Power</label>
                    <input type="number" name="power" value="0" min="0" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-3 lg:px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                </div>
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Rarity</label>
                    <select name="rarity" required class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-3 lg:px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                        <option value="common">Common</option>
                        <option value="rare">Rare</option>
                        <option value="legendary">Legendary</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="mt-4 bg-[#0038A8] hover:bg-[#0047b3] text-white px-4 lg:px-6 py-2 rounded-lg transition text-sm">
                <i class="fas fa-save mr-1 lg:mr-2"></i><span class="hidden sm:inline">Save Item</span>
            </button>
        </form>
    </div>

    <!-- Items Table -->
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">ID</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Name</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Type</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Description</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Power</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Rarity</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <?php foreach ($items as $item): ?>
                        <tr class="hover:bg-gray-700/50">
                            <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo $item['id']; ?></td>
                            <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm font-medium text-white truncate max-w-[100px] lg:max-w-none"><?php echo htmlspecialchars($item['name']); ?></td>
                            <td class="px-4 lg:px-6 py-4 whitespace-nowrap">
                                <?php
                                $type_colors = [
                                    'weapon' => 'bg-red-500',
                                    'armor' => 'bg-blue-500',
                                    'scroll' => 'bg-purple-500',
                                    'potion' => 'bg-green-500'
                                ];
                                $color = $type_colors[$item['type']] ?? 'bg-gray-500';
                                ?>
                                <span class="px-2 lg:px-3 py-1 rounded-full text-xs font-medium text-white <?php echo $color; ?>">
                                    <?php echo ucfirst($item['type']); ?>
                                </span>
                            </td>
                            <td class="px-4 lg:px-6 py-4 text-sm text-gray-300 truncate max-w-[150px] lg:max-w-none"><?php echo htmlspecialchars(substr($item['description'], 0, 30)) . (strlen($item['description']) > 30 ? '...' : ''); ?></td>
                            <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo $item['power']; ?></td>
                            <td class="px-4 lg:px-6 py-4 whitespace-nowrap">
                                <?php
                                $rarity_colors = [
                                    'common' => 'bg-gray-500',
                                    'rare' => 'bg-blue-500',
                                    'legendary' => 'bg-yellow-500'
                                ];
                                $color = $rarity_colors[$item['rarity']] ?? 'bg-gray-500';
                                ?>
                                <span class="px-2 lg:px-3 py-1 rounded-full text-xs font-medium text-white <?php echo $color; ?>">
                                    <?php echo ucfirst($item['rarity']); ?>
                                </span>
                            </td>
                            <td class="px-4 lg:px-6 py-4 whitespace-nowrap">
                                <a href="?delete=<?php echo $item['id']; ?>" onclick="return confirm('Are you sure you want to delete this item?');" class="text-red-400 hover:text-red-300 transition">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="bg-gray-700 px-4 lg:px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-xs sm:text-sm text-gray-300 text-center sm:text-left">
                Showing <?php echo ($offset + 1); ?> to <?php echo min($offset + $per_page, $total); ?> of <?php echo $total; ?> items
            </p>
            <div class="flex gap-2">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>" class="px-3 lg:px-4 py-2 bg-gray-600 hover:bg-gray-500 rounded-lg text-white transition text-sm">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>
                <span class="px-3 lg:px-4 py-2 bg-[#0038A8] rounded-lg text-white text-sm"><?php echo $page; ?></span>
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="px-3 lg:px-4 py-2 bg-gray-600 hover:bg-gray-500 rounded-lg text-white transition text-sm">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
