<?php
error_reporting(0);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$page_title = 'Dashboard';

$db = getDB();

// Get stats with error handling
try {
    $total_users     = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $total_questions = $db->query("SELECT COUNT(*) FROM questions")->fetchColumn();
    $battles_today   = $db->query("SELECT COUNT(*) FROM battle_log WHERE DATE(created_at) = CURDATE()")->fetchColumn();
    $quiz_today      = $db->query("SELECT COUNT(*) FROM scores WHERE DATE(created_at) = CURDATE()")->fetchColumn();
    $new_users_week  = $db->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();

    $popular_category = $db->query("
        SELECT c.name, COUNT(s.id) as plays 
        FROM scores s 
        JOIN categories c ON s.category_id = c.id 
        GROUP BY c.id 
        ORDER BY plays DESC 
        LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);

    // 7-day user registration data
    $registration_data = [];
    for ($i = 6; $i >= 0; $i--) {
        $date  = date('Y-m-d', strtotime("-$i days"));
        $count = $db->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) = '$date'")->fetchColumn();
        $registration_data[] = [
            'date'  => date('M j', strtotime($date)),
            'count' => (int)$count
        ];
    }

    // Category distribution
    $category_dist = $db->query("
        SELECT c.name, COUNT(q.id) as count 
        FROM categories c 
        LEFT JOIN questions q ON c.id = q.category_id 
        GROUP BY c.id
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $total_users = $total_questions = $battles_today = $quiz_today = $new_users_week = 0;
    $popular_category  = ['name' => 'N/A', 'plays' => 0];
    $registration_data = [];
    $category_dist     = [];
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="space-y-6">
    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">
        <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <p class="text-gray-400 text-xs lg:text-sm truncate">Total Users</p>
                    <p class="text-2xl lg:text-3xl font-bold text-white truncate"><?php echo number_format($total_users); ?></p>
                </div>
                <div class="w-10 h-10 lg:w-12 lg:h-12 bg-blue-500/20 rounded-full flex items-center justify-center flex-shrink-0 ml-2">
                    <i class="fas fa-users text-blue-500 text-lg lg:text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <p class="text-gray-400 text-xs lg:text-sm truncate">Total Questions</p>
                    <p class="text-2xl lg:text-3xl font-bold text-white truncate"><?php echo number_format($total_questions); ?></p>
                </div>
                <div class="w-10 h-10 lg:w-12 lg:h-12 bg-green-500/20 rounded-full flex items-center justify-center flex-shrink-0 ml-2">
                    <i class="fas fa-question-circle text-green-500 text-lg lg:text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <p class="text-gray-400 text-xs lg:text-sm truncate">Battles Today</p>
                    <p class="text-2xl lg:text-3xl font-bold text-white truncate"><?php echo number_format($battles_today); ?></p>
                </div>
                <div class="w-10 h-10 lg:w-12 lg:h-12 bg-red-500/20 rounded-full flex items-center justify-center flex-shrink-0 ml-2">
                    <i class="fas fa-swords text-red-500 text-lg lg:text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <p class="text-gray-400 text-xs lg:text-sm truncate">Quiz Games Today</p>
                    <p class="text-2xl lg:text-3xl font-bold text-white truncate"><?php echo number_format($quiz_today); ?></p>
                </div>
                <div class="w-10 h-10 lg:w-12 lg:h-12 bg-purple-500/20 rounded-full flex items-center justify-center flex-shrink-0 ml-2">
                    <i class="fas fa-book-open text-purple-500 text-lg lg:text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <p class="text-gray-400 text-xs lg:text-sm truncate">New Users (7 Days)</p>
                    <p class="text-2xl lg:text-3xl font-bold text-white truncate"><?php echo number_format($new_users_week); ?></p>
                </div>
                <div class="w-10 h-10 lg:w-12 lg:h-12 bg-yellow-500/20 rounded-full flex items-center justify-center flex-shrink-0 ml-2">
                    <i class="fas fa-user-plus text-yellow-500 text-lg lg:text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <p class="text-gray-400 text-xs lg:text-sm truncate">Popular Category</p>
                    <p class="text-base lg:text-lg lg:text-xl font-bold text-white truncate"><?php echo htmlspecialchars($popular_category['name'] ?? 'N/A'); ?></p>
                    <p class="text-xs lg:text-sm text-gray-400 truncate"><?php echo number_format($popular_category['plays'] ?? 0); ?> plays</p>
                </div>
                <div class="w-10 h-10 lg:w-12 lg:h-12 bg-[#0038A8]/20 rounded-full flex items-center justify-center flex-shrink-0 ml-2">
                    <i class="fas fa-star text-[#FFD700] text-lg lg:text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
        <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
            <h3 class="text-base lg:text-lg font-bold text-white mb-4 truncate">7-Day User Registrations</h3>
            <div class="h-64 lg:h-80">
                <canvas id="registrationChart"></canvas>
            </div>
        </div>

        <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
            <h3 class="text-base lg:text-lg font-bold text-white mb-4 truncate">Category Distribution</h3>
            <div class="h-64 lg:h-80">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
// Registration Chart
const registrationCtx = document.getElementById('registrationChart').getContext('2d');
new Chart(registrationCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($registration_data, 'date')); ?>,
        datasets: [{
            label: 'New Users',
            data: <?php echo json_encode(array_column($registration_data, 'count')); ?>,
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
                    stepSize: 1,
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
                grid: { color: 'rgba(255,255,255,0.05)' }
            }
        }
    }
});

// Category Chart
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
new Chart(categoryCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($category_dist, 'name')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($category_dist, 'count')); ?>,
            backgroundColor: ['#0038A8', '#CE1126', '#FFD700', '#16a34a', '#9333ea']
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
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>