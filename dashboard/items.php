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

// Handle POST - Add/Edit item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_item') {
    $item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
    $name = trim($_POST['name']);
    $type = $_POST['type'];
    $description = trim($_POST['description']);
    $power = (int)$_POST['power'];
    $rarity = $_POST['rarity'];
    $drop_rate = (float)$_POST['drop_rate'];
    $level_requirement = (int)$_POST['level_requirement'];
    
    if ($name && $type && $description) {
        if ($item_id) {
            $stmt = $db->prepare("UPDATE items SET name = ?, type = ?, description = ?, power = ?, rarity = ?, drop_rate = ?, level_requirement = ? WHERE id = ?");
            $stmt->execute([$name, $type, $description, $power, $rarity, $drop_rate, $level_requirement, $item_id]);
        } else {
            $stmt = $db->prepare("INSERT INTO items (name, type, description, power, rarity, drop_rate, level_requirement) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $type, $description, $power, $rarity, $drop_rate, $level_requirement]);
        }
        header('Location: /dashboard/items.php?success=1');
        exit;
    }
}

// Handle POST - Bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action']) && isset($_POST['item_ids'])) {
    $action = $_POST['bulk_action'];
    $item_ids = $_POST['item_ids'];
    
    if (!empty($item_ids)) {
        $placeholders = str_repeat('?,', count($item_ids) - 1) . '?';
        
        if ($action === 'delete') {
            $stmt = $db->prepare("DELETE FROM items WHERE id IN ($placeholders)");
            $stmt->execute($item_ids);
            header('Location: /dashboard/items.php?bulk_deleted=1');
            exit;
        } elseif ($action === 'change_rarity' && isset($_POST['new_rarity'])) {
            $stmt = $db->prepare("UPDATE items SET rarity = ? WHERE id IN ($placeholders)");
            $params = array_merge([$_POST['new_rarity']], $item_ids);
            $stmt->execute($params);
            header('Location: /dashboard/items.php?bulk_updated=1');
            exit;
        } elseif ($action === 'change_type' && isset($_POST['new_type'])) {
            $stmt = $db->prepare("UPDATE items SET type = ? WHERE id IN ($placeholders)");
            $params = array_merge([$_POST['new_type']], $item_ids);
            $stmt->execute($params);
            header('Location: /dashboard/items.php?bulk_updated=1');
            exit;
        }
    }
}

// Handle GET - Delete item
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM items WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    header('Location: /dashboard/items.php?deleted=1');
    exit;
}

// Handle GET - Edit item (populate form)
$edit_item = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM items WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit_item = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_type = isset($_GET['type']) ? $_GET['type'] : '';
$filter_rarity = isset($_GET['rarity']) ? $_GET['rarity'] : '';

// Build query
$query = "SELECT id, name, type, description, power, rarity, drop_rate, level_requirement FROM items WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND name LIKE ?";
    $params[] = "%$search%";
}

if ($filter_type) {
    $query .= " AND type = ?";
    $params[] = $filter_type;
}

if ($filter_rarity) {
    $query .= " AND rarity = ?";
    $params[] = $filter_rarity;
}

// Get total count
$count_query = str_replace('SELECT id, name, type, description, power, rarity, drop_rate, level_requirement', 'SELECT COUNT(*)', $query);
$total_stmt = $db->prepare($count_query);
$total_stmt->execute($params);
$total = $total_stmt->fetchColumn();
$total_pages = ceil($total / $per_page);

// Get items
$query .= " ORDER BY id DESC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;

$stmt = $db->prepare($query);
$stmt->execute($params);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/includes/header.php';
?>

