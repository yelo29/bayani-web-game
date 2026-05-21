<?php
session_start();
require_once 'includes/header.php';
require_once 'includes/functions.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user data
$userId = $_SESSION['user_id'];
$userData = getUserData($userId);
$userStats = getUserStats($userId);
$achievements = getUserAchievements($userId);

// Hero class info
$heroClasses = [
    'mandirigma' => ['name' => 'Mandirigma', 'title' => 'Warrior', 'color' => '#CE1126', 'icon' => 'fa-shield-alt'],
    'lakambini' => ['name' => 'Lakambini', 'title' => 'Scholar', 'color' => '#0038A8', 'icon' => 'fa-book'],
    'mangkukulam' => ['name' => 'Mangkukulam', 'title' => 'Mystic', 'color' => '#FCD116', 'icon' => 'fa-bolt']
];

$currentHero = $heroClasses[$userData['hero_class']] ?? null;
?>

<main class="min-h-screen bg-gray-50 py-8 px-4">
    <div class="max-w-5xl mx-auto">
        <!-- Profile Header -->
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-6">
            <div class="flex flex-col md:flex-row items-center gap-8">
                <div class="w-32 h-32 rounded-full flex items-center justify-center text-white text-5xl" style="background: <?php echo $currentHero ? $currentHero['color'] : '#0038A8'; ?>;">
                    <i class="fas <?php echo $currentHero ? $currentHero['icon'] : 'fa-user'; ?>"></i>
                </div>
                <div class="flex-1 text-center md:text-left">
                    <h1 class="text-4xl font-bold font-serif text-[#0038A8] mb-2">
                        <?php echo htmlspecialchars($userData['username']); ?>
                    </h1>
                    <?php if ($currentHero): ?>
                        <p class="text-xl text-gray-600 mb-2">
                            <span class="px-3 py-1 rounded-full text-white font-bold" style="background: <?php echo $currentHero['color']; ?>;">
                                <?php echo $currentHero['name']; ?> - <?php echo $currentHero['title']; ?>
                            </span>
                        </p>
                    <?php endif; ?>
                    <p class="text-gray-500">Member since <?php echo date('F j, Y', strtotime($userData['created_at'])); ?></p>
                </div>
                <div class="text-center">
                    <p class="text-5xl font-bold text-[#0038A8]"><?php echo $userData['level']; ?></p>
                    <p class="text-gray-600">Level</p>
                </div>
            </div>
        </div>

        <!-- XP Progress -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <h3 class="text-xl font-bold text-[#0038A8] mb-4">Experience Progress</h3>
            <div class="flex items-center justify-between mb-4">
                <div class="text-center">
                    <p class="text-3xl font-bold text-green-500"><?php echo $userData['xp']; ?></p>
                    <p class="text-sm text-gray-600">Total XP</p>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-bold text-[#0038A8]"><?php echo $userData['level']; ?></p>
                    <p class="text-sm text-gray-600">Current Level</p>
                </div>
                <div class="text-center">
                    <?php
                    $nextLevelXP = getXPForNextLevel($userData['level']);
                    $prevLevelXP = $userData['level'] === 1 ? 0 : getXPForNextLevel($userData['level'] - 1);
                    $xpToNext = $userData['level'] >= 10 ? 0 : $nextLevelXP - $userData['xp'];
                    ?>
                    <p class="text-3xl font-bold text-yellow-500"><?php echo $xpToNext; ?></p>
                    <p class="text-sm text-gray-600">XP to Next Level</p>
                </div>
            </div>
            <?php
            $progress = $userData['level'] >= 10 ? 100 : (($userData['xp'] - $prevLevelXP) / ($nextLevelXP - $prevLevelXP)) * 100;
            ?>
            <div class="w-full bg-gray-200 rounded-full h-6">
                <div class="bg-gradient-to-r from-yellow-400 to-yellow-600 h-6 rounded-full transition-all duration-1000" style="width: <?php echo $progress; ?>%"></div>
            </div>
            <p class="text-sm text-gray-600 mt-2 text-center">
                <?php if ($userData['level'] >= 10): ?>
                    🏆 Bayani ng Bayan - Maximum Level Achieved!
                <?php else: ?>
                    Level <?php echo $userData['level']; ?> → Level <?php echo $userData['level'] + 1; ?>
                <?php endif; ?>
            </p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-2xl shadow-lg p-6 text-center">
                <i class="fas fa-gamepad text-4xl text-[#0038A8] mb-4"></i>
                <p class="text-3xl font-bold text-gray-800"><?php echo $userStats['total_quizzes']; ?></p>
                <p class="text-gray-600">Quizzes Taken</p>
            </div>
            <div class="bg-white rounded-2xl shadow-lg p-6 text-center">
                <i class="fas fa-trophy text-4xl text-yellow-500 mb-4"></i>
                <p class="text-3xl font-bold text-gray-800"><?php echo $userStats['best_score']; ?>/10</p>
                <p class="text-gray-600">Best Score</p>
            </div>
            <div class="bg-white rounded-2xl shadow-lg p-6 text-center">
                <i class="fas fa-medal text-4xl text-green-500 mb-4"></i>
                <p class="text-3xl font-bold text-gray-800"><?php echo count($achievements); ?></p>
                <p class="text-gray-600">Achievements</p>
            </div>
        </div>

        <!-- Achievements -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <h3 class="text-xl font-bold text-[#0038A8] mb-4">Achievements</h3>
            <?php if (empty($achievements)): ?>
                <p class="text-gray-500 text-center py-8">No achievements yet. Keep playing to earn badges!</p>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($achievements as $achievement): ?>
                        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 border-2 border-yellow-400 rounded-xl p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-full bg-yellow-400 flex items-center justify-center text-white">
                                    <i class="fas fa-star"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-800"><?php echo htmlspecialchars($achievement['achievement_name']); ?></p>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($achievement['achievement_description']); ?></p>
                                    <p class="text-xs text-gray-500 mt-1"><?php echo date('M j, Y', strtotime($achievement['earned_at'])); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recent Quiz History -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <h3 class="text-xl font-bold text-[#0038A8] mb-4">Recent Quiz History</h3>
            <?php if (empty($userStats['recent_quizzes'])): ?>
                <p class="text-gray-500 text-center py-8">No quizzes taken yet. Start your journey!</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($userStats['recent_quizzes'] as $quiz): ?>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full bg-[#0038A8] flex items-center justify-center text-white font-bold">
                                    <?php echo $quiz['score']; ?>/<?php echo $quiz['total_questions']; ?>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($quiz['category_name']); ?></p>
                                    <p class="text-sm text-gray-500"><?php echo date('M j, Y g:i A', strtotime($quiz['created_at'])); ?></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-yellow-500">+<?php echo $quiz['xp_earned']; ?> XP</p>
                                <p class="text-sm text-gray-500"><?php echo gmdate('i:s', $quiz['time_taken']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4">
            <a href="index.php" class="flex-1 bg-[#0038A8] text-white px-6 py-4 rounded-xl font-bold text-center hover:bg-[#002870] transition">
                <i class="fas fa-home mr-2"></i>Back to Home
            </a>
            <a href="leaderboard.php" class="flex-1 bg-gray-200 text-gray-800 px-6 py-4 rounded-xl font-bold text-center hover:bg-gray-300 transition">
                <i class="fas fa-trophy mr-2"></i>View Leaderboard
            </a>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
