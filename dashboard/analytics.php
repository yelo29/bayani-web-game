<?php
error_reporting(0);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$page_title = 'Analytics';
$db = getDB();

// Date range filter
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-d', strtotime('-30 days'));
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');

// Hero class distribution
try {
    $hero_dist = $db->query("SELECT hero_class, COUNT(*) as count FROM users GROUP BY hero_class")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $hero_dist = [];
}

// Most played categories
try {
    $category_plays = $db->query("
        SELECT c.name, COUNT(s.id) as plays
        FROM scores s
        JOIN categories c ON s.category_id = c.id
        GROUP BY c.id
        ORDER BY plays DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $category_plays = [];
}

// Top 10 most active users by battles
try {
    $top_users = $db->query("
        SELECT u.username, u.hero_class, u.level, COUNT(b.id) as battles
        FROM battle_log b
        JOIN users u ON b.user_id = u.id
        GROUP BY u.id
        ORDER BY battles DESC
        LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $top_users = [];
}

// User engagement metrics
try {
    $engagement = [
        'total_users' => $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        'active_users_7d' => $db->query("SELECT COUNT(DISTINCT user_id) FROM scores WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn(),
        'active_users_30d' => $db->query("SELECT COUNT(DISTINCT user_id) FROM scores WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn(),
        'avg_session_length' => $db->query("SELECT AVG(time_taken) FROM scores WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn(),
    ];
} catch (PDOException $e) {
    $engagement = [
        'total_users' => 0,
        'active_users_7d' => 0,
        'active_users_30d' => 0,
        'avg_session_length' => 0,
    ];
}

// Game performance metrics
try {
    $performance = [
        'total_quizzes' => $db->query("SELECT COUNT(*) FROM scores")->fetchColumn(),
        'total_battles' => $db->query("SELECT COUNT(*) FROM battle_log")->fetchColumn() ?: 0,
        'avg_quiz_score' => $db->query("SELECT AVG(score * 100.0 / total_questions) FROM scores WHERE total_questions > 0")->fetchColumn(),
        'battle_win_rate' => $db->query("SELECT AVG(won) * 100 FROM battle_log")->fetchColumn() ?: 0,
    ];
} catch (PDOException $e) {
    $performance = [
        'total_quizzes' => 0,
        'total_battles' => 0,
        'avg_quiz_score' => 0,
        'battle_win_rate' => 0,
    ];
}

// Economic metrics
try {
    $economy = [
        'total_coins_earned' => $db->query("SELECT SUM(coins) FROM users")->fetchColumn() ?: 0,
        'total_xp_earned' => $db->query("SELECT SUM(xp) FROM users")->fetchColumn() ?: 0,
        'avg_coins_per_user' => $db->query("SELECT AVG(coins) FROM users")->fetchColumn() ?: 0,
        'items_in_inventory' => $db->query("SELECT COUNT(*) FROM inventory")->fetchColumn() ?: 0,
    ];
} catch (PDOException $e) {
    $economy = [
        'total_coins_earned' => 0,
        'total_xp_earned' => 0,
        'avg_coins_per_user' => 0,
        'items_in_inventory' => 0,
    ];
}

// Daily activity (last 30 days)
try {
    $daily_activity = $db->query("
        SELECT DATE(created_at) as date, COUNT(*) as activity
        FROM (
            SELECT created_at FROM scores
            UNION ALL
            SELECT created_at FROM battle_log
        ) combined
        WHERE created_at >= ?
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ")->fetchAll(PDO::FETCH_ASSOC, [$date_from]);
} catch (PDOException $e) {
    $daily_activity = [];
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="space-y-6">
    <!-- Date Filter -->
    <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
        <form method="GET" class="flex flex-col sm:flex-row gap-3 items-end">
            <div>
                <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">From</label>
                <input type="date" name="date_from" value="<?php echo $date_from; ?>" class="bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
            </div>
            <div>
                <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">To</label>
                <input type="date" name="date_to" value="<?php echo $date_to; ?>" class="bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
            </div>
            <button type="submit" class="bg-[#0038A8] hover:bg-[#0047b3] text-white px-4 py-2 rounded-lg transition text-sm">
                <i class="fas fa-filter mr-2"></i>Apply Filter
            </button>
            <a href="/dashboard/analytics.php" class="bg-gray-600 hover:bg-gray-500 text-white px-4 py-2 rounded-lg transition text-sm">
                Reset
            </a>
        </form>
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-xs lg:text-sm">Total Users</p>
                    <p class="text-2xl lg:text-3xl font-bold text-white mt-1"><?php echo number_format($engagement['total_users']); ?></p>
                </div>
                <div class="w-12 h-12 bg-[#0038A8]/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-[#0038A8] text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-xs lg:text-sm">Active (7d)</p>
                    <p class="text-2xl lg:text-3xl font-bold text-white mt-1"><?php echo number_format($engagement['active_users_7d']); ?></p>
                </div>
                <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line text-green-500 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-xs lg:text-sm">Total Quizzes</p>
                    <p class="text-2xl lg:text-3xl font-bold text-white mt-1"><?php echo number_format($performance['total_quizzes']); ?></p>
                </div>
                <div class="w-12 h-12 bg-purple-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-book text-purple-500 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-xs lg:text-sm">Total Battles</p>
                    <p class="text-2xl lg:text-3xl font-bold text-white mt-1"><?php echo number_format($performance['total_battles']); ?></p>
                </div>
                <div class="w-12 h-12 bg-red-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-swords text-red-500 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
        <!-- Hero Class Distribution -->
        <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
            <h3 class="text-base lg:text-lg font-bold text-white mb-4"><i class="fas fa-users mr-2"></i>Hero Class Distribution</h3>
            <div class="h-64 lg:h-80">
                <canvas id="heroChart"></canvas>
            </div>
        </div>

        <!-- Most Played Categories -->
        <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
            <h3 class="text-base lg:text-lg font-bold text-white mb-4"><i class="fas fa-chart-bar mr-2"></i>Most Played Categories</h3>
            <div class="h-64 lg:h-80">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Top 10 Active Users -->
    <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
        <h3 class="text-base lg:text-lg font-bold text-white mb-4"><i class="fas fa-trophy mr-2"></i>Top 10 Most Active Users (Battles)</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Rank</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Username</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Hero Class</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Level</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Battles</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <?php foreach ($top_users as $index => $user): ?>
                        <tr class="hover:bg-gray-700/50">
                            <td class="px-4 lg:px-6 py-4 whitespace-nowrap">
                                <?php if ($index === 0): ?>
                                    <span class="text-yellow-400 text-lg lg:text-xl"><i class="fas fa-crown"></i></span>
                                <?php elseif ($index === 1): ?>
                                    <span class="text-gray-300 text-lg lg:text-xl"><i class="fas fa-medal"></i></span>
                                <?php elseif ($index === 2): ?>
                                    <span class="text-orange-400 text-lg lg:text-xl"><i class="fas fa-medal"></i></span>
                                <?php else: ?>
                                    <span class="text-gray-400 text-sm">#<?php echo $index + 1; ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm font-medium text-white truncate max-w-[120px] lg:max-w-none"><?php echo htmlspecialchars($user['username']); ?></td>
                            <td class="px-4 lg:px-6 py-4 whitespace-nowrap">
                                <?php
                                $class_colors = [
                                    'mandirigma' => 'bg-blue-500',
                                    'lakambini' => 'bg-pink-500',
                                    'mangkukulam' => 'bg-purple-500'
                                ];
                                $color = $class_colors[$user['hero_class']] ?? 'bg-gray-500';
                                $class_name = ucfirst($user['hero_class'] ?? 'None');
                                ?>
                                <span class="px-2 lg:px-3 py-1 rounded-full text-xs font-medium text-white <?php echo $color; ?>">
                                    <?php echo $class_name; ?>
                                </span>
                            </td>
                            <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo $user['level']; ?></td>
                            <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm font-bold text-white"><?php echo number_format($user['battles']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Engagement Metrics -->
    <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
        <h3 class="text-base lg:text-lg font-bold text-white mb-4">
            <i class="fas fa-chart-line mr-2 text-[#0038A8]"></i>User Engagement
        </h3>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-gray-700/50 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-white"><?php echo number_format($engagement['active_users_30d']); ?></p>
                <p class="text-gray-400 text-xs mt-1">Active Users (30d)</p>
            </div>
            <div class="bg-gray-700/50 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-white"><?php echo gmdate('i:s', $engagement['avg_session_length'] ?: 0); ?></p>
                <p class="text-gray-400 text-xs mt-1">Avg Session Length</p>
            </div>
            <div class="bg-gray-700/50 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-white"><?php echo round(($engagement['active_users_7d'] / max($engagement['total_users'], 1)) * 100, 1); ?>%</p>
                <p class="text-gray-400 text-xs mt-1">Weekly Active Rate</p>
            </div>
            <div class="bg-gray-700/50 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-white"><?php echo round(($engagement['active_users_30d'] / max($engagement['total_users'], 1)) * 100, 1); ?>%</p>
                <p class="text-gray-400 text-xs mt-1">Monthly Active Rate</p>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
        <h3 class="text-base lg:text-lg font-bold text-white mb-4">
            <i class="fas fa-tachometer-alt mr-2 text-[#0038A8]"></i>Game Performance
        </h3>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-gray-700/50 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-white"><?php echo round($performance['avg_quiz_score'], 1); ?>%</p>
                <p class="text-gray-400 text-xs mt-1">Avg Quiz Score</p>
            </div>
            <div class="bg-gray-700/50 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-white"><?php echo round($performance['battle_win_rate'], 1); ?>%</p>
                <p class="text-gray-400 text-xs mt-1">Battle Win Rate</p>
            </div>
            <div class="bg-gray-700/50 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-white"><?php echo number_format($performance['total_quizzes'] / max($engagement['total_users'], 1), 1); ?></p>
                <p class="text-gray-400 text-xs mt-1">Quizzes Per User</p>
            </div>
            <div class="bg-gray-700/50 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-white"><?php echo number_format($performance['total_battles'] / max($engagement['total_users'], 1), 1); ?></p>
                <p class="text-gray-400 text-xs mt-1">Battles Per User</p>
            </div>
        </div>
    </div>

    <!-- Economic Metrics -->
    <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
        <h3 class="text-base lg:text-lg font-bold text-white mb-4">
            <i class="fas fa-coins mr-2 text-[#0038A8]"></i>Economic Metrics
        </h3>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-gray-700/50 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-yellow-500"><?php echo number_format($economy['total_coins_earned']); ?></p>
                <p class="text-gray-400 text-xs mt-1">Total Coins Earned</p>
            </div>
            <div class="bg-gray-700/50 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-[#0038A8]"><?php echo number_format($economy['total_xp_earned']); ?></p>
                <p class="text-gray-400 text-xs mt-1">Total XP Earned</p>
            </div>
            <div class="bg-gray-700/50 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-white"><?php echo number_format($economy['avg_coins_per_user']); ?></p>
                <p class="text-gray-400 text-xs mt-1">Avg Coins Per User</p>
            </div>
            <div class="bg-gray-700/50 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-white"><?php echo number_format($economy['items_in_inventory']); ?></p>
                <p class="text-gray-400 text-xs mt-1">Items in Inventory</p>
            </div>
        </div>
    </div>

    <!-- Daily Activity Chart -->
    <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
        <h3 class="text-base lg:text-lg font-bold text-white mb-4">
            <i class="fas fa-chart-area mr-2 text-[#0038A8]"></i>Daily Activity
        </h3>
        <div class="h-64 lg:h-80">
            <canvas id="activityChart"></canvas>
        </div>
    </div>
</div>

<script>
// Hero Class Distribution Chart
const heroCtx = document.getElementById('heroChart').getContext('2d');
new Chart(heroCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_map('ucfirst', array_column($hero_dist, 'hero_class'))); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($hero_dist, 'count')); ?>,
            backgroundColor: ['#0038A8', '#EC407A', '#9C27B0', '#607D8B']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    color: '#9ca3af',
                    font: { size: window.innerWidth < 640 ? 10 : 12 },
                    padding: window.innerWidth < 640 ? 8 : 12
                }
            }
        }
    }
});

