<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/functions.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Refresh session data from database
refreshSessionData();

$pdo = getDB();

// Get user's inventory with item details
$stmt = $pdo->prepare("
    SELECT inv.id as inventory_id, inv.equipped, i.*
    FROM inventory inv
    JOIN items i ON inv.item_id = i.id
    WHERE inv.user_id = ?
    ORDER BY inv.equipped DESC, i.power DESC
");
$stmt->execute([$_SESSION['user_id']]);
$inventory = $stmt->fetchAll();

// Handle equip/unequip/sell requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['inventory_id'])) {
    $inventoryId = (int)$_POST['inventory_id'];
    $action = $_POST['action'];

    if ($action === 'equip') {
        // Get item type
        $stmt = $pdo->prepare("
            SELECT i.type FROM inventory inv
            JOIN items i ON inv.item_id = i.id
            WHERE inv.id = ? AND inv.user_id = ?
        ");
        $stmt->execute([$inventoryId, $_SESSION['user_id']]);
        $item = $stmt->fetch();

        if ($item) {
            // Unequip all items of same type
            $pdo->prepare("
                UPDATE inventory inv
                JOIN items i ON inv.item_id = i.id
                SET inv.equipped = 0
                WHERE inv.user_id = ? AND i.type = ?
            ")->execute([$_SESSION['user_id'], $item['type']]);

            // Equip selected item
            $pdo->prepare("UPDATE inventory SET equipped = 1 WHERE id = ? AND user_id = ?")
                ->execute([$inventoryId, $_SESSION['user_id']]);
        }
    } elseif ($action === 'unequip') {
        $pdo->prepare("UPDATE inventory SET equipped = 0 WHERE id = ? AND user_id = ?")
            ->execute([$inventoryId, $_SESSION['user_id']]);
    } elseif ($action === 'sell') {
        // Get item details
        $stmt = $pdo->prepare("
            SELECT inv.equipped, i.power, i.rarity
            FROM inventory inv
            JOIN items i ON inv.item_id = i.id
            WHERE inv.id = ? AND inv.user_id = ?
        ");
        $stmt->execute([$inventoryId, $_SESSION['user_id']]);
        $item = $stmt->fetch();

        if ($item && !$item['equipped']) {
            // Shop price is power × 10
            // Sell price is a percentage of shop price (always lower than buy price)
            $sellPercentage = [
                'common' => 0.5,    // 50% of shop price
                'rare' => 0.6,      // 60% of shop price
                'legendary' => 0.7  // 70% of shop price
            ];
            $percentage = $sellPercentage[$item['rarity']] ?? 0.5;
            $shopPrice = $item['power'] * 10;
            $sellPrice = (int)($shopPrice * $percentage);

            // Remove item from inventory
            $pdo->prepare("DELETE FROM inventory WHERE id = ? AND user_id = ?")
                ->execute([$inventoryId, $_SESSION['user_id']]);

            // Add coins to user
            $pdo->prepare("UPDATE users SET coins = coins + ? WHERE id = ?")
                ->execute([$sellPrice, $_SESSION['user_id']]);

            // Refresh session
            refreshSessionData();
        } elseif ($item['equipped']) {
            // Cannot sell equipped items
            $_SESSION['sell_error'] = 'Hindi mo maaaring magbenta ng naka-equip na item!';
        }
    }

    header('Location: inventaryo.php');
    exit;
}

require_once 'includes/header.php';
?>

<main class="min-h-screen bg-gray-50 py-8 px-4">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl font-bold font-serif text-center text-[#0038A8] mb-2">Inventaryo</h1>
        <p class="text-center text-gray-600 mb-8">
            Iyong mga armas, armor, at scrolls
        </p>

        <!-- Sell error message -->
        <?php if (isset($_SESSION['sell_error'])): ?>
            <div class="bg-red-100 border-2 border-red-400 text-red-700 px-4 py-3 rounded-xl mb-6">
                <?php echo htmlspecialchars($_SESSION['sell_error']); ?>
            </div>
            <?php unset($_SESSION['sell_error']); ?>
        <?php endif; ?>

        <!-- Inventory Grid -->
        <?php if (empty($inventory)): ?>
            <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
                <i class="fas fa-box-open text-gray-400 text-6xl mb-4"></i>
                <p class="text-gray-600 text-lg">Wala pang gamit — labanan ang mga kaaway para makakuha ng armas!</p>
                <a href="mundo.php" class="inline-block mt-6 bg-[#0038A8] text-white px-6 py-3 rounded-xl font-bold hover:bg-[#002870] transition">
                    <i class="fas fa-sword mr-2"></i> Pumunta sa Mundo
                </a>
            </div>
        <?php else: ?>
            <!-- Weapons Section -->
            <?php $weapons = array_filter($inventory, fn($i) => $i['type'] === 'weapon'); ?>
            <?php if (!empty($weapons)): ?>
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-sword text-red-500"></i> Armas (Weapons)
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($weapons as $item): ?>
                        <?php
                        $rarityColors = [
                            'common' => 'text-gray-600',
                            'rare' => 'text-blue-600',
                            'legendary' => 'text-yellow-600'
                        ];
                        ?>
                        <div class="bg-white rounded-2xl shadow-lg p-6 border-2 <?php echo $item['equipped'] ? 'border-yellow-400 ring-4 ring-yellow-400' : 'border-gray-200'; ?>">
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center shadow">
                                        <i class="fas fa-sword text-xl text-red-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-800"><?php echo htmlspecialchars($item['name']); ?></h3>
                                        <span class="text-xs uppercase font-bold <?php echo $rarityColors[$item['rarity']] ?? 'text-gray-600'; ?>">
                                            <?php echo htmlspecialchars($item['rarity']); ?>
                                        </span>
                                    </div>
                                </div>
                                <?php if ($item['equipped']): ?>
                                    <span class="px-2 py-1 bg-yellow-400 text-[#0038A8] rounded-full text-xs font-bold">
                                        <i class="fas fa-check mr-1"></i> EQUIPPED
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <p class="text-gray-600 text-sm mb-4"><?php echo htmlspecialchars($item['description']); ?></p>
                            
                            <div class="flex justify-between items-center mb-4">
                                <div class="text-sm">
                                    <?php if ($item['type'] === 'scroll'): ?>
                                        <span class="text-gray-600">XP Bonus:</span>
                                        <span class="font-bold text-yellow-600">+<?php echo $item['power']; ?>%</span>
                                    <?php elseif ($_SESSION['hero_class'] === 'mangkukulam' && $item['type'] === 'weapon'): ?>
                                        <span class="text-gray-600">Magic Power:</span>
                                        <span class="font-bold text-purple-600">+<?php echo $item['power']; ?></span>
                                    <?php else: ?>
                                        <span class="text-gray-600">Power:</span>
                                        <span class="font-bold text-red-600">+<?php echo $item['power']; ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-sm">
                                    <span class="text-gray-600">Sell Price:</span>
                                    <?php
                                    $sellPercentage = ['common' => 0.5, 'rare' => 0.6, 'legendary' => 0.7];
                                    $percentage = $sellPercentage[$item['rarity']] ?? 0.5;
                                    $shopPrice = $item['power'] * 10;
                                    $sellPrice = (int)($shopPrice * $percentage);
                                    ?>
                                    <span class="font-bold text-green-600"><?php echo $sellPrice; ?> coins</span>
                                </div>
                            </div>

                            <?php if ($item['equipped']): ?>
                                <form method="POST" action="inventaryo.php">
                                    <input type="hidden" name="action" value="unequip">
                                    <input type="hidden" name="inventory_id" value="<?php echo $item['inventory_id']; ?>">
                                    <button type="submit" class="w-full bg-gray-200 text-gray-700 py-2 rounded-xl font-bold hover:bg-gray-300 transition">
                                        <i class="fas fa-times mr-2"></i> Unequip
                                    </button>
                                </form>
                            <?php else: ?>
                                <div class="grid grid-cols-2 gap-2">
                                    <form method="POST" action="inventaryo.php">
                                        <input type="hidden" name="action" value="equip">
                                        <input type="hidden" name="inventory_id" value="<?php echo $item['inventory_id']; ?>">
                                        <button type="submit" class="w-full bg-[#0038A8] text-white py-2 rounded-xl font-bold hover:bg-[#002870] transition">
                                            <i class="fas fa-check mr-1"></i> Equip
                                        </button>
                                    </form>
                                    <form method="POST" action="inventaryo.php" onsubmit="return confirm('Sigurado ka bang gusto mong ibenta itong item?');">
                                        <input type="hidden" name="action" value="sell">
                                        <input type="hidden" name="inventory_id" value="<?php echo $item['inventory_id']; ?>">
                                        <button type="submit" class="w-full bg-green-600 text-white py-2 rounded-xl font-bold hover:bg-green-700 transition">
                                            <i class="fas fa-coins mr-1"></i> Sell
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Armor Section -->
            <?php $armor = array_filter($inventory, fn($i) => $i['type'] === 'armor'); ?>
            <?php if (!empty($armor)): ?>
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-shield-alt text-blue-500"></i> Armor
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($armor as $item): ?>
                        <?php
                        $rarityColors = [
                            'common' => 'text-gray-600',
                            'rare' => 'text-blue-600',
                            'legendary' => 'text-yellow-600'
                        ];
                        ?>
                        <div class="bg-white rounded-2xl shadow-lg p-6 border-2 <?php echo $item['equipped'] ? 'border-yellow-400 ring-4 ring-yellow-400' : 'border-gray-200'; ?>">
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center shadow">
                                        <i class="fas fa-shield-alt text-xl text-blue-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-800"><?php echo htmlspecialchars($item['name']); ?></h3>
                                        <span class="text-xs uppercase font-bold <?php echo $rarityColors[$item['rarity']] ?? 'text-gray-600'; ?>">
                                            <?php echo htmlspecialchars($item['rarity']); ?>
                                        </span>
                                    </div>
                                </div>
                                <?php if ($item['equipped']): ?>
                                    <span class="px-2 py-1 bg-yellow-400 text-[#0038A8] rounded-full text-xs font-bold">
                                        <i class="fas fa-check mr-1"></i> EQUIPPED
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <p class="text-gray-600 text-sm mb-4"><?php echo htmlspecialchars($item['description']); ?></p>
                            
                            <div class="flex justify-between items-center mb-4">
                                <div class="text-sm">
                                    <span class="text-gray-600">Defense:</span>
                                    <span class="font-bold text-blue-600">+<?php echo $item['power']; ?></span>
                                </div>
                                <div class="text-sm">
                                    <span class="text-gray-600">Sell Price:</span>
                                    <?php
                                    $sellPercentage = ['common' => 0.5, 'rare' => 0.6, 'legendary' => 0.7];
                                    $percentage = $sellPercentage[$item['rarity']] ?? 0.5;
                                    $shopPrice = $item['power'] * 10;
                                    $sellPrice = (int)($shopPrice * $percentage);
                                    ?>
                                    <span class="font-bold text-green-600"><?php echo $sellPrice; ?> coins</span>
                                </div>
                            </div>

                            <?php if ($item['equipped']): ?>
                                <form method="POST" action="inventaryo.php">
                                    <input type="hidden" name="action" value="unequip">
                                    <input type="hidden" name="inventory_id" value="<?php echo $item['inventory_id']; ?>">
                                    <button type="submit" class="w-full bg-gray-200 text-gray-700 py-2 rounded-xl font-bold hover:bg-gray-300 transition">
                                        <i class="fas fa-times mr-2"></i> Unequip
                                    </button>
                                </form>
                            <?php else: ?>
                                <div class="grid grid-cols-2 gap-2">
                                    <form method="POST" action="inventaryo.php">
                                        <input type="hidden" name="action" value="equip">
                                        <input type="hidden" name="inventory_id" value="<?php echo $item['inventory_id']; ?>">
                                        <button type="submit" class="w-full bg-[#0038A8] text-white py-2 rounded-xl font-bold hover:bg-[#002870] transition">
                                            <i class="fas fa-check mr-1"></i> Equip
                                        </button>
                                    </form>
                                    <form method="POST" action="inventaryo.php" onsubmit="return confirm('Sigurado ka bang gusto mong ibenta itong item?');">
                                        <input type="hidden" name="action" value="sell">
                                        <input type="hidden" name="inventory_id" value="<?php echo $item['inventory_id']; ?>">
                                        <button type="submit" class="w-full bg-green-600 text-white py-2 rounded-xl font-bold hover:bg-green-700 transition">
                                            <i class="fas fa-coins mr-1"></i> Sell
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Scrolls Section -->
            <?php $scrolls = array_filter($inventory, fn($i) => $i['type'] === 'scroll'); ?>
            <?php if (!empty($scrolls)): ?>
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-scroll text-yellow-500"></i> Scrolls
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($scrolls as $item): ?>
                        <?php
                        $rarityColors = [
                            'common' => 'text-gray-600',
                            'rare' => 'text-blue-600',
                            'legendary' => 'text-yellow-600'
                        ];
                        ?>
                        <div class="bg-white rounded-2xl shadow-lg p-6 border-2 <?php echo $item['equipped'] ? 'border-yellow-400 ring-4 ring-yellow-400' : 'border-gray-200'; ?>">
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center shadow">
                                        <i class="fas fa-scroll text-xl text-yellow-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-800"><?php echo htmlspecialchars($item['name']); ?></h3>
                                        <span class="text-xs uppercase font-bold <?php echo $rarityColors[$item['rarity']] ?? 'text-gray-600'; ?>">
                                            <?php echo htmlspecialchars($item['rarity']); ?>
                                        </span>
                                    </div>
                                </div>
                                <?php if ($item['equipped']): ?>
                                    <span class="px-2 py-1 bg-yellow-400 text-[#0038A8] rounded-full text-xs font-bold">
                                        <i class="fas fa-check mr-1"></i> EQUIPPED
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <p class="text-gray-600 text-sm mb-4"><?php echo htmlspecialchars($item['description']); ?></p>
                            
                            <div class="flex justify-between items-center mb-4">
                                <div class="text-sm">
                                    <span class="text-gray-600">XP Bonus:</span>
                                    <span class="font-bold text-yellow-600">+<?php echo $item['power']; ?>%</span>
                                </div>
                                <div class="text-sm">
                                    <span class="text-gray-600">Sell Price:</span>
                                    <?php
                                    $sellPercentage = ['common' => 0.5, 'rare' => 0.6, 'legendary' => 0.7];
                                    $percentage = $sellPercentage[$item['rarity']] ?? 0.5;
                                    $shopPrice = $item['power'] * 10;
                                    $sellPrice = (int)($shopPrice * $percentage);
                                    ?>
                                    <span class="font-bold text-green-600"><?php echo $sellPrice; ?> coins</span>
                                </div>
                            </div>

                            <?php if ($item['equipped']): ?>
                                <form method="POST" action="inventaryo.php">
                                    <input type="hidden" name="action" value="unequip">
                                    <input type="hidden" name="inventory_id" value="<?php echo $item['inventory_id']; ?>">
                                    <button type="submit" class="w-full bg-gray-200 text-gray-700 py-2 rounded-xl font-bold hover:bg-gray-300 transition">
                                        <i class="fas fa-times mr-2"></i> Unequip
                                    </button>
                                </form>
                            <?php else: ?>
                                <div class="grid grid-cols-2 gap-2">
                                    <form method="POST" action="inventaryo.php">
                                        <input type="hidden" name="action" value="equip">
                                        <input type="hidden" name="inventory_id" value="<?php echo $item['inventory_id']; ?>">
                                        <button type="submit" class="w-full bg-[#0038A8] text-white py-2 rounded-xl font-bold hover:bg-[#002870] transition">
                                            <i class="fas fa-check mr-1"></i> Equip
                                        </button>
                                    </form>
                                    <form method="POST" action="inventaryo.php" onsubmit="return confirm('Sigurado ka bang gusto mong ibenta itong item?');">
                                        <input type="hidden" name="action" value="sell">
                                        <input type="hidden" name="inventory_id" value="<?php echo $item['inventory_id']; ?>">
                                        <button type="submit" class="w-full bg-green-600 text-white py-2 rounded-xl font-bold hover:bg-green-700 transition">
                                            <i class="fas fa-coins mr-1"></i> Sell
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Back to World Map -->
        <div class="text-center mt-8">
            <a href="mundo.php" class="inline-block bg-gray-200 text-gray-800 px-6 py-3 rounded-full font-medium hover:bg-gray-300 transition">
                <i class="fas fa-map mr-2"></i> Bumalik sa Mundo
            </a>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
