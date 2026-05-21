<?php
session_start();
require_once 'includes/functions.php';

// Check if quiz was completed
if (!isset($_SESSION['quiz_started'])) {
    header('Location: index.php');
    exit;
}

// Get quiz results
$score = $_SESSION['quiz_score'];
$totalQuestions = count($_SESSION['quiz_questions']);
$categoryId = $_SESSION['quiz_category_id'];
$answers = $_SESSION['quiz_answers'];
$startTime = $_SESSION['quiz_start_time'];
$timeTaken = time() - $startTime;

// Get category name
$categories = getCategories();
$categoryName = '';
foreach ($categories as $cat) {
    if ($cat['id'] === $categoryId) {
        $categoryName = $cat['name'];
        break;
    }
}

// Determine performance message
$percentage = ($score / $totalQuestions) * 100;
if ($score === $totalQuestions) {
    $message = 'Tunay na Bayani! 🏆';
    $messageClass = 'text-yellow-500';
} elseif ($score >= 7) {
    $message = 'Mahusay! Isa kang tunay na Pilipino! 💪';
    $messageClass = 'text-green-500';
} elseif ($score >= 4) {
    $message = 'Magaling! Kaunti pang pag-aaral! 📚';
    $messageClass = 'text-blue-500';
} else {
    $message = 'Huwag sumuko! Subukan ulit! 🔥';
    $messageClass = 'text-red-500';
}

// Handle score saving
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['player_name'])) {
    $playerName = sanitize($_POST['player_name']);
    if (!empty($playerName)) {
        saveScore($playerName, $categoryId, $score, $totalQuestions, $timeTaken);
        $_SESSION['score_saved'] = true;
        $_SESSION['saved_player_name'] = $playerName;
        header('Location: results.php');
        exit;
    }
}

require_once 'includes/header.php';
?>

