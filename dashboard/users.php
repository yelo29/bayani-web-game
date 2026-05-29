<?php
error_reporting(0);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$page_title = 'Users';
$db = getDB();

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$query = "SELECT id, username, email, hero_class, level, xp, coins, created_at, is_banned, is_admin FROM users WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND username LIKE ?";
    $params[] = "%$search%";
}

$query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;

// Get total count for pagination
$count_query = "SELECT COUNT(*) FROM users WHERE 1=1";
$count_params = [];
if ($search) {
    $count_query .= " AND username LIKE ?";
    $count_params[] = "%$search%";
}
$total = $db->prepare($count_query);
$total->execute($count_params);
$total_users = $total->fetchColumn();
$total_pages = ceil($total_users / $per_page);

// Get users
$stmt = $db->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/includes/header.php';
?>

<div class="space-y-6">
    <!-- Search -->
    <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <input
                type="text"
                name="search"
                value="<?php echo htmlspecialchars($search); ?>"
                placeholder="Search by username..."
                class="flex-1 bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm"
            >
            <div class="flex gap-2">
                <button type="submit" class="bg-[#0038A8] hover:bg-[#0047b3] text-white px-4 lg:px-6 py-2 rounded-lg transition text-sm flex-shrink-0">
                    <i class="fas fa-search mr-1 lg:mr-2"></i><span class="hidden sm:inline">Search</span>
                </button>
                <?php if ($search): ?>
                    <a href="/dashboard/users.php" class="bg-gray-600 hover:bg-gray-500 text-white px-4 lg:px-6 py-2 rounded-lg transition text-sm flex-shrink-0">
                        <i class="fas fa-times mr-1 lg:mr-2"></i><span class="hidden sm:inline">Clear</span>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Username</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Hero Class</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Level</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">XP</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Coins</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Registered</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-gray-700/50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo $user['id']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white"><?php echo htmlspecialchars($user['username']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $class_colors = [
                                    'mandirigma' => 'bg-blue-500',
                                    'lakambini' => 'bg-pink-500',
                                    'mangkukulam' => 'bg-purple-500'
                                ];
                                $color = $class_colors[$user['hero_class']] ?? 'bg-gray-500';
                                $class_name = ucfirst($user['hero_class'] ?? 'None');
                                ?>
                                <span class="px-3 py-1 rounded-full text-xs font-medium text-white <?php echo $color; ?>">
                                    <?php echo $class_name; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo $user['level']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo number_format($user['xp']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo number_format($user['coins']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($user['is_banned']): ?>
                                    <span class="px-2 py-1 bg-red-500/20 text-red-400 rounded-full text-xs font-medium">Banned</span>
                                <?php elseif ($user['is_admin']): ?>
                                    <span class="px-2 py-1 bg-purple-500/20 text-purple-400 rounded-full text-xs font-medium">Admin</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 bg-green-500/20 text-green-400 rounded-full text-xs font-medium">Active</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="/dashboard/user-edit.php?id=<?php echo $user['id']; ?>" class="text-[#0038A8] hover:text-[#0047b3] text-sm font-medium">
                                    <i class="fas fa-edit mr-1"></i>Edit
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="bg-gray-700 px-4 lg:px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-xs sm:text-sm text-gray-300 text-center sm:text-left">
                Showing <?php echo ($offset + 1); ?> to <?php echo min($offset + $per_page, $total_users); ?> of <?php echo $total_users; ?> users
            </p>
            <div class="flex gap-2">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="px-3 lg:px-4 py-2 bg-gray-600 hover:bg-gray-500 rounded-lg text-white transition text-sm">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>
                <span class="px-3 lg:px-4 py-2 bg-[#0038A8] rounded-lg text-white text-sm"><?php echo $page; ?></span>
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="px-3 lg:px-4 py-2 bg-gray-600 hover:bg-gray-500 rounded-lg text-white transition text-sm">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
