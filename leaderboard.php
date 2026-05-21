<?php
session_start();
require_once 'includes/header.php';
require_once 'includes/functions.php';

// Get filter parameters
$categoryId = isset($_GET['category']) && $_GET['category'] !== '' ? (int)$_GET['category'] : null;
$sortBy = isset($_GET['sort']) && in_array($_GET['sort'], ['score', 'xp']) ? $_GET['sort'] : 'score';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get categories for filter tabs
$categories = getCategories();

// Get leaderboard data
$leaderboard = getLeaderboard($categoryId, $limit, $offset, $sortBy);
$totalPlayers = getTotalPlayers($categoryId);

// Calculate total pages for pagination
$totalScores = getTotalScores($categoryId, $sortBy);
$totalPages = $totalScores > 0 ? ceil($totalScores / $limit) : 1;

// Debug: Log pagination values (remove in production)
error_log("Pagination: page=$page, limit=$limit, offset=$offset, totalScores=$totalScores, totalPages=$totalPages, categoryId=" . ($categoryId ?? 'null') . ", sortBy=$sortBy, leaderboardCount=" . count($leaderboard));

// Ensure page is within valid range
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
    $leaderboard = getLeaderboard($categoryId, $limit, $offset, $sortBy);
}

// Get battle leaderboard data
$pdo = getDB();
$stmt = $pdo->query("
    SELECT 
        u.id,
        u.username,
        u.hero_class,
        u.level,
        u.current_streak,
        u.best_streak,
        COUNT(bl.id) as total_battles,
        SUM(bl.won) as total_wins
    FROM users u
    LEFT JOIN battle_log bl ON u.id = bl.user_id
    GROUP BY u.id
    HAVING total_battles > 0
    ORDER BY total_wins DESC, current_streak DESC
    LIMIT 10
");
$battleLeaderboard = $stmt->fetchAll();
?>

<main class="min-h-screen bg-gray-50 py-8 px-4">
    <div class="max-w-4xl mx-auto">
        <!-- Quiz Leaderboard Section -->
        <div class="mb-12">
            <h1 class="text-4xl font-bold font-serif text-center text-[#0038A8] mb-2">Quiz Leaderboard</h1>
            <p class="text-center text-gray-600 mb-8">
                Top scores from all quiz categories
            </p>

            <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-4 mb-6 text-center">
                <span class="text-blue-800 font-bold text-lg">📝 QUIZ ONLY</span>
            </div>

            <!-- Filter Tabs -->
        <div class="bg-white rounded-2xl shadow-lg p-4 mb-6">
            <div class="flex flex-wrap gap-2 mb-4">
                <a href="leaderboard.php"
                   class="px-4 py-2 rounded-full font-medium transition <?php echo $categoryId === null ? 'bg-[#0038A8] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                    All Categories
                </a>
                <?php foreach ($categories as $cat): ?>
                    <a href="leaderboard.php?category=<?php echo $cat['id']; ?>"
                       class="px-4 py-2 rounded-full font-medium transition <?php echo $categoryId === $cat['id'] ? 'bg-[#0038A8] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <div class="flex gap-2 border-t pt-4">
                <a href="leaderboard.php?category=<?php echo $categoryId ?? ''; ?>&sort=score"
                   class="px-4 py-2 rounded-full font-medium transition <?php echo $sortBy === 'score' ? 'bg-yellow-400 text-[#0038A8]' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                    <i class="fas fa-trophy mr-2"></i>By Score
                </a>
                <a href="leaderboard.php?category=<?php echo $categoryId ?? ''; ?>&sort=xp"
                   class="px-4 py-2 rounded-full font-medium transition <?php echo $sortBy === 'xp' ? 'bg-yellow-400 text-[#0038A8]' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                    <i class="fas fa-star mr-2"></i>By XP
                </a>
            </div>
        </div>

        <!-- Leaderboard Table -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <?php if (empty($leaderboard)): ?>
                <div class="text-center py-12 text-gray-500">
                    <i class="fas fa-trophy text-4xl mb-4"></i>
                    <p>No scores yet. Be the first to play!</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Rank</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Player</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Hero</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Level</th>
                                <?php if ($sortBy === 'xp'): ?>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">XP</th>
                                <?php else: ?>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Category</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Score</th>
                                <?php endif; ?>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($leaderboard as $index => $score): ?>
                                <?php
                                $rank = ($page - 1) * $limit + $index + 1;
                                $rankClasses = [
                                    1 => 'bg-yellow-100 text-yellow-800 border-yellow-400',
                                    2 => 'bg-gray-100 text-gray-800 border-gray-400',
                                    3 => 'bg-orange-100 text-orange-800 border-orange-400'
                                ];
                                $rankClass = $rankClasses[$rank] ?? 'bg-gray-50 text-gray-600 border-gray-200';
                                $rankIcon = $rank <= 3 ? 'fas fa-trophy' : '';
                                ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        <div class="w-10 h-10 rounded-full <?php echo $rankClass; ?> border-2 flex items-center justify-center font-bold">
                                            <?php if ($rankIcon): ?>
                                                <i class="<?php echo $rankIcon; ?>"></i>
                                            <?php else: ?>
                                                <?php echo $rank; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-semibold text-gray-800">
                                            <?php echo htmlspecialchars($score['display_name'] ?? $score['player_name'] ?? $score['username'] ?? 'Anonymous'); ?>
                                        </p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if (!empty($score['hero_class'])): ?>
                                            <?php
                                            $heroColors = ['mandirigma' => '#CE1126', 'lakambini' => '#0038A8', 'mangkukulam' => '#FCD116'];
                                            $heroColor = $heroColors[$score['hero_class']] ?? '#0038A8';
                                            ?>
                                            <span class="inline-block px-2 py-1 text-white rounded-full text-xs font-bold uppercase" style="background: <?php echo $heroColor; ?>;">
                                                <?php echo htmlspecialchars($score['hero_class']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-xs">Guest</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-block px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-bold whitespace-nowrap">
                                            Lvl <?php echo $score['level'] ?? 1; ?>
                                        </span>
                                    </td>
                                    <?php if ($sortBy === 'xp'): ?>
                                        <td class="px-6 py-4">
                                            <p class="text-lg font-bold text-yellow-500"><?php echo $score['xp'] ?? 0; ?></p>
                                        </td>
                                    <?php else: ?>
                                        <td class="px-6 py-4">
                                            <span class="inline-block max-w-[120px] sm:max-w-full px-3 py-1 bg-[#0038A8] text-white rounded-full text-xs font-medium whitespace-nowrap overflow-hidden text-ellipsis">
                                                <?php echo htmlspecialchars($score['category_name']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="text-2xl font-bold text-[#0038A8]">
                                                <?php echo $score['score']; ?>/<?php echo $score['total_questions']; ?>
                                            </p>
                                        </td>
                                    <?php endif; ?>
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-gray-600">
                                            <?php $date = new DateTime($score['created_at'], new DateTimeZone('UTC'));
$date->setTimezone(new DateTimeZone('Asia/Manila'));
echo $date->format('M d, Y g:i A'); ?>
                                        </p>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <p class="text-sm text-gray-600">
                            Showing <?php echo ($page - 1) * $limit + 1; ?> to <?php echo min($page * $limit, $totalScores); ?> of <?php echo $totalScores; ?> scores
                        </p>
                        <div class="flex gap-2">
                            <?php if ($page > 1): ?>
                                <a href="?<?php echo $categoryId !== null ? 'category=' . $categoryId . '&' : ''; ?>sort=<?php echo $sortBy; ?>&page=<?php echo $page - 1; ?>"
                                   class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>

                            <?php
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);

                            for ($i = $startPage; $i <= $endPage; $i++):
                            ?>
                                <a href="?<?php echo $categoryId !== null ? 'category=' . $categoryId . '&' : ''; ?>sort=<?php echo $sortBy; ?>&page=<?php echo $i; ?>"
                                   class="px-4 py-2 rounded-lg font-medium transition <?php echo $i === $page ? 'bg-[#0038A8] text-white' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <a href="?<?php echo $categoryId !== null ? 'category=' . $categoryId . '&' : ''; ?>sort=<?php echo $sortBy; ?>&page=<?php echo $page + 1; ?>"
                                   class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        </div>

        <!-- Battle Leaderboard Section -->
        <div id="battle" class="mb-8">
            <h2 class="text-3xl font-bold font-serif text-center text-[#CE1126] mb-2">Mundo ng mga Bayani — Battle Leaderboard</h2>
            <p class="text-center text-gray-600 mb-8">
                Top warriors in the battle arena
            </p>

            <div class="bg-red-50 border-2 border-red-200 rounded-xl p-4 mb-6 text-center">
                <span class="text-red-800 font-bold text-lg">⚔️ BATTLE ARENA</span>
            </div>

            <!-- Battle Leaderboard Table -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <?php if (empty($battleLeaderboard)): ?>
                    <div class="text-center py-12 text-gray-500">
                        <i class="fas fa-sword text-4xl mb-4"></i>
                        <p>No battles yet. Start fighting in Mundo!</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Rank</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Player</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Hero Class</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Level</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Battles Won</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Win Rate</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Streak</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($battleLeaderboard as $index => $player): ?>
                                    <?php
                                    $rank = $index + 1;
                                    $rankClasses = [
                                        1 => 'bg-yellow-100 text-yellow-800 border-yellow-400',
                                        2 => 'bg-gray-100 text-gray-800 border-gray-400',
                                        3 => 'bg-orange-100 text-orange-800 border-orange-400'
                                    ];
                                    $rankClass = $rankClasses[$rank] ?? 'bg-gray-50 text-gray-600 border-gray-200';
                                    $rankIcon = $rank <= 3 ? 'fas fa-trophy' : '';
                                    $winRate = $player['total_battles'] > 0 ? round(($player['total_wins'] / $player['total_battles']) * 100, 1) : 0;
                                    ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4">
                                            <div class="w-10 h-10 rounded-full <?php echo $rankClass; ?> border-2 flex items-center justify-center font-bold">
                                                <?php if ($rankIcon): ?>
                                                    <i class="<?php echo $rankIcon; ?>"></i>
                                                <?php else: ?>
                                                    <?php echo $rank; ?>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="font-semibold text-gray-800">
                                                <?php echo htmlspecialchars($player['username']); ?>
                                            </p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php if (!empty($player['hero_class'])): ?>
                                                <?php
                                                $heroColors = ['mandirigma' => '#CE1126', 'lakambini' => '#0038A8', 'mangkukulam' => '#FCD116'];
                                                $heroColor = $heroColors[$player['hero_class']] ?? '#0038A8';
                                                ?>
                                                <span class="inline-block px-2 py-1 text-white rounded-full text-xs font-bold uppercase" style="background: <?php echo $heroColor; ?>;">
                                                    <?php echo htmlspecialchars($player['hero_class']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-gray-400 text-xs">None</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-block px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-bold whitespace-nowrap">
                                                Lvl <?php echo $player['level'] ?? 1; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="text-lg font-bold text-green-600"><?php echo $player['total_wins']; ?></p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="text-lg font-bold text-blue-600"><?php echo $winRate; ?>%</p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="text-lg font-bold <?php echo $player['current_streak'] >= 3 ? 'text-yellow-600' : 'text-gray-600'; ?>">
                                                🔥 <?php echo $player['current_streak']; ?>
                                            </p>
                                            <p class="text-xs text-gray-500">Best: <?php echo $player['best_streak']; ?></p>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Back to Home -->
        <div class="text-center mt-8">
            <a href="index.php" class="inline-block bg-[#0038A8] text-white px-6 py-3 rounded-full font-medium hover:bg-[#002870] transition">
                <i class="fas fa-home mr-2"></i> Back to Home
            </a>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
