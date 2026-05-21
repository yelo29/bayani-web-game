<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'includes/functions.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if region_id and enemy_id are provided
if (!isset($_GET['region_id']) || !isset($_GET['enemy_id'])) {
    header('Location: mundo.php');
    exit;
}

$regionId = (int)$_GET['region_id'];
$enemyId = (int)$_GET['enemy_id'];
$pdo = getDB();

// Get enemy data
$stmt = $pdo->prepare("SELECT * FROM enemies WHERE id = ?");
$stmt->execute([$enemyId]);
$enemy = $stmt->fetch();

if (!$enemy) {
    header('Location: mundo.php');
    exit;
}

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Initialize battle state if not set
if (!isset($_SESSION['battle_started']) || $_SESSION['battle_enemy_id'] !== $enemyId) {
    $_SESSION['battle_started'] = true;
    $_SESSION['battle_region_id'] = $regionId;
    $_SESSION['battle_enemy_id'] = $enemyId;
    $_SESSION['battle_enemy'] = $enemy; // Store enemy in session
    $_SESSION['battle_player_hp'] = $user['player_hp'] ?? 100;
    $_SESSION['battle_player_max_hp'] = $user['player_max_hp'] ?? 100;
    $_SESSION['battle_enemy_hp'] = $enemy['hp'];
    $_SESSION['battle_enemy_max_hp'] = $enemy['hp'];
    $_SESSION['battle_round'] = 1;
    $_SESSION['battle_max_rounds'] = 5;
    $_SESSION['battle_log'] = [];
    $_SESSION['battle_used_questions'] = [];
    $_SESSION['battle_start_time'] = time();
}

// Handle POST request for battle turn
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'attack') {
    $selected = $_POST['selected_option'];
    $correct = $_POST['correct_option'];
    $isCorrect = $selected === $correct;

    // 1. Get player data first
    $player_level = $_SESSION['level'] ?? 1;
    $userId = $_SESSION['user_id'];

    // 2. Get equipped items
    $weapon = getEquippedItem($userId, 'weapon');
    $armor = getEquippedItem($userId, 'armor');
    $weaponBonus = $weapon ? $weapon['power'] : 0;
    $armorBonus = $armor ? $armor['power'] : 0;

    // 3. Get enemy data from session
    $enemy = $_SESSION['battle_enemy'];
    $enemy_defense = $enemy['defense'] ?? 0;
    $enemy_attack = $enemy['attack'] ?? 0;

    // 4. Calculate time taken
    $timeTaken = time() - ($_SESSION['question_start_time'] ?? time());

    // 5. Now calculate damage
    if ($isCorrect) {
        $base_attack = 10 + ($player_level * 5);
        
        if ($timeTaken <= 5) { 
            $speedBonus = 20; 
            $speedLabel = 'Kidlat! ⚡ +20'; 
            $speedClass = 'text-yellow-500';
        } elseif ($timeTaken <= 10) { 
            $speedBonus = 10; 
            $speedLabel = 'Mabilis! 🔥 +10'; 
            $speedClass = 'text-orange-500';
        } elseif ($timeTaken <= 20) { 
            $speedBonus = 0; 
            $speedLabel = 'Normal hit'; 
            $speedClass = 'text-white';
        } else { 
            $speedBonus = -5; 
            $speedLabel = 'Mabagal... -5'; 
            $speedClass = 'text-gray-400';
        }
        
        $damage = max(5, ($base_attack + $speedBonus + $weaponBonus) - $enemy_defense);
        $_SESSION['battle_enemy_hp'] -= $damage;

        $_SESSION['battle_log'][] = [
            'type' => 'player',
            'message' => "Tamaan! {$speedLabel} - {$enemy['name']} -{$damage} HP",
            'speedClass' => $speedClass
        ];
    } else {
        $base_defense = 5 + ($player_level * 2);
        $total_defense = $base_defense + $armorBonus;
        $enemy_damage = max(5, $enemy_attack - $total_defense);
        $_SESSION['battle_player_hp'] -= $enemy_damage;

        $_SESSION['battle_log'][] = [
            'type' => 'enemy',
            'message' => "Nasagasaan! Player -{$enemy_damage} HP"
        ];
    }

    // Update user HP in database
    $stmt = $pdo->prepare("UPDATE users SET player_hp = ? WHERE id = ?");
    $stmt->execute([$_SESSION['battle_player_hp'], $_SESSION['user_id']]);

    // Increment round
    $_SESSION['battle_round']++;

    // Check if battle is over (5 rounds or HP depleted)
    if ($_SESSION['battle_round'] > $_SESSION['battle_max_rounds'] || 
        $_SESSION['battle_player_hp'] <= 0 || 
        $_SESSION['battle_enemy_hp'] <= 0) {
        
        // Determine winner
        if ($_SESSION['battle_player_hp'] > $_SESSION['battle_enemy_hp']) {
            header('Location: victory.php');
        } else {
            header('Location: defeat.php');
        }
        exit;
    }

    // Redirect to prevent form resubmission
    header('Location: battle.php?region_id=' . $regionId . '&enemy_id=' . $enemyId);
    exit;
}

