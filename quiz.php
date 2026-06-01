<?php
error_reporting(0);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/includes/functions.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/includes/header.php';

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
    header('Location: quiz.php?category=' . $categoryId);
    exit;
}

// Initialize quiz session (keeping original approach for stability)
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
}

// Check if quiz is complete BEFORE accessing current question
$questions = $_SESSION['quiz_questions'];
$totalQuestions = count($questions);

// Handle case where no questions are available
if ($totalQuestions === 0) {
    require_once __DIR__ . '/includes/header.php';
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
    require_once __DIR__ . '/includes/footer.php';
    exit;
}
$currentScore = $_SESSION['quiz_score'];

if ($_SESSION['quiz_current_index'] >= $totalQuestions) {
    // Quiz is complete - redirect to results
    header('Location: results.php');
    exit;
}

// Quiz is not complete - access current question
$currentQuestion = $questions[$_SESSION['quiz_current_index']];
$currentQuestionNumber = $_SESSION['quiz_current_index'] + 1;

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

        <!-- Question Card -->
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-6">
            <h3 class="text-2xl font-bold text-gray-800 text-center mb-8">
                <?php echo htmlspecialchars($currentQuestion['question']); ?>
            </h3>

            <!-- Answer Buttons -->
            <div class="grid grid-cols-1 gap-4" id="answerButtons">
                <button type="button" onclick="submitAnswer('a')" class="answer-btn bg-gray-100 hover:bg-[#0038A8] hover:text-white p-4 rounded-xl text-left font-medium transition border-2 border-transparent hover:border-[#0038A8]" data-option="a">
                    <span class="inline-block w-8 h-8 bg-[#0038A8] text-white rounded-full text-center leading-8 font-bold mr-3">A</span>
                    <?php echo htmlspecialchars($currentQuestion['option_a']); ?>
                </button>
                <button type="button" onclick="submitAnswer('b')" class="answer-btn bg-gray-100 hover:bg-[#0038A8] hover:text-white p-4 rounded-xl text-left font-medium transition border-2 border-transparent hover:border-[#0038A8]" data-option="b">
                    <span class="inline-block w-8 h-8 bg-[#0038A8] text-white rounded-full text-center leading-8 font-bold mr-3">B</span>
                    <?php echo htmlspecialchars($currentQuestion['option_b']); ?>
                </button>
                <button type="button" onclick="submitAnswer('c')" class="answer-btn bg-gray-100 hover:bg-[#0038A8] hover:text-white p-4 rounded-xl text-left font-medium transition border-2 border-transparent hover:border-[#0038A8]" data-option="c">
                    <span class="inline-block w-8 h-8 bg-[#0038A8] text-white rounded-full text-center leading-8 font-bold mr-3">C</span>
                    <?php echo htmlspecialchars($currentQuestion['option_c']); ?>
                </button>
                <button type="button" onclick="submitAnswer('d')" class="answer-btn bg-gray-100 hover:bg-[#0038A8] hover:text-white p-4 rounded-xl text-left font-medium transition border-2 border-transparent hover:border-[#0038A8]" data-option="d">
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

<script>
const correctOption = '<?php echo strtolower($currentQuestion['correct_option']); ?>';
const questionId = <?php echo $currentQuestion['id']; ?>;
const funFact = <?php echo json_encode($currentQuestion['fun_fact'] ?? ''); ?>;
const categoryId = <?php echo $categoryId; ?>;
let answered = false;

function submitAnswer(selected) {
    if (answered) return;
    answered = true;

    // Disable all buttons
    document.querySelectorAll('.answer-btn').forEach(btn => btn.disabled = true);

    // Show visual feedback immediately
    const isCorrect = selected === correctOption;
    const clickedBtn = document.querySelector('[data-option="' + selected + '"]');
    const correctBtn = document.querySelector('[data-option="' + correctOption + '"]');

    if (isCorrect) {
        clickedBtn.classList.add('bg-green-500', 'text-white');
        clickedBtn.classList.remove('bg-gray-100');
    } else {
        clickedBtn.classList.add('bg-red-500', 'text-white');
        clickedBtn.classList.remove('bg-gray-100');
        correctBtn.classList.add('bg-green-500', 'text-white');
        correctBtn.classList.remove('bg-gray-100');
    }

    // Send to API via AJAX
    fetch('/api/save_answer.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            selected_option: selected,
            correct_option: correctOption,
            question_id: questionId,
            fun_fact: funFact,
            category_id: categoryId
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Show fun fact overlay
            showFunFact(data.is_correct, data.fun_fact, data.redirect);
        } else {
            alert('Error: ' + (data.error || 'Unknown error'));
            answered = false;
        }
    })
    .catch(err => {
        console.error('Error:', err);
        answered = false;
    });
}

function showFunFact(isCorrect, funFact, redirectUrl) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    modal.innerHTML = `
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full mx-4 p-8">
            <div class="text-center mb-6">
                <div class="w-20 h-20 bg-gradient-to-br ${isCorrect ? 'from-green-400 to-green-600' : 'from-red-400 to-red-600'} rounded-full mx-auto mb-4 flex items-center justify-center">
                    <i class="fas ${isCorrect ? 'fa-check' : 'fa-times'} text-white text-3xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-blue-800 mb-2">${isCorrect ? 'Tama!' : 'Mali!'}</h3>
            </div>
            <p class="text-gray-700 text-center mb-6 text-lg">${funFact || ''}</p>
            <button onclick="window.location.href='${redirectUrl}'" class="block w-full bg-blue-800 text-white py-4 rounded-xl font-bold text-lg hover:bg-blue-900 transition text-center">
                ${redirectUrl.includes('show_result') ? 'See My Results 🏆' : 'Next Question →'}
            </button>
        </div>
    `;
    document.body.appendChild(modal);
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
