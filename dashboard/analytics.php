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

// Hero class distribution
$hero_dist = $db->query("SELECT hero_class, COUNT(*) as count FROM users GROUP BY hero_class")->fetchAll(PDO::FETCH_ASSOC);

// Most played categories
$category_plays = $db->query("
    SELECT c.name, COUNT(s.id) as plays 
    FROM scores s 
    JOIN categories c ON s.category_id = c.id 
    GROUP BY c.id 
    ORDER BY plays DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Top 10 most active users by battles
$top_users = $db->query("
    SELECT u.username, u.hero_class, u.level, COUNT(b.id) as battles 
    FROM battle_log b 
    JOIN users u ON b.user_id = u.id 
    GROUP BY u.id 
    ORDER BY battles DESC 
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/includes/header.php';
?>

<div class="space-y-6">
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
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
