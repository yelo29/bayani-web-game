<?php
session_start();
require_once 'includes/functions.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get all regions with enemy counts and player progress
$regions = getRegions();
$pdo = getDB();

// Get enemy counts per region
$regionEnemyCounts = [];
foreach ($regions as $region) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM enemies WHERE region_id = ?");
    $stmt->execute([$region['id']]);
    $regionEnemyCounts[$region['id']] = $stmt->fetchColumn();
}

// Get player progress per region
$playerProgress = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT region_id, battles_won, completed FROM region_progress WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    while ($row = $stmt->fetch()) {
        $playerProgress[$row['region_id']] = $row;
    }
}

// Get enemies for selected region
$selectedRegionId = isset($_GET['region_id']) ? (int)$_GET['region_id'] : null;
$enemies = [];
if ($selectedRegionId) {
    $stmt = $pdo->prepare("SELECT * FROM enemies WHERE region_id = ? ORDER BY id ASC");
    $stmt->execute([$selectedRegionId]);
    $enemies = $stmt->fetchAll();
}

require_once 'includes/header.php';
?>

<main class="min-h-screen bg-gray-50 py-8 px-4">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-4xl font-bold font-serif text-center text-[#0038A8] mb-2">Mundo ng mga Bayani</h1>
        <p class="text-center text-gray-600 mb-8">
            Igalang ang kasaysayan, labanan ang mga kaaway, maging tunay na bayani!
        </p>

        <!-- Player Stats Bar -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
            <div class="flex flex-wrap justify-between items-center gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-[#0038A8] rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-white text-xl"></i>
                    </div>
                    <div>
                        <p class="font-bold text-gray-800"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                        <p class="text-sm text-gray-600">Level <?php echo $_SESSION['level']; ?> • <?php echo $_SESSION['hero_class'] ?? 'Walang Klase'; ?></p>
                    </div>
                </div>
                <div class="flex gap-6">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-yellow-500"><?php echo $_SESSION['xp']; ?></p>
                        <p class="text-sm text-gray-600">XP</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-[#0038A8]"><?php echo $_SESSION['xp'] ?? 0; ?></p>
                        <p class="text-sm text-gray-600">HP</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Regions Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($regions as $region): ?>
                <?php
                $isLocked = $_SESSION['level'] < $region['min_level'];
                $progress = $playerProgress[$region['id']] ?? ['battles_won' => 0, 'completed' => 0];
                $enemyCount = $regionEnemyCounts[$region['id']] ?? 0;
                ?>
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden <?php echo $isLocked ? 'opacity-60' : 'hover:shadow-xl transition'; ?>">
                    <!-- Region Header -->
                    <div class="p-6" style="background: <?php echo $region['background_color'] ?? '#0038A8'; ?>;">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-2xl font-bold text-white mb-1"><?php echo htmlspecialchars($region['name']); ?></h3>
                                <p class="text-white/80 text-sm"><?php echo htmlspecialchars($region['province']); ?></p>
                            </div>
                            <?php if ($isLocked): ?>
                                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                                    <i class="fas fa-lock text-white"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex gap-2 mb-3">
                            <span class="px-3 py-1 bg-white/20 text-white rounded-full text-xs font-bold uppercase">
                                <?php echo htmlspecialchars($region['island_group']); ?>
                            </span>
                            <span class="px-3 py-1 bg-white/20 text-white rounded-full text-xs">
                                Min Level: <?php echo $region['min_level']; ?>
                            </span>
                        </div>
                        <?php if ($progress['completed']): ?>
                            <span class="inline-block px-3 py-1 bg-yellow-400 text-[#0038A8] rounded-full text-xs font-bold">
                                <i class="fas fa-trophy mr-1"></i> Natapos
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Region Body -->
                    <div class="p-6">
                        <p class="text-gray-600 text-sm mb-4"><?php echo htmlspecialchars($region['description']); ?></p>
                        
                        <div class="flex justify-between items-center mb-4">
                            <div class="text-sm text-gray-600">
                                <i class="fas fa-skull mr-1"></i> <?php echo $enemyCount; ?> Kaaway
                            </div>
                            <div class="text-sm text-gray-600">
                                <i class="fas fa-medal mr-1"></i> <?php echo $progress['battles_won']; ?> Panalo
                            </div>
                        </div>

                        <?php if ($isLocked): ?>
                            <button disabled class="w-full bg-gray-300 text-gray-500 py-3 rounded-xl font-bold cursor-not-allowed">
                                <i class="fas fa-lock mr-2"></i> Nakakandado (Level <?php echo $region['min_level']; ?>)
                            </button>
                        <?php else: ?>
                            <a href="mundo.php?region_id=<?php echo $region['id']; ?>" class="block w-full bg-[#0038A8] text-white py-3 rounded-xl font-bold text-center hover:bg-[#002870] transition">
                                <i class="fas fa-sword mr-2"></i> Pumunta sa Rehiyon
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Enemies List (when region is selected) -->
        <?php if ($selectedRegionId && !empty($enemies)): ?>
            <?php
            $selectedRegion = null;
            foreach ($regions as $r) {
                if ($r['id'] === $selectedRegionId) {
                    $selectedRegion = $r;
                    break;
                }
            }
            ?>
            <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-[#0038A8] mb-1"><?php echo htmlspecialchars($selectedRegion['name']); ?></h2>
                        <p class="text-gray-600">Piliin ang iyong kaaway</p>
                    </div>
                    <a href="mundo.php" class="text-gray-600 hover:text-[#0038A8] transition">
                        <i class="fas fa-times text-xl"></i>
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <?php foreach ($enemies as $enemy): ?>
                        <div class="border-2 border-gray-200 rounded-xl p-4 hover:border-[#0038A8] transition">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="font-bold text-gray-800"><?php echo htmlspecialchars($enemy['name']); ?></h3>
                                <span class="px-2 py-1 bg-gray-200 text-gray-700 rounded-full text-xs font-bold">
                                    <?php echo htmlspecialchars($enemy['era']); ?>
                                </span>
                            </div>
                            
                            <div class="flex gap-4 text-sm text-gray-600 mb-3">
                                <div><i class="fas fa-heart text-red-500 mr-1"></i> <?php echo $enemy['hp']; ?> HP</div>
                                <div><i class="fas fa-fist-raised text-orange-500 mr-1"></i> <?php echo $enemy['attack']; ?> ATK</div>
                                <div><i class="fas fa-shield-alt text-blue-500 mr-1"></i> <?php echo $enemy['defense']; ?> DEF</div>
                            </div>
                            
                            <p class="text-gray-600 text-sm mb-4"><?php echo htmlspecialchars($enemy['description']); ?></p>
                            
                            <a href="battle.php?region_id=<?php echo $selectedRegionId; ?>&enemy_id=<?php echo $enemy['id']; ?>" 
                               class="block w-full bg-[#CE1126] text-white py-2 rounded-xl font-bold text-center hover:bg-[#a00d1a] transition">
                                <i class="fas fa-sword mr-2"></i> Labanan
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Back to Home -->
        <div class="text-center mt-8">
            <a href="index.php" class="inline-block bg-gray-200 text-gray-800 px-6 py-3 rounded-full font-medium hover:bg-gray-300 transition">
                <i class="fas fa-home mr-2"></i> Bumalik sa Home
            </a>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
