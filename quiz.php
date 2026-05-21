<?php
session_start();
require_once 'includes/functions.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Handle POST request to update session
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'answer') {
    $selected = $_POST['selected_option'];
    $correct = $_POST['correct_option'];
    $questionId = (int)$_POST['question_id'];
    $funFact = $_POST['fun_fact'] ?? '';
    $isCorrect = $selected === $correct;

    if ($isCorrect) {
        $_SESSION['quiz_score']++;
    }

    $_SESSION['quiz_answers'][$questionId] = [
        'question_id' => $questionId,
        'selected' => $selected,
        'correct' => $correct
    ];

    $_SESSION['quiz_current_index']++;

    // Store answer for fun fact display
    $_SESSION['last_answer'] = [
        'selected' => $selected,
        'correct' => $correct,
        'is_correct' => $isCorrect,
        'fun_fact' => $funFact
    ];

    // Check if this was the last question
    $totalQuestions = count($_SESSION['quiz_questions']);
    if ($_SESSION['quiz_current_index'] >= $totalQuestions) {
        // Last question - redirect to show fun fact before results
        header('Location: quiz.php?category=' . $_POST['category_id'] . '&show_result=1');
    } else {
        // Not last question - redirect to next question
        header('Location: quiz.php?category=' . $_POST['category_id']);
    }
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
    unset($_SESSION['score_saved']);
    unset($_SESSION['saved_player_name']);
    unset($_SESSION['last_answer']);
    header('Location: quiz.php?category=' . $categoryId);
    exit;
}

// Initialize quiz session
if (!isset($_SESSION['quiz_started']) || (int)$_SESSION['quiz_category_id'] !== $categoryId) {
    $_SESSION['quiz_started'] = true;
    $_SESSION['quiz_category_id'] = $categoryId;
    $_SESSION['quiz_questions'] = getQuestions($categoryId, 10);
    $_SESSION['quiz_current_index'] = 0;
    $_SESSION['quiz_score'] = 0;
    $_SESSION['quiz_answers'] = [];
    $_SESSION['quiz_start_time'] = time();
    unset($_SESSION['score_saved']);
    unset($_SESSION['saved_player_name']);
    unset($_SESSION['last_answer']);
}

// Check if quiz is complete BEFORE accessing current question
$questions = $_SESSION['quiz_questions'];
$totalQuestions = count($questions);

// Handle case where no questions are available
if ($totalQuestions === 0) {
    require_once 'includes/header.php';
    ?>
    <main class="min-h-screen bg-gray-50 py-8 px-4">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
                <i class="fas fa-exclamation-triangle text-5xl text-yellow-500 mb-4"></i>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">No Questions Available</h2>
                <p class="text-gray-600 mb-6">There are no questions available for this category yet.</p>
                <a href="piliin.php" class="inline-block bg-[#0038A8] text-white px-6 py-3 rounded-xl font-bold hover:bg-[#002870] transition">
                    Choose Another Category
                </a>
            </div>
        </div>
    </main>
    <?php
    require_once 'includes/footer.php';
    exit;
}
$currentScore = $_SESSION['quiz_score'];