// Category Plays Chart
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
new Chart(categoryCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($category_plays, 'name')); ?>,
        datasets: [{
            label: 'Plays',
            data: <?php echo json_encode(array_column($category_plays, 'plays')); ?>,
            backgroundColor: '#0038A8'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    color: '#9ca3af',
                    font: { size: window.innerWidth < 640 ? 10 : 12 }
                },
                grid: { color: 'rgba(255,255,255,0.05)' }
            },
            x: {
                ticks: {
                    color: '#9ca3af',
                    font: { size: window.innerWidth < 640 ? 10 : 12 }
                },
                grid: { display: false }
            }
        }
    }
});

// Daily Activity Chart
const activityCtx = document.getElementById('activityChart').getContext('2d');
new Chart(activityCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($daily_activity, 'date')); ?>,
        datasets: [{
            label: 'Activity',
            data: <?php echo json_encode(array_column($daily_activity, 'activity')); ?>,
            borderColor: '#0038A8',
            backgroundColor: 'rgba(0, 56, 168, 0.1)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    color: '#9ca3af',
                    font: { size: window.innerWidth < 640 ? 10 : 12 }
                },
                grid: { color: 'rgba(255,255,255,0.05)' }
            },
            x: {
                ticks: {
                    color: '#9ca3af',
                    font: { size: window.innerWidth < 640 ? 10 : 12 },
                    maxTicksLimit: 10
                },
                grid: { display: false }
            }
        }
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
