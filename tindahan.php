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

// Get user data including coins
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Get class-specific items for the user's hero class
$userHeroClass = $_SESSION['hero_class'] ?? null;
$stmt = $pdo->prepare("
    SELECT * FROM items
    WHERE (hero_class IS NULL OR hero_class = ?)
    AND type IN ('weapon', 'armor')
    ORDER BY power ASC
");
$stmt->execute([$userHeroClass]);
$classItems = $stmt->fetchAll();

$message = '';
$messageType = '';

// Handle purchase
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle buying class-specific items from database
    if (isset($_POST['buy_item_id']) && isset($_POST['buy_item_cost'])) {
        $itemId = (int)$_POST['buy_item_id'];
        $cost = (int)$_POST['buy_item_cost'];

        // Verify item exists and is available for user's class
        $stmt = $pdo->prepare("
            SELECT * FROM items
            WHERE id = ?
            AND (hero_class IS NULL OR hero_class = ?)
        ");
        $stmt->execute([$itemId, $userHeroClass]);
        $item = $stmt->fetch();

        if ($item && $user['coins'] >= $cost) {
            // Add to inventory
            $stmt = $pdo->prepare("
                INSERT INTO inventory (user_id, item_id, equipped)
                VALUES (?, ?, 0)
            ");
            $stmt->execute([$_SESSION['user_id'], $itemId]);

            // Deduct coins
            $pdo->prepare("UPDATE users SET coins = coins - ? WHERE id = ?")
                ->execute([$cost, $_SESSION['user_id']]);
            $_SESSION['coins'] -= $cost;
            $user['coins'] -= $cost;

            $message = "Nabili mo ang {$item['name']}!";
            $messageType = 'success';
        } else {
            $message = "Kulang ang iyong mga barya o hindi available ang item!";
            $messageType = 'error';
        }
    }

    // Handle buying hardcoded items
    if (isset($_POST['item'])) {
        $item = $_POST['item'];
        $cost = 0;
        $effect = '';

        switch ($item) {
            case 'small_potion':
                $cost = 30;
                $hpRestore = 25;
                if ($user['coins'] >= $cost) {
                    $newHp = min($user['player_max_hp'], $user['player_hp'] + $hpRestore);
                    $pdo->prepare("UPDATE users SET coins = coins - ?, player_hp = ? WHERE id = ?")
                        ->execute([$cost, $newHp, $_SESSION['user_id']]);
                    $_SESSION['coins'] -= $cost;
                    $_SESSION['player_hp'] = $newHp;
                    $message = "Nabili mo ang Small HP Potion! +25 HP";
                    $messageType = 'success';
                    $user['coins'] -= $cost;
                    $user['player_hp'] = $newHp;
                } else {
                    $message = "Kulang ang iyong mga barya!";
                    $messageType = 'error';
                }
                break;

            case 'large_potion':
                $cost = 60;
                $hpRestore = 50;
                if ($user['coins'] >= $cost) {
                    $newHp = min($user['player_max_hp'], $user['player_hp'] + $hpRestore);
                    $pdo->prepare("UPDATE users SET coins = coins - ?, player_hp = ? WHERE id = ?")
                        ->execute([$cost, $newHp, $_SESSION['user_id']]);
                    $_SESSION['coins'] -= $cost;
                    $_SESSION['player_hp'] = $newHp;
                    $message = "Nabili mo ang Large HP Potion! +50 HP";
                    $messageType = 'success';
                    $user['coins'] -= $cost;
                    $user['player_hp'] = $newHp;
                } else {
                    $message = "Kulang ang iyong mga barya!";
                    $messageType = 'error';
                }
                break;

            case 'full_restore':
                $cost = 150;
                if ($user['coins'] >= $cost) {
                    $pdo->prepare("UPDATE users SET coins = coins - ?, player_hp = player_max_hp WHERE id = ?")
                        ->execute([$cost, $_SESSION['user_id']]);
                    $_SESSION['coins'] -= $cost;
                    $_SESSION['player_hp'] = $user['player_max_hp'];
                    $message = "Nabili mo ang Full Restore! Full HP";
                    $messageType = 'success';
                    $user['coins'] -= $cost;
                    $user['player_hp'] = $user['player_max_hp'];
                } else {
                    $message = "Kulang ang iyong mga barya!";
                    $messageType = 'error';
                }
                break;

            case 'lucky_charm':
                $cost = 400;
                if ($user['coins'] >= $cost) {
                    // Add lucky charm to inventory
                    // First check if lucky charm item exists, if not create it
                    $stmt = $pdo->prepare("SELECT id FROM items WHERE name = 'Lucky Charm' LIMIT 1");
                    $stmt->execute();
                    $charmItem = $stmt->fetch();

                    if (!$charmItem) {
                        $stmt = $pdo->prepare("
                            INSERT INTO items (name, type, power, rarity, description, region_id)
                            VALUES ('Lucky Charm', 'scroll', 50, 'rare', 'Experience bonus for next battle', NULL)
                        ");
                        $stmt->execute();
                        $charmItemId = $pdo->lastInsertId();
                    } else {
                        $charmItemId = $charmItem['id'];
                    }

                    // Add to inventory
                    $stmt = $pdo->prepare("
                        INSERT INTO inventory (user_id, item_id, equipped)
                        VALUES (?, ?, 0)
                    ");
                    $stmt->execute([$_SESSION['user_id'], $charmItemId]);

                    $pdo->prepare("UPDATE users SET coins = coins - ? WHERE id = ?")
                        ->execute([$cost, $_SESSION['user_id']]);
                    $_SESSION['coins'] -= $cost;
                    $message = "Nabili mo ang Lucky Charm! XP sa susunod na laban";
                    $messageType = 'success';
                    $user['coins'] -= $cost;
                } else {
                    $message = "Kulang ang iyong mga barya!";
                    $messageType = 'error';
                }
                break;
        }
    }
}

require_once 'includes/header.php';
?>

<main class="min-h-screen bg-gray-50 py-8 px-4">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl font-bold font-serif text-center text-[#0038A8] mb-2">Tindahan</h1>
        <p class="text-center text-gray-600 mb-8">
            Bilhin ang mga item para sa iyong paglalakbay!
        </p>

        <!-- Coins Display -->
        <div class="bg-gradient-to-r from-yellow-400 to-yellow-600 rounded-2xl shadow-lg p-6 mb-8 text-center">
            <p class="text-5xl font-bold text-white mb-2">🪙 <?php echo $user['coins']; ?></p>
            <p class="text-white/90">Iyong mga Barya</p>
        </div>

        <!-- Message -->
        <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-xl text-center font-bold <?php echo $messageType === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <!-- Shop Items -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Small HP Potion -->
            <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-green-200 hover:border-green-500 transition">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-flask text-3xl text-green-600"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Small HP Potion</h3>
                        <p class="text-sm text-gray-600">Restore 25 HP</p>
                    </div>
                </div>
                <p class="text-gray-600 text-sm mb-4">Maliit na pang-restore ng HP para sa mabilisang pagpapagaling.</p>
                <div class="flex justify-between items-center">
                    <span class="text-2xl font-bold text-yellow-500">🪙 30</span>
                    <form method="POST" action="tindahan.php">
                        <input type="hidden" name="item" value="small_potion">
                        <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded-xl font-bold hover:bg-green-600 transition">
                            <i class="fas fa-shopping-cart mr-2"></i> Bilhin
                        </button>
                    </form>
                </div>
            </div>

            <!-- Large HP Potion -->
            <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-blue-200 hover:border-blue-500 transition">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-flask text-3xl text-blue-600"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Large HP Potion</h3>
                        <p class="text-sm text-gray-600">Restore 50 HP</p>
                    </div>
                </div>
                <p class="text-gray-600 text-sm mb-4">Malaking pang-restore ng HP para sa mas malalakas na kaaway.</p>
                <div class="flex justify-between items-center">
                    <span class="text-2xl font-bold text-yellow-500">🪙 60</span>
                    <form method="POST" action="tindahan.php">
                        <input type="hidden" name="item" value="large_potion">
                        <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-xl font-bold hover:bg-blue-600 transition">
                            <i class="fas fa-shopping-cart mr-2"></i> Bilhin
                        </button>
                    </form>
                </div>
            </div>

            <!-- Full Restore -->
            <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-purple-200 hover:border-purple-500 transition">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-heart text-3xl text-purple-600"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Full Restore</h3>
                        <p class="text-sm text-gray-600">Restore Full HP</p>
                    </div>
                </div>
                <p class="text-gray-600 text-sm mb-4">Kumpletong pagpapagaling! Ibalik ang iyong HP sa maximum.</p>
                <div class="flex justify-between items-center">
                    <span class="text-2xl font-bold text-yellow-500">🪙 150</span>
                    <form method="POST" action="tindahan.php">
                        <input type="hidden" name="item" value="full_restore">
                        <button type="submit" class="bg-purple-500 text-white px-6 py-2 rounded-xl font-bold hover:bg-purple-600 transition">
                            <i class="fas fa-shopping-cart mr-2"></i> Bilhin
                        </button>
                    </form>
                </div>
            </div>

            <!-- Lucky Charm -->
            <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-yellow-200 hover:border-yellow-500 transition">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-star text-3xl text-yellow-600"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Lucky Charm</h3>
                        <p class="text-sm text-gray-600">XP Bonus</p>
                    </div>
                </div>
                <p class="text-gray-600 text-sm mb-4">Susunod na laban: XP bonus! (Equip sa Inventaryo)</p>
                <div class="flex justify-between items-center">
                    <span class="text-2xl font-bold text-yellow-500">🪙 400</span>
                    <form method="POST" action="tindahan.php">
                        <input type="hidden" name="item" value="lucky_charm">
                        <button type="submit" class="bg-yellow-500 text-white px-6 py-2 rounded-xl font-bold hover:bg-yellow-600 transition">
                            <i class="fas fa-shopping-cart mr-2"></i> Bilhin
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Class-Specific Equipment -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-[#0038A8] mb-4">
                <i class="fas fa-shield-alt mr-2"></i>
                Kasanayang Equipment
                <?php if ($userHeroClass): ?>
                    <span class="text-sm font-normal text-gray-600 ml-2">
                        (Para sa <?php echo ucfirst($userHeroClass); ?>)
                    </span>
                <?php endif; ?>
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($classItems as $item): ?>
                    <?php
                    $cost = $item['power'] * 10; // Cost based on power
                    $icon = $item['type'] === 'weapon' ? 'fa-sword' : 'fa-shield-alt';
                    $color = $item['type'] === 'weapon' ? 'red' : 'blue';
                    $rarityColors = [
                        'common' => 'gray',
                        'rare' => 'blue',
                        'legendary' => 'yellow'
                    ];
                    $rarityColor = $rarityColors[$item['rarity']] ?? 'gray';
                    ?>
                    <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-<?php echo $rarityColor; ?>-200 hover:border-<?php echo $rarityColor; ?>-500 transition">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-16 h-16 bg-<?php echo $color; ?>-100 rounded-full flex items-center justify-center">
                                <i class="fas <?php echo $icon; ?> text-3xl text-<?php echo $color; ?>-600"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p class="text-sm text-gray-600">
                                    <?php echo ucfirst($item['type']); ?> •
                                    <?php if ($item['hero_class'] === 'mangkukulam' && $item['type'] === 'weapon'): ?>
                                        Magic Power: <span class="font-bold text-purple-600"><?php echo $item['power']; ?></span>
                                    <?php else: ?>
                                        Power: <?php echo $item['power']; ?>
                                    <?php endif; ?>
                                    <?php if ($item['hero_class']): ?>
                                        <span class="text-xs bg-<?php echo $rarityColor; ?>-100 text-<?php echo $rarityColor; ?>-800 px-2 py-1 rounded-full ml-2">
                                            <?php echo ucfirst($item['hero_class']); ?>
                                        </span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <p class="text-gray-600 text-sm mb-4"><?php echo htmlspecialchars($item['description']); ?></p>
                        <div class="flex justify-between items-center">
                            <span class="text-2xl font-bold text-yellow-500">🪙 <?php echo $cost; ?></span>
                            <form method="POST" action="tindahan.php">
                                <input type="hidden" name="buy_item_id" value="<?php echo $item['id']; ?>">
                                <input type="hidden" name="buy_item_cost" value="<?php echo $cost; ?>">
                                <button type="submit" class="bg-<?php echo $color; ?>-500 text-white px-6 py-2 rounded-xl font-bold hover:bg-<?php echo $color; ?>-600 transition">
                                    <i class="fas fa-shopping-cart mr-2"></i> Bilhin
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Current HP Display -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
            <h3 class="text-xl font-bold text-[#0038A8] mb-4">Kasalukuyang HP</h3>
            <div class="flex items-center justify-between mb-4">
                <p class="text-3xl font-bold text-red-500"><?php echo $user['player_hp']; ?>/<?php echo $user['player_max_hp']; ?></p>
                <p class="text-gray-600">HP</p>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-4">
                <div class="bg-gradient-to-r from-red-400 to-red-600 h-4 rounded-full transition-all duration-500" style="width: <?php echo ($user['player_hp'] / $user['player_max_hp']) * 100; ?>%"></div>
            </div>
        </div>

        <!-- Back to World Map -->
        <div class="text-center">
            <a href="mundo.php" class="inline-block bg-gray-200 text-gray-800 px-6 py-3 rounded-full font-medium hover:bg-gray-300 transition">
                <i class="fas fa-map mr-2"></i> Bumalik sa Mundo
            </a>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
