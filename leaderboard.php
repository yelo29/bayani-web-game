<?php
session_start();
require_once 'includes/header.php';
require_once 'includes/functions.php';

// Get filter parameters
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get categories for filter tabs
$categories = getCategories();

// Get leaderboard data
$leaderboard = getLeaderboard($categoryId, $limit, $offset);
$totalPlayers = getTotalPlayers($categoryId);

// Calculate total pages for pagination
$totalScores = count(getLeaderboard($categoryId, 1000, 0)); // Get all for pagination
$totalPages = ceil($totalScores / $limit);
?>

<main class="min-h-screen bg-gray-50 py-8 px-4">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl font-bold font-serif text-center text-[#0038A8] mb-2">Leaderboard</h1>
        <p class="text-center text-gray-600 mb-8">
            <?php echo $totalPlayers; ?> players have competed
        </p>

        <!-- Filter Tabs -->
        <div class="bg-white rounded-2xl shadow-lg p-4 mb-6">
            <div class="flex flex-wrap gap-2">
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
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Category</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Score</th>
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
                                            <?php echo htmlspecialchars($score['player_name']); ?>
                                        </p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 bg-[#0038A8] text-white rounded-full text-xs font-medium">
                                            <?php echo htmlspecialchars($score['category_name']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-2xl font-bold text-[#0038A8]">
                                            <?php echo $score['score']; ?>/<?php echo $score['total_questions']; ?>
                                        </p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-gray-600">
                                            <?php echo date('M d, Y', strtotime($score['created_at'])); ?>
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
                                <a href="?category=<?php echo $categoryId; ?>&page=<?php echo $page - 1; ?>" 
                                   class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);
                            
                            for ($i = $startPage; $i <= $endPage; $i++):
                            ?>
                                <a href="?category=<?php echo $categoryId; ?>&page=<?php echo $i; ?>" 
                                   class="px-4 py-2 rounded-lg font-medium transition <?php echo $i === $page ? 'bg-[#0038A8] text-white' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="?category=<?php echo $categoryId; ?>&page=<?php echo $page + 1; ?>" 
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

        <!-- Back to Home -->
        <div class="text-center mt-8">
            <a href="index.php" class="inline-block bg-[#0038A8] text-white px-6 py-3 rounded-full font-medium hover:bg-[#002870] transition">
                <i class="fas fa-home mr-2"></i> Back to Home
            </a>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
