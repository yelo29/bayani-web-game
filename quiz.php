<?php
session_start();
require_once 'includes/functions.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Handle POST request to update session
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['quiz_score'] = (int)$_POST['current_score'];
    $_SESSION['quiz_current_index'] = (int)$_POST['current_index'];
    $_SESSION['quiz_answers'][] = [
        'question_id' => (int)$_POST['question_id'],
        'selected' => $_POST['selected_option'] ?? 'timeout',
        'correct' => $_POST['correct_option']
    ];
    echo json_encode(['success' => true]);
    exit;
}

require_once 'includes/header.php';

// Check if category is selected
if (!isset($_GET['category'])) {
    header('Location: index.php');
    exit;
}

$categoryId = (int)$_GET['category'];

// Check if reset is requested
if (isset($_GET['reset'])) {
    unset($_SESSION['quiz_started']);
    unset($_SESSION['quiz_category_id']);
    unset($_SESSION['quiz_questions']);
    unset($_SESSION['quiz_current_index']);
    unset($_SESSION['quiz_score']);
    unset($_SESSION['quiz_answers']);
    unset($_SESSION['quiz_start_time']);
    header('Location: quiz.php?category=' . $categoryId);
    exit;
}

// Initialize quiz session
if (!isset($_SESSION['quiz_started']) || $_SESSION['quiz_category_id'] !== $categoryId) {
    $_SESSION['quiz_started'] = true;
    $_SESSION['quiz_category_id'] = $categoryId;
    $_SESSION['quiz_questions'] = getQuestions($categoryId, 10);
    $_SESSION['quiz_current_index'] = 0;
    $_SESSION['quiz_score'] = 0;
    $_SESSION['quiz_answers'] = [];
    $_SESSION['quiz_start_time'] = time();
}

// Check if quiz is complete
if ($_SESSION['quiz_current_index'] >= count($_SESSION['quiz_questions'])) {
    header('Location: results.php');
    exit;
}

// Get current question
$questions = $_SESSION['quiz_questions'];
$currentQuestion = $questions[$_SESSION['quiz_current_index']];
$currentQuestionNumber = $_SESSION['quiz_current_index'] + 1;
$totalQuestions = count($questions);
$currentScore = $_SESSION['quiz_score'];

// Get category name
$categories = getCategories();
$categoryName = '';
foreach ($categories as $cat) {
    if ($cat['id'] === $categoryId) {
        $categoryName = $cat['name'];
        break;
    }
}
?>

<main class="min-h-screen bg-gray-50 py-8 px-4">
    <div class="max-w-3xl mx-auto">
        <!-- Top Bar -->
        <div class="bg-white rounded-2xl shadow-lg p-4 mb-6 flex justify-between items-center">
            <div>
                <span class="text-sm text-gray-500"><?php echo htmlspecialchars($categoryName); ?></span>
                <h2 class="text-lg font-bold text-[#0038A8]">
                    Question <?php echo $currentQuestionNumber; ?> of <?php echo $totalQuestions; ?>
                </h2>
            </div>
            <div class="text-right">
                <span class="text-sm text-gray-500">Score</span>
                <p class="text-2xl font-bold text-[#0038A8]"><?php echo $currentScore; ?></p>
            </div>
        </div>

        <!-- Countdown Timer -->
        <div class="bg-white rounded-2xl shadow-lg p-4 mb-6">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-600">Time Remaining</span>
                <span id="timerText" class="text-2xl font-bold text-[#0038A8]">30</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div id="timerBar" class="bg-green-500 h-3 rounded-full transition-all duration-1000" style="width: 100%"></div>
            </div>
        </div>

        <!-- Question Card -->
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-6">
            <h3 class="text-2xl font-bold text-gray-800 text-center mb-8">
                <?php echo htmlspecialchars($currentQuestion['question']); ?>
            </h3>

            <!-- Answer Buttons -->
            <div class="grid grid-cols-1 gap-4" id="answerButtons">
                <button onclick="selectAnswer('a')" class="answer-btn bg-gray-100 hover:bg-[#0038A8] hover:text-white p-4 rounded-xl text-left font-medium transition transform hover:scale-102 border-2 border-transparent hover:border-[#0038A8]" data-option="a">
                    <span class="inline-block w-8 h-8 bg-[#0038A8] text-white rounded-full text-center leading-8 font-bold mr-3">A</span>
                    <?php echo htmlspecialchars($currentQuestion['option_a']); ?>
                </button>
                <button onclick="selectAnswer('b')" class="answer-btn bg-gray-100 hover:bg-[#0038A8] hover:text-white p-4 rounded-xl text-left font-medium transition transform hover:scale-102 border-2 border-transparent hover:border-[#0038A8]" data-option="b">
                    <span class="inline-block w-8 h-8 bg-[#0038A8] text-white rounded-full text-center leading-8 font-bold mr-3">B</span>
                    <?php echo htmlspecialchars($currentQuestion['option_b']); ?>
                </button>
                <button onclick="selectAnswer('c')" class="answer-btn bg-gray-100 hover:bg-[#0038A8] hover:text-white p-4 rounded-xl text-left font-medium transition transform hover:scale-102 border-2 border-transparent hover:border-[#0038A8]" data-option="c">
                    <span class="inline-block w-8 h-8 bg-[#0038A8] text-white rounded-full text-center leading-8 font-bold mr-3">C</span>
                    <?php echo htmlspecialchars($currentQuestion['option_c']); ?>
                </button>
                <button onclick="selectAnswer('d')" class="answer-btn bg-gray-100 hover:bg-[#0038A8] hover:text-white p-4 rounded-xl text-left font-medium transition transform hover:scale-102 border-2 border-transparent hover:border-[#0038A8]" data-option="d">
                    <span class="inline-block w-8 h-8 bg-[#0038A8] text-white rounded-full text-center leading-8 font-bold mr-3">D</span>
                    <?php echo htmlspecialchars($currentQuestion['option_d']); ?>
                </button>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="bg-white rounded-2xl shadow-lg p-4">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm text-gray-600">Progress</span>
                <span class="text-sm font-medium text-[#0038A8]"><?php echo $currentQuestionNumber; ?>/<?php echo $totalQuestions; ?></span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-[#0038A8] h-2 rounded-full transition-all duration-500" style="width: <?php echo ($currentQuestionNumber / $totalQuestions) * 100; ?>%"></div>
            </div>
        </div>
    </div>
</main>

<!-- Quiz Data for JavaScript -->
<div id="quizData" 
     data-score="<?php echo $currentScore; ?>" 
     data-correct-answer="<?php echo $currentQuestion['correct_option']; ?>" 
     data-question-index="<?php echo $_SESSION['quiz_current_index']; ?>"
     data-question-id="<?php echo $currentQuestion['id']; ?>"
     style="display: none;">
</div>

<!-- Fun Fact Modal -->
<div id="funFactModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full mx-4 p-8 pop-in">
        <div class="text-center mb-6">
            <div class="w-20 h-20 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-full mx-auto mb-4 flex items-center justify-center">
                <i class="fas fa-lightbulb text-white text-3xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-[#0038A8] mb-2">Did You Know?</h3>
        </div>
        <p id="funFactText" class="text-gray-700 text-center mb-6 text-lg">
            <?php echo htmlspecialchars($currentQuestion['fun_fact']); ?>
        </p>
        <button onclick="nextQuestion()" class="w-full bg-[#0038A8] text-white py-4 rounded-xl font-bold text-lg hover:bg-[#002870] transition">
            Next Question <i class="fas fa-arrow-right ml-2"></i>
        </button>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