// Get a random question (60% enemy's category, 40% random other category)
$usedQuestions = $_SESSION['battle_used_questions'] ?? [];
$question = null;

// Decide category: 60% enemy's category, 40% random
$useEnemyCategory = (rand(1, 100) <= 60);
$categoryId = $useEnemyCategory ? $enemy['category_id'] : null;

if ($categoryId === null) {
    // Get random category (not enemy's)
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE id != ? ORDER BY RAND() LIMIT 1");
    $stmt->execute([$enemy['category_id']]);
    $randomCat = $stmt->fetch();
    if ($randomCat) {
        $categoryId = $randomCat['id'];
    } else {
        $categoryId = $enemy['category_id']; // Fallback
    }
}

if (empty($usedQuestions)) {
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE category_id = ? ORDER BY RAND() LIMIT 1");
    $stmt->execute([$categoryId]);
    $question = $stmt->fetch();
} else {
    $placeholders = implode(',', array_fill(0, count($usedQuestions), '?'));
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE category_id = ? AND id NOT IN ($placeholders) ORDER BY RAND() LIMIT 1");
    $stmt->execute(array_merge([$categoryId], $usedQuestions));
    $question = $stmt->fetch();

    // If all questions used, reset and start over
    if (!$question) {
        $_SESSION['battle_used_questions'] = [];
        $stmt = $pdo->prepare("SELECT * FROM questions WHERE category_id = ? ORDER BY RAND() LIMIT 1");
        $stmt->execute([$categoryId]);
        $question = $stmt->fetch();
    }
}

if ($question) {
    $_SESSION['battle_used_questions'][] = $question['id'];
    // Set question start time for speed tracking
    $_SESSION['question_start_time'] = time();
}

require_once 'includes/header.php';
?>