if ($_SESSION['quiz_current_index'] >= $totalQuestions) {
    // Quiz is complete
    if (isset($_GET['show_result'])) {
        // If last_answer is not set (user refreshed), redirect to results
        if (!isset($_SESSION['last_answer'])) {
            header('Location: results.php');
            exit;
        }
        // Show fun fact modal then See My Results button
        // Don't access current question - use placeholder values for display
        $currentQuestionNumber = $totalQuestions;
        $currentQuestion = null;
    } else {
        // Redirect to results page
        header('Location: results.php');
        exit;
    }
} else {
    // Quiz is not complete - access current question
    $currentQuestion = $questions[$_SESSION['quiz_current_index']];
    $currentQuestionNumber = $_SESSION['quiz_current_index'] + 1;
}

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

        <!-- Question Card (hide when showing result modal) -->
        <?php if (!isset($_GET['show_result'])): ?>
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-6">
            <h3 class="text-2xl font-bold text-gray-800 text-center mb-8">
                <?php echo htmlspecialchars($currentQuestion['question']); ?>
            </h3>

            <!-- Answer Buttons -->
            <form method="POST" action="quiz.php?category=<?php echo $categoryId; ?>">
                <input type="hidden" name="action" value="answer">
                <input type="hidden" name="question_id" value="<?php echo $currentQuestion['id']; ?>">
                <input type="hidden" name="correct_option" value="<?php echo strtolower($currentQuestion['correct_option']); ?>">
                <input type="hidden" name="fun_fact" value="<?php echo htmlspecialchars($currentQuestion['fun_fact'] ?? ''); ?>">
                <input type="hidden" name="category_id" value="<?php echo $categoryId; ?>">
                <div class="grid grid-cols-1 gap-4" id="answerButtons">
                    <button type="submit" name="selected_option" value="a" class="answer-btn bg-gray-100 hover:bg-[#0038A8] hover:text-white p-4 rounded-xl text-left font-medium transition transform hover:scale-102 border-2 border-transparent hover:border-[#0038A8]" data-option="a">
                        <span class="inline-block w-8 h-8 bg-[#0038A8] text-white rounded-full text-center leading-8 font-bold mr-3">A</span>
                        <?php echo htmlspecialchars($currentQuestion['option_a']); ?>
                    </button>
                    <button type="submit" name="selected_option" value="b" class="answer-btn bg-gray-100 hover:bg-[#0038A8] hover:text-white p-4 rounded-xl text-left font-medium transition transform hover:scale-102 border-2 border-transparent hover:border-[#0038A8]" data-option="b">
                        <span class="inline-block w-8 h-8 bg-[#0038A8] text-white rounded-full text-center leading-8 font-bold mr-3">B</span>
                        <?php echo htmlspecialchars($currentQuestion['option_b']); ?>
                    </button>
                    <button type="submit" name="selected_option" value="c" class="answer-btn bg-gray-100 hover:bg-[#0038A8] hover:text-white p-4 rounded-xl text-left font-medium transition transform hover:scale-102 border-2 border-transparent hover:border-[#0038A8]" data-option="c">
                        <span class="inline-block w-8 h-8 bg-[#0038A8] text-white rounded-full text-center leading-8 font-bold mr-3">C</span>
                        <?php echo htmlspecialchars($currentQuestion['option_c']); ?>
                    </button>
                    <button type="submit" name="selected_option" value="d" class="answer-btn bg-gray-100 hover:bg-[#0038A8] hover:text-white p-4 rounded-xl text-left font-medium transition transform hover:scale-102 border-2 border-transparent hover:border-[#0038A8]" data-option="d">
                        <span class="inline-block w-8 h-8 bg-[#0038A8] text-white rounded-full text-center leading-8 font-bold mr-3">D</span>
                        <?php echo htmlspecialchars($currentQuestion['option_d']); ?>
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- Progress Bar (hide when showing result modal) -->
        <?php if (!isset($_GET['show_result'])): ?>
        <div class="bg-white rounded-2xl shadow-lg p-4">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm text-gray-600">Progress</span>
                <span class="text-sm font-medium text-[#0038A8]"><?php echo $currentQuestionNumber; ?>/<?php echo $totalQuestions; ?></span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-[#0038A8] h-2 rounded-full transition-all duration-500" style="width: <?php echo ($currentQuestionNumber / $totalQuestions) * 100; ?>%"></div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</main>

<!-- Quiz Data for JavaScript (only when quiz is active) -->
<?php if ($currentQuestion !== null): ?>
<div id="quizData"
     data-score="<?php echo $currentScore; ?>"
     data-correct-answer="<?php echo strtolower($currentQuestion['correct_option']); ?>"
     data-question-index="<?php echo $_SESSION['quiz_current_index']; ?>"
     data-question-id="<?php echo $currentQuestion['id']; ?>"
     style="display: none;">
</div>
<?php endif; ?>

<!-- Fun Fact Modal -->
<?php if (isset($_SESSION['last_answer'])): ?>
    <?php $lastAnswer = $_SESSION['last_answer']; ?>
    <?php $isLastQuestion = isset($_GET['show_result']) && $_GET['show_result'] === '1'; ?>
    <div id="funFactModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full mx-4 p-8 pop-in">
            <div class="text-center mb-6">
                <div class="w-20 h-20 bg-gradient-to-br <?php echo $lastAnswer['is_correct'] ? 'from-green-400 to-green-600' : 'from-red-400 to-red-600'; ?> rounded-full mx-auto mb-4 flex items-center justify-center">
                    <i class="fas <?php echo $lastAnswer['is_correct'] ? 'fa-check' : 'fa-times'; ?> text-white text-3xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-[#0038A8] mb-2"><?php echo $lastAnswer['is_correct'] ? 'Correct!' : 'Wrong!'; ?></h3>
            </div>
            <p id="funFactText" class="text-gray-700 text-center mb-6 text-lg">
                <?php echo htmlspecialchars($lastAnswer['fun_fact']); ?>
            </p>
            <?php if ($isLastQuestion): ?>
                <a href="results.php" class="block w-full bg-[#0038A8] text-white py-4 rounded-xl font-bold text-lg hover:bg-[#002870] transition text-center">
                    See My Results <i class="fas fa-trophy ml-2"></i>
                </a>
            <?php else: ?>
                <a href="quiz.php?category=<?php echo $categoryId; ?>" class="block w-full bg-[#0038A8] text-white py-4 rounded-xl font-bold text-lg hover:bg-[#002870] transition text-center">
                    Next Question <i class="fas fa-arrow-right ml-2"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php unset($_SESSION['last_answer']); ?>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