<main class="min-h-screen bg-gray-50 py-8 px-4">
    <div class="max-w-4xl mx-auto">
        <!-- Score Display -->
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-6 text-center">
            <h2 class="text-3xl font-bold font-serif text-[#0038A8] mb-4">Quiz Complete!</h2>
            <div class="mb-6">
                <span id="scoreCounter" class="text-7xl font-bold text-[#0038A8]">0</span>
                <span class="text-4xl text-gray-400">/<?php echo $totalQuestions; ?></span>
            </div>
            <p class="text-2xl font-bold <?php echo $messageClass; ?> mb-4"><?php echo $message; ?></p>
            <p class="text-gray-600">
                Category: <?php echo htmlspecialchars($categoryName); ?> | 
                Time: <?php echo gmdate('i:s', $timeTaken); ?>
            </p>
        </div>

        <!-- Save Score Form -->
        <?php if (!isset($_SESSION['score_saved'])): ?>
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <h3 class="text-xl font-bold text-[#0038A8] mb-4">Save Your Score</h3>
            <form method="POST" action="results.php">
                <div class="flex gap-4">
                    <input type="text" 
                           name="player_name" 
                           placeholder="Enter your name" 
                           required
                           maxlength="80"
                           class="flex-1 px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-[#0038A8] focus:outline-none transition">
                    <button type="submit" class="bg-[#0038A8] text-white px-8 py-3 rounded-xl font-bold hover:bg-[#002870] transition">
                        Save Score
                    </button>
                </div>
            </form>
        </div>
        <?php else: ?>
        <div class="bg-green-50 border-2 border-green-500 rounded-2xl p-6 mb-6 text-center">
            <i class="fas fa-check-circle text-green-500 text-3xl mb-2"></i>
            <p class="text-green-700 font-bold">Score saved as <?php echo htmlspecialchars($_SESSION['saved_player_name']); ?>!</p>
        </div>
        <?php endif; ?>

        <!-- Review Section -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <h3 class="text-xl font-bold text-[#0038A8] mb-4">Review Your Answers</h3>
            <div class="space-y-4">
                <?php foreach ($_SESSION['quiz_questions'] as $index => $question): ?>
                    <?php
                    $answer = $answers[$index] ?? null;
                    $isCorrect = $answer && $answer['selected'] === $answer['correct'];
                    ?>
                    <div class="border-2 <?php echo $isCorrect ? 'border-green-500 bg-green-50' : 'border-red-500 bg-red-50'; ?> rounded-xl p-4">
                        <div class="flex items-start gap-4">
                            <div class="w-8 h-8 rounded-full <?php echo $isCorrect ? 'bg-green-500' : 'bg-red-500'; ?> text-white flex items-center justify-center font-bold flex-shrink-0">
                                <?php echo $isCorrect ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>'; ?>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-800 mb-2">
                                    <?php echo ($index + 1); ?>. <?php echo htmlspecialchars($question['question']); ?>
                                </p>
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <div class="<?php echo $question['correct_option'] === 'a' ? 'text-green-600 font-bold' : 'text-gray-600'; ?>">
                                        A. <?php echo htmlspecialchars($question['option_a']); ?>
                                    </div>
                                    <div class="<?php echo $question['correct_option'] === 'b' ? 'text-green-600 font-bold' : 'text-gray-600'; ?>">
                                        B. <?php echo htmlspecialchars($question['option_b']); ?>
                                    </div>
                                    <div class="<?php echo $question['correct_option'] === 'c' ? 'text-green-600 font-bold' : 'text-gray-600'; ?>">
                                        C. <?php echo htmlspecialchars($question['option_c']); ?>
                                    </div>
                                    <div class="<?php echo $question['correct_option'] === 'd' ? 'text-green-600 font-bold' : 'text-gray-600'; ?>">
                                        D. <?php echo htmlspecialchars($question['option_d']); ?>
                                    </div>
                                </div>
                                <?php if (!$isCorrect && $answer): ?>
                                    <p class="text-red-600 text-sm mt-2">
                                        Your answer: <?php echo strtoupper($answer['selected']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Share Card Section -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <h3 class="text-xl font-bold text-[#0038A8] mb-4">Share Your Score</h3>
            <div class="flex flex-col sm:flex-row gap-4">
                <button onclick="downloadShareCard()" class="flex-1 bg-yellow-400 text-[#0038A8] px-6 py-4 rounded-xl font-bold hover:bg-yellow-300 transition">
                    <i class="fas fa-download mr-2"></i> Download Card
                </button>
                <button onclick="shareOnFacebookBtn()" class="flex-1 bg-blue-600 text-white px-6 py-4 rounded-xl font-bold hover:bg-blue-700 transition">
                    <i class="fab fa-facebook mr-2"></i> Share on Facebook
                </button>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4">
            <a href="quiz.php?category=<?php echo $categoryId; ?>&reset=1" 
               class="flex-1 bg-[#0038A8] text-white px-6 py-4 rounded-xl font-bold text-center hover:bg-[#002870] transition">
                <i class="fas fa-redo mr-2"></i> Play Again
            </a>
            <a href="index.php" 
               class="flex-1 bg-gray-200 text-gray-800 px-6 py-4 rounded-xl font-bold text-center hover:bg-gray-300 transition">
                <i class="fas fa-home mr-2"></i> Try Another Category
            </a>
        </div>
    </div>
</main>

<script>
// Animate score counter
function animateScore() {
    const counter = document.getElementById('scoreCounter');
    const target = <?php echo $score; ?>;
    let current = 0;
    const increment = target / 30;
    
    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            counter.textContent = target;
            clearInterval(timer);
        } else {
            counter.textContent = Math.floor(current);
        }
    }, 50);
}

// Download share card
function downloadShareCard() {
    const playerName = '<?php echo isset($_SESSION['saved_player_name']) ? htmlspecialchars($_SESSION['saved_player_name']) : 'Anonymous'; ?>';
    downloadCard(<?php echo $score; ?>, <?php echo $totalQuestions; ?>, '<?php echo htmlspecialchars($categoryName); ?>', playerName);
}

// Share on Facebook
function shareOnFacebookBtn() {
    const playerName = '<?php echo isset($_SESSION['saved_player_name']) ? htmlspecialchars($_SESSION['saved_player_name']) : 'Anonymous'; ?>';
    shareOnFacebook(<?php echo $score; ?>, <?php echo $totalQuestions; ?>, '<?php echo htmlspecialchars($categoryName); ?>', playerName);
}

// Start animation on page load
animateScore();
</script>

<?php require_once 'includes/footer.php'; ?>
