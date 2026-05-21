<?php
session_start();
require_once 'includes/header.php';
require_once 'includes/functions.php';

// Get categories and leaderboard data
$categories = getCategories();
$leaderboard = getLeaderboard(null, 3, 0);
?>

<!-- Hero Section -->
<section class="min-h-screen flex items-center justify-center relative overflow-hidden" style="background: linear-gradient(135deg, #0038A8 0%, #1a1a2e 50%, #CE1126 100%);">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-20 left-20 w-32 h-32 bg-yellow-400 rounded-full blur-3xl"></div>
        <div class="absolute bottom-20 right-20 w-48 h-48 bg-yellow-400 rounded-full blur-3xl"></div>
    </div>
    
    <div class="text-center z-10 px-4">
        <h1 class="text-6xl md:text-8xl font-bold font-serif text-white mb-6 drop-shadow-lg">
            Bayani Quiz
        </h1>
        <p class="text-xl md:text-2xl text-yellow-400 font-medium mb-8 max-w-2xl mx-auto">
            Subukan ang iyong kaalaman sa kasaysayan ng Pilipinas
        </p>
        <a href="#categories" class="inline-block bg-yellow-400 text-[#0038A8] px-8 py-4 rounded-full font-bold text-lg hover:bg-yellow-300 transition transform hover:scale-105 shadow-lg">
            <i class="fas fa-play mr-2"></i> Magsimula
        </a>
        
        <!-- Language Toggle -->
        <div class="mt-8">
            <button id="langToggle" class="text-white hover:text-yellow-400 transition text-sm">
                <i class="fas fa-globe mr-2"></i>
                <span id="langText">English</span>
            </button>
        </div>
    </div>
</section>

<!-- Category Selection Grid -->
<section id="categories" class="py-20 px-4 bg-gray-50">
    <div class="max-w-6xl mx-auto">
        <h2 class="text-4xl font-bold font-serif text-center mb-4 text-[#0038A8]">
            Piliin ang Kategorya
        </h2>
        <p class="text-center text-gray-600 mb-12 max-w-2xl mx-auto">
            Pumili ng kategorya at simulan ang pagsubok sa iyong kaalaman
        </p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($categories as $category): ?>
                <?php
                $colorClasses = [
                    'yellow' => 'from-yellow-400 to-yellow-600',
                    'red' => 'from-red-400 to-red-600',
                    'green' => 'from-green-400 to-green-600',
                    'blue' => 'from-blue-400 to-blue-600',
                    'purple' => 'from-purple-400 to-purple-600'
                ];
                $gradient = $colorClasses[$category['color']] ?? 'from-gray-400 to-gray-600';
                ?>
                <a href="quiz.php?category=<?php echo $category['id']; ?>" 
                   class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-2xl transition transform hover:-translate-y-2 border-2 border-transparent hover:border-yellow-400 group">
                    <div class="flex items-center mb-4">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-br <?php echo $gradient; ?> flex items-center justify-center text-white text-2xl group-hover:scale-110 transition">
                            <i class="fas <?php echo $category['icon']; ?>"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-xl font-bold text-gray-800 group-hover:text-[#0038A8] transition">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </h3>
                            <span class="text-sm text-gray-500">
                                <i class="fas fa-question-circle mr-1"></i> 10 Questions
                            </span>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-4">
                        <?php echo htmlspecialchars($category['description']); ?>
                    </p>
                    <div class="flex items-center justify-between">
                        <span class="px-3 py-1 bg-gray-100 rounded-full text-xs font-medium text-gray-600">
                            Medium
                        </span>
                        <span class="text-[#0038A8] font-medium group-hover:translate-x-2 transition">
                            Play <i class="fas fa-arrow-right ml-1"></i>
                        </span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Live Leaderboard Preview -->
<section class="py-20 px-4 bg-white">
    <div class="max-w-4xl mx-auto">
        <h2 class="text-4xl font-bold font-serif text-center mb-4 text-[#0038A8]">
            Top Scores
        </h2>
        <p class="text-center text-gray-600 mb-8">
            Tingnan ang mga nangungunang manlalaro
        </p>
        
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200">
            <?php if (empty($leaderboard)): ?>
                <div class="text-center py-12 text-gray-500">
                    <i class="fas fa-trophy text-4xl mb-4"></i>
                    <p>Walang mga score pa. Maging unang manlalaro!</p>
                </div>
            <?php else: ?>
                <div class="divide-y divide-gray-200">
                    <?php foreach ($leaderboard as $index => $score): ?>
                        <?php
                        $rank = $index + 1;
                        $rankClasses = [
                            1 => 'bg-yellow-100 text-yellow-800',
                            2 => 'bg-gray-100 text-gray-800',
                            3 => 'bg-orange-100 text-orange-800'
                        ];
                        $rankClass = $rankClasses[$rank] ?? 'bg-gray-50 text-gray-600';
                        $rankIcon = $rank <= 3 ? 'fas fa-trophy' : '';
                        ?>
                        <div class="flex items-center px-6 py-4 hover:bg-gray-50 transition">
                            <div class="w-12 h-12 rounded-full <?php echo $rankClass; ?> flex items-center justify-center font-bold text-lg mr-4">
                                <?php if ($rankIcon): ?>
                                    <i class="<?php echo $rankIcon; ?>"></i>
                                <?php else: ?>
                                    <?php echo $rank; ?>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-800">
                                    <?php echo htmlspecialchars($score['player_name']); ?>
                                </p>
                                <p class="text-sm text-gray-500">
                                    <?php echo htmlspecialchars($score['category_name']); ?>
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-bold text-[#0038A8]">
                                    <?php echo $score['score']; ?>/10
                                </p>
                                <p class="text-xs text-gray-500">
                                    <?php echo date('M d', strtotime($score['created_at'])); ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="text-center mt-8">
            <a href="leaderboard.php" class="inline-block bg-[#0038A8] text-white px-6 py-3 rounded-full font-medium hover:bg-[#002870] transition">
                View Full Leaderboard <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>
</section>

<script>
// Language toggle
const langToggle = document.getElementById('langToggle');
const langText = document.getElementById('langText');
let currentLang = localStorage.getItem('lang') || 'en';

langText.textContent = currentLang === 'en' ? 'English' : 'Filipino';

langToggle.addEventListener('click', () => {
    currentLang = currentLang === 'en' ? 'fil' : 'en';
    localStorage.setItem('lang', currentLang);
    langText.textContent = currentLang === 'en' ? 'English' : 'Filipino';
});
</script>

<?php require_once 'includes/footer.php'; ?>