<main class="min-h-screen bg-gray-50 py-8 px-4">
    <div class="max-w-4xl mx-auto">
        <!-- Round Indicator -->
        <div class="bg-white rounded-2xl shadow-lg p-4 mb-6 text-center">
            <p class="text-2xl font-bold text-[#0038A8]">
                Round <?php echo $_SESSION['battle_round']; ?>/<?php echo $_SESSION['battle_max_rounds']; ?>
            </p>
        </div>

        <!-- Enemy Side -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h2 class="text-2xl font-bold text-[#CE1126] mb-1"><?php echo htmlspecialchars($enemy['name']); ?></h2>
                    <span class="inline-block px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-xs font-bold">
                        <?php echo htmlspecialchars($enemy['era']); ?>
                    </span>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600">HP</p>
                    <p class="text-xl font-bold text-[#CE1126]"><?php echo max(0, $_SESSION['battle_enemy_hp']); ?>/<?php echo $_SESSION['battle_enemy_max_hp']; ?></p>
                </div>
            </div>
            
            <!-- Enemy HP Bar -->
            <div class="w-full bg-gray-200 rounded-full h-4 mb-4">
                <div class="bg-[#CE1126] h-4 rounded-full transition-all duration-500" style="width: <?php echo (max(0, $_SESSION['battle_enemy_hp']) / $_SESSION['battle_enemy_max_hp']) * 100; ?>%"></div>
            </div>
            
            <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($enemy['description']); ?></p>
        </div>

        <!-- VS Divider with Battle Log -->
        <div class="bg-[#1a1a2e] rounded-2xl shadow-lg p-6 mb-6">
            <div class="text-center mb-4">
                <span class="text-4xl font-bold text-yellow-400">VS</span>
            </div>
            
            <!-- Battle Log (last 3 actions) -->
            <div class="bg-black/30 rounded-xl p-4 max-h-32 overflow-y-auto">
                <?php if (empty($_SESSION['battle_log'])): ?>
                    <p class="text-yellow-400 text-center text-sm">Sugod na!</p>
                <?php else: ?>
                    <?php foreach (array_slice(array_reverse($_SESSION['battle_log']), 0, 3) as $log): ?>
                        <p class="text-sm <?php echo $log['type'] === 'player' ? 'text-green-400' : 'text-red-400'; ?> mb-1">
                            <?php if ($log['type'] === 'player' && isset($log['speedClass'])): ?>
                                <span class="<?php echo $log['speedClass']; ?>"><?php echo htmlspecialchars($log['message']); ?></span>
                            <?php else: ?>
                                <?php echo htmlspecialchars($log['message']); ?>
                            <?php endif; ?>
                        </p>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Player Side -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h2 class="text-2xl font-bold text-[#0038A8] mb-1"><?php echo htmlspecialchars($_SESSION['username']); ?></h2>
                    <span class="inline-block px-3 py-1 bg-yellow-400 text-[#0038A8] rounded-full text-xs font-bold uppercase">
                        <?php echo htmlspecialchars($_SESSION['hero_class'] ?? 'Walang Klase'); ?>
                    </span>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600">HP</p>
                    <p class="text-xl font-bold text-[#0038A8]"><?php echo max(0, $_SESSION['battle_player_hp']); ?>/<?php echo $_SESSION['battle_player_max_hp']; ?></p>
                </div>
            </div>
            
            <!-- Player HP Bar -->
            <div class="w-full bg-gray-200 rounded-full h-4 mb-4">
                <div class="bg-[#0038A8] h-4 rounded-full transition-all duration-500" style="width: <?php echo (max(0, $_SESSION['battle_player_hp']) / $_SESSION['battle_player_max_hp']) * 100; ?>%"></div>
            </div>
            
            <div class="flex gap-4 text-sm text-gray-600">
                <div>
                    <i class="fas fa-star text-yellow-500 mr-1"></i> Level <?php echo $_SESSION['level']; ?>
                </div>
                <div>
                    <i class="fas fa-bolt text-yellow-500 mr-1"></i> <?php echo $_SESSION['xp']; ?> XP
                </div>
            </div>
        </div>

        <!-- Question Card with Speed Timer -->
        <?php if ($question): ?>
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-6">
            <div class="flex justify-between items-center mb-6">
                <span class="text-sm text-gray-600">Category: <?php echo htmlspecialchars($question['category_id'] == $enemy['category_id'] ? 'Thematic' : 'Random'); ?></span>
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-600">Speed:</span>
                    <div id="speedIndicator" class="w-24 h-4 bg-green-500 rounded-full transition-all duration-1000"></div>
                    <span id="speedText" class="text-sm font-bold text-green-600">0s</span>
                </div>
            </div>
            
            <h3 class="text-2xl font-bold text-gray-800 text-center mb-8">
                <?php echo htmlspecialchars($question['question']); ?>
            </h3>

            <!-- Answer Buttons -->
            <form method="POST" action="battle.php?region_id=<?php echo $regionId; ?>&enemy_id=<?php echo $enemyId; ?>" id="battleForm">
                <input type="hidden" name="action" value="attack">
                <input type="hidden" name="correct_option" value="<?php echo strtolower($question['correct_option']); ?>">
                <div class="grid grid-cols-1 gap-4">
                    <button type="submit" name="selected_option" value="a" class="answer-btn bg-gray-100 hover:bg-[#0038A8] hover:text-white p-4 rounded-xl text-left font-medium transition transform hover:scale-102 border-2 border-transparent hover:border-[#0038A8]">
                        <span class="inline-block w-8 h-8 bg-[#0038A8] text-white rounded-full text-center leading-8 font-bold mr-3">A</span>
                        <?php echo htmlspecialchars($question['option_a']); ?>
                    </button>
                    <button type="submit" name="selected_option" value="b" class="answer-btn bg-gray-100 hover:bg-[#0038A8] hover:text-white p-4 rounded-xl text-left font-medium transition transform hover:scale-102 border-2 border-transparent hover:border-[#0038A8]">
                        <span class="inline-block w-8 h-8 bg-[#0038A8] text-white rounded-full text-center leading-8 font-bold mr-3">B</span>
                        <?php echo htmlspecialchars($question['option_b']); ?>
                    </button>
                    <button type="submit" name="selected_option" value="c" class="answer-btn bg-gray-100 hover:bg-[#0038A8] hover:text-white p-4 rounded-xl text-left font-medium transition transform hover:scale-102 border-2 border-transparent hover:border-[#0038A8]">
                        <span class="inline-block w-8 h-8 bg-[#0038A8] text-white rounded-full text-center leading-8 font-bold mr-3">C</span>
                        <?php echo htmlspecialchars($question['option_c']); ?>
                    </button>
                    <button type="submit" name="selected_option" value="d" class="answer-btn bg-gray-100 hover:bg-[#0038A8] hover:text-white p-4 rounded-xl text-left font-medium transition transform hover:scale-102 border-2 border-transparent hover:border-[#0038A8]">
                        <span class="inline-block w-8 h-8 bg-[#0038A8] text-white rounded-full text-center leading-8 font-bold mr-3">D</span>
                        <?php echo htmlspecialchars($question['option_d']); ?>
                    </button>
                </div>
            </form>
        </div>
        <?php else: ?>
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-6 text-center">
            <p class="text-gray-600">No questions available.</p>
            <a href="mundo.php" class="inline-block mt-4 bg-[#0038A8] text-white px-6 py-3 rounded-xl font-bold hover:bg-[#002870] transition">
                Back to World Map
            </a>
        </div>
        <?php endif; ?>

        <!-- Back to World Map -->
        <div class="text-center">
            <a href="mundo.php" class="inline-block bg-gray-200 text-gray-800 px-6 py-3 rounded-full font-medium hover:bg-gray-300 transition">
                <i class="fas fa-map mr-2"></i> Bumalik sa Mundo
            </a>
        </div>
    </div>
</main>

<script>
let startTime = Date.now();
let timerInterval;

function updateSpeedIndicator() {
    const elapsed = (Date.now() - startTime) / 1000;
    const indicator = document.getElementById('speedIndicator');
    const text = document.getElementById('speedText');

    text.textContent = elapsed.toFixed(1) + 's';

    if (elapsed < 5) {
        indicator.className = 'w-24 h-4 bg-green-500 rounded-full transition-all duration-1000';
        text.className = 'text-sm font-bold text-green-600';
    } else if (elapsed < 10) {
        indicator.className = 'w-24 h-4 bg-yellow-500 rounded-full transition-all duration-1000';
        text.className = 'text-sm font-bold text-yellow-600';
    } else if (elapsed < 20) {
        indicator.className = 'w-24 h-4 bg-orange-500 rounded-full transition-all duration-1000';
        text.className = 'text-sm font-bold text-orange-600';
    } else {
        indicator.className = 'w-24 h-4 bg-red-500 rounded-full transition-all duration-1000';
        text.className = 'text-sm font-bold text-red-600';
    }
}

timerInterval = setInterval(updateSpeedIndicator, 100);
</script>

<?php require_once 'includes/footer.php'; ?>