<div class="space-y-6">
    <!-- Add/Edit Item Form -->
    <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
        <h3 class="text-base lg:text-lg font-bold text-white mb-4">
            <i class="fas fa-<?php echo $edit_item ? 'edit' : 'plus-circle'; ?> mr-2"></i>
            <?php echo $edit_item ? 'Edit Item' : 'Add New Item'; ?>
        </h3>
        <form method="POST" action="">
            <input type="hidden" name="action" value="save_item">
            <input type="hidden" name="item_id" value="<?php echo $edit_item['id'] ?? 0; ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 lg:gap-4">
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Name</label>
                    <input type="text" name="name" required value="<?php echo htmlspecialchars($edit_item['name'] ?? ''); ?>" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-3 lg:px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm" placeholder="Item name">
                </div>
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Type</label>
                    <select name="type" required class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-3 lg:px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                        <option value="weapon" <?php echo ($edit_item['type'] ?? '') === 'weapon' ? 'selected' : ''; ?>>Weapon</option>
                        <option value="armor" <?php echo ($edit_item['type'] ?? '') === 'armor' ? 'selected' : ''; ?>>Armor</option>
                        <option value="scroll" <?php echo ($edit_item['type'] ?? '') === 'scroll' ? 'selected' : ''; ?>>Scroll</option>
                        <option value="potion" <?php echo ($edit_item['type'] ?? '') === 'potion' ? 'selected' : ''; ?>>Potion</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Power</label>
                    <input type="number" name="power" value="<?php echo $edit_item['power'] ?? 0; ?>" min="0" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-3 lg:px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                </div>
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Rarity</label>
                    <select name="rarity" required class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-3 lg:px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                        <option value="common" <?php echo ($edit_item['rarity'] ?? '') === 'common' ? 'selected' : ''; ?>>Common</option>
                        <option value="rare" <?php echo ($edit_item['rarity'] ?? '') === 'rare' ? 'selected' : ''; ?>>Rare</option>
                        <option value="legendary" <?php echo ($edit_item['rarity'] ?? '') === 'legendary' ? 'selected' : ''; ?>>Legendary</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Drop Rate (%)</label>
                    <input type="number" name="drop_rate" value="<?php echo $edit_item['drop_rate'] ?? 0; ?>" min="0" max="100" step="0.1" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-3 lg:px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                </div>
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Level Requirement</label>
                    <input type="number" name="level_requirement" value="<?php echo $edit_item['level_requirement'] ?? 1; ?>" min="1" max="10" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-3 lg:px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                </div>
                <div class="md:col-span-2 lg:col-span-3">
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Description</label>
                    <textarea name="description" required rows="2" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-3 lg:px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm" placeholder="Item description..."><?php echo htmlspecialchars($edit_item['description'] ?? ''); ?></textarea>
                </div>
            </div>
            <div class="flex gap-2 mt-4">
                <button type="submit" class="bg-[#0038A8] hover:bg-[#0047b3] text-white px-4 lg:px-6 py-2 rounded-lg transition text-sm">
                    <i class="fas fa-save mr-1 lg:mr-2"></i><span class="hidden sm:inline">Save Item</span>
                </button>
                <?php if ($edit_item): ?>
                    <a href="/dashboard/items.php" class="bg-gray-600 hover:bg-gray-500 text-white px-4 lg:px-6 py-2 rounded-lg transition text-sm">
                        Cancel
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Filters -->
    <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
        <form method="GET" class="flex flex-col lg:flex-row gap-3">
            <input
                type="text"
                name="search"
                value="<?php echo htmlspecialchars($search); ?>"
                placeholder="Search items..."
                class="flex-1 bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm"
            >
            <select name="type" class="bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                <option value="">All Types</option>
                <option value="weapon" <?php echo $filter_type === 'weapon' ? 'selected' : ''; ?>>Weapon</option>
                <option value="armor" <?php echo $filter_type === 'armor' ? 'selected' : ''; ?>>Armor</option>
                <option value="scroll" <?php echo $filter_type === 'scroll' ? 'selected' : ''; ?>>Scroll</option>
                <option value="potion" <?php echo $filter_type === 'potion' ? 'selected' : ''; ?>>Potion</option>
            </select>
            <select name="rarity" class="bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                <option value="">All Rarities</option>
                <option value="common" <?php echo $filter_rarity === 'common' ? 'selected' : ''; ?>>Common</option>
                <option value="rare" <?php echo $filter_rarity === 'rare' ? 'selected' : ''; ?>>Rare</option>
                <option value="legendary" <?php echo $filter_rarity === 'legendary' ? 'selected' : ''; ?>>Legendary</option>
            </select>
            <div class="flex gap-2">
                <button type="submit" class="bg-[#0038A8] hover:bg-[#0047b3] text-white px-4 lg:px-6 py-2 rounded-lg transition text-sm flex-shrink-0">
                    <i class="fas fa-filter mr-1 lg:mr-2"></i><span class="hidden sm:inline">Filter</span>
                </button>
                <?php if ($search || $filter_type || $filter_rarity): ?>
                    <a href="/dashboard/items.php" class="bg-gray-600 hover:bg-gray-500 text-white px-4 lg:px-6 py-2 rounded-lg transition text-sm flex-shrink-0">
                        <i class="fas fa-times mr-1 lg:mr-2"></i><span class="hidden sm:inline">Clear</span>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Items Table -->
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <form method="POST" id="bulkActionForm">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase w-12">
                                <input type="checkbox" id="selectAll" class="rounded bg-gray-600 border-gray-500 text-[#0038A8] focus:ring-[#0038A8]">
                            </th>
                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">ID</th>
                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Name</th>
                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Type</th>
                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Power</th>
                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Rarity</th>
                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Drop Rate</th>
                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Level Req</th>
                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <?php foreach ($items as $item): ?>
                            <tr class="hover:bg-gray-700/50">
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <input type="checkbox" name="item_ids[]" value="<?php echo $item['id']; ?>" class="item-checkbox rounded bg-gray-600 border-gray-500 text-[#0038A8] focus:ring-[#0038A8]">
                                </td>
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
                                <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo $item['drop_rate']; ?>%</td>
                                <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo $item['level_requirement']; ?></td>
                                <td class="px-4 lg:px-6 py-4 whitespace-nowrap">
                                    <a href="?edit=<?php echo $item['id']; ?>" class="text-[#0038A8] hover:text-[#0047b3] mr-2 transition">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?delete=<?php echo $item['id']; ?>" onclick="return confirm('Are you sure you want to delete this item?');" class="text-red-400 hover:text-red-300 transition">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Bulk Actions Bar -->
            <div class="bg-gray-700 px-4 lg:px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-gray-600">
                <div class="flex items-center gap-3 flex-wrap">
                    <select name="bulk_action" class="bg-gray-600 text-white border border-gray-500 rounded-lg px-3 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                        <option value="">Bulk Actions</option>
                        <option value="delete">Delete Selected</option>
                        <option value="change_rarity">Change Rarity</option>
                        <option value="change_type">Change Type</option>
                    </select>
                    <select name="new_rarity" class="bg-gray-600 text-white border border-gray-500 rounded-lg px-3 py-2 focus:outline-none focus:border-[#0038A8] text-sm hidden" id="bulkRaritySelect">
                        <option value="">Select Rarity</option>
                        <option value="common">Common</option>
                        <option value="rare">Rare</option>
                        <option value="legendary">Legendary</option>
                    </select>
                    <select name="new_type" class="bg-gray-600 text-white border border-gray-500 rounded-lg px-3 py-2 focus:outline-none focus:border-[#0038A8] text-sm hidden" id="bulkTypeSelect">
                        <option value="">Select Type</option>
                        <option value="weapon">Weapon</option>
                        <option value="armor">Armor</option>
                        <option value="scroll">Scroll</option>
                        <option value="potion">Potion</option>
                    </select>
                    <button type="submit" class="bg-[#0038A8] hover:bg-[#0047b3] text-white px-4 py-2 rounded-lg transition text-sm">
                        Apply
                    </button>
                </div>
            </div>
        </form>

        <!-- Pagination -->
        <div class="bg-gray-700 px-4 lg:px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-xs sm:text-sm text-gray-300 text-center sm:text-left">
                Showing <?php echo ($offset + 1); ?> to <?php echo min($offset + $per_page, $total); ?> of <?php echo $total; ?> items
            </p>
            <div class="flex gap-2">
                <?php
                $query_params = [];
                if ($search) $query_params[] = 'search=' . urlencode($search);
                if ($filter_type) $query_params[] = 'type=' . $filter_type;
                if ($filter_rarity) $query_params[] = 'rarity=' . $filter_rarity;
                $query_string = implode('&', $query_params);
                ?>
                <?php if ($page > 1): ?>
                    <a href="?<?php echo $query_string ? $query_string . '&' : ''; ?>page=<?php echo $page - 1; ?>" class="px-3 lg:px-4 py-2 bg-gray-600 hover:bg-gray-500 rounded-lg text-white transition text-sm">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>
                <span class="px-3 lg:px-4 py-2 bg-[#0038A8] rounded-lg text-white text-sm"><?php echo $page; ?></span>
                <?php if ($page < $total_pages): ?>
                    <a href="?<?php echo $query_string ? $query_string . '&' : ''; ?>page=<?php echo $page + 1; ?>" class="px-3 lg:px-4 py-2 bg-gray-600 hover:bg-gray-500 rounded-lg text-white transition text-sm">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Select all checkbox
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
});

// Bulk action dropdown change
document.querySelector('select[name="bulk_action"]').addEventListener('change', function() {
    const raritySelect = document.getElementById('bulkRaritySelect');
    const typeSelect = document.getElementById('bulkTypeSelect');

    raritySelect.classList.add('hidden');
    typeSelect.classList.add('hidden');

    if (this.value === 'change_rarity') {
        raritySelect.classList.remove('hidden');
    } else if (this.value === 'change_type') {
        typeSelect.classList.remove('hidden');
    }
});

// Form submission validation
document.getElementById('bulkActionForm').addEventListener('submit', function(e) {
    const action = this.querySelector('select[name="bulk_action"]').value;
    const selectedItems = this.querySelectorAll('.item-checkbox:checked');

    if (!action) {
        e.preventDefault();
        alert('Please select a bulk action');
        return;
    }

    if (selectedItems.length === 0) {
        e.preventDefault();
        alert('Please select at least one item');
        return;
    }

    if (action === 'delete') {
        if (!confirm(`Are you sure you want to delete ${selectedItems.length} item(s)?`)) {
            e.preventDefault();
        }
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
