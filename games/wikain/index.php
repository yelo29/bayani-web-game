<?php
error_reporting(0);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/header.php';

// Load data files
$words = json_decode(file_get_contents(__DIR__ . '/data/words.json'), true);
$sentences = json_decode(file_get_contents(__DIR__ . '/data/sentences.json'), true);

// Filter sentences into regular sentences only (grammar mode removed due to data issues)
$regularSentences = array_filter($sentences, function($s) {
    return !isset($s['sentence_with_error']);
});

// Re-index array
$regularSentences = array_values($regularSentences);
?>

<main class="min-h-screen bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 py-8 px-4">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl md:text-5xl font-bold font-serif text-white mb-4">Wikain Quiz</h1>
            <p class="text-xl text-white/80 mb-2">Pag-aaral ng Wikang Filipino</p>
            <p class="text-white/60">Pumili ng mode upang maglaro</p>
        </div>

        <!-- Mode Selection -->
        <div id="mode-selection" class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 max-w-3xl mx-auto">
            <!-- Word Quiz -->
            <div class="bg-white rounded-3xl shadow-2xl p-6 transform hover:scale-105 transition-transform duration-300 cursor-pointer" onclick="setMode('word')">
                <div class="text-center mb-4">
                    <div class="w-20 h-20 bg-blue-100 rounded-full mx-auto mb-3 flex items-center justify-center">
                        <i class="fas fa-font text-blue-600 text-4xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-blue-600 mb-2">Salita</h2>
                    <p class="text-gray-600 text-sm">Word Quiz</p>
                </div>
                <div class="mb-4">
                    <div class="flex flex-wrap gap-2 justify-center">
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">English Translation</span>
                        <span class="px-2 py-1 bg-indigo-100 text-indigo-800 rounded-full text-xs">Multiple Choice</span>
                    </div>
                </div>
                <button class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 transition">
                    <i class="fas fa-play mr-2"></i> Maglaro
                </button>
            </div>

            <!-- Sentence Quiz -->
            <div class="bg-white rounded-3xl shadow-2xl p-6 transform hover:scale-105 transition-transform duration-300 cursor-pointer" onclick="setMode('sentence')">
                <div class="text-center mb-4">
                    <div class="w-20 h-20 bg-purple-100 rounded-full mx-auto mb-3 flex items-center justify-center">
                        <i class="fas fa-align-left text-purple-600 text-4xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-purple-600 mb-2">Pangungusap</h2>
                    <p class="text-gray-600 text-sm">Sentence Quiz</p>
                </div>
                <div class="mb-4">
                    <div class="flex flex-wrap gap-2 justify-center">
                        <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs">English Translation</span>
                        <span class="px-2 py-1 bg-pink-100 text-pink-800 rounded-full text-xs">Multiple Choice</span>
                    </div>
                </div>
                <button class="w-full bg-purple-600 text-white py-3 rounded-xl font-bold hover:bg-purple-700 transition">
                    <i class="fas fa-play mr-2"></i> Maglaro
                </button>
            </div>
        </div>

        <!-- Back Button -->
        <div class="text-center">
            <a href="../../maglaro.php" class="inline-block bg-white/20 text-white px-8 py-3 rounded-xl font-bold hover:bg-white/30 transition">
                <i class="fas fa-arrow-left mr-2"></i> Bumalik
            </a>
        </div>

        <!-- Quiz Area (Hidden by default) -->
        <div id="quiz-area" class="hidden">
            <div class="bg-white rounded-3xl shadow-2xl p-6 md:p-8">
                <!-- Quiz Header -->
                <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                    <h2 id="quiz-title" class="text-xl md:text-2xl font-bold text-blue-600">Quiz</h2>
                    <button onclick="backToMenu()" class="text-gray-600 hover:text-gray-800">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>

                <!-- Progress Bar -->
                <div class="mb-6">
                    <div class="flex justify-between text-sm text-gray-600 mb-2">
                        <span id="progress-text">Tanong 1/10</span>
                        <span id="score-text">Puntos: 0</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div id="progress-bar" class="bg-blue-600 h-3 rounded-full transition-all duration-300" style="width: 10%"></div>
                    </div>
                </div>

                <!-- Question Card -->
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 md:p-6 mb-6 rounded-lg">
                    <p id="question-text" class="text-lg md:text-xl font-medium text-gray-800 text-center"></p>
                </div>

                <!-- Options Grid -->
                <div id="options-grid" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6"></div>

                <!-- Feedback -->
                <div id="feedback" class="hidden text-center p-4 rounded-lg mb-4">
                    <p id="feedback-text" class="text-lg font-bold"></p>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
// Data from PHP
const wordsData = <?php echo json_encode($words ?? []); ?>;
const sentencesData = <?php echo json_encode($regularSentences ?? []); ?>;

// Game state
let currentMode = '';
let questions = [];
let currentQuestionIndex = 0;
let score = 0;
let totalQuestions = 10;
let isAnswering = false;

// Set mode and start quiz
function setMode(mode) {
    currentMode = mode;

    // Load questions based on mode
    if (mode === 'word') {
        questions = shuffleArray(wordsData.map(w => ({
            question: w.word,
            correct: w.english,
            options: generateOptions(w.english, wordsData.map(w => w.english))
        })));
        document.getElementById('quiz-title').textContent = 'Salita Quiz';
    } else if (mode === 'sentence') {
        questions = shuffleArray(sentencesData.map(s => ({
            question: s.sentence,
            correct: s.english,
            options: generateOptions(s.english, sentencesData.map(s => s.english))
        })));
        document.getElementById('quiz-title').textContent = 'Pangungusap Quiz';
    }

    // Limit to 10 questions
    questions = questions.slice(0, 10);
    totalQuestions = questions.length;

    // Reset game state
    currentQuestionIndex = 0;
    score = 0;
    isAnswering = false;

    // Show quiz area
    document.getElementById('mode-selection').classList.add('hidden');
    document.getElementById('quiz-area').classList.remove('hidden');

    // Load first question
    loadQuestion();
}

// Generate options (1 correct + 3 wrong)
function generateOptions(correct, allOptions) {
    // Filter out correct answer from wrong options
    const wrongOptions = allOptions.filter(o => o !== correct);
    
    // Shuffle wrong options and take 3
    const shuffledWrong = shuffleArray(wrongOptions).slice(0, 3);
    
    // Add correct and shuffle all
    const all = shuffleArray([correct, ...shuffledWrong]);
    
    return all;
}

// Load current question
function loadQuestion() {
    const question = questions[currentQuestionIndex];
    
    // Update progress
    document.getElementById('progress-text').textContent = `Tanong ${currentQuestionIndex + 1}/${totalQuestions}`;
    document.getElementById('score-text').textContent = `Puntos: ${score}`;
    document.getElementById('progress-bar').style.width = `${((currentQuestionIndex + 1) / totalQuestions) * 100}%`;
    
    // Update question
    document.getElementById('question-text').textContent = question.question;
    
    // Generate options
    const optionsGrid = document.getElementById('options-grid');
    optionsGrid.innerHTML = '';
    
    question.options.forEach((option, index) => {
        const button = document.createElement('button');
        button.className = 'bg-white border-2 border-gray-200 hover:border-blue-400 p-4 rounded-xl text-left transition font-medium text-gray-800 hover:bg-blue-50';
        button.textContent = option;
        button.onclick = () => checkAnswer(option, question.correct, button);
        optionsGrid.appendChild(button);
    });

    // Hide feedback
    document.getElementById('feedback').classList.add('hidden');
    isAnswering = false;
}

// Check answer
function checkAnswer(selected, correct, button) {
    if (isAnswering) return;
    isAnswering = true;

    const isCorrect = selected === correct;
    const feedback = document.getElementById('feedback');
    const feedbackText = document.getElementById('feedback-text');

    // Disable all buttons
    const allButtons = document.querySelectorAll('#options-grid button');
    allButtons.forEach(btn => {
        btn.disabled = true;
        btn.classList.remove('hover:border-blue-400', 'hover:bg-blue-50');
    });

    // Show feedback
    feedback.classList.remove('hidden');
    
    if (isCorrect) {
        score++;
        button.classList.remove('border-gray-200');
        button.classList.add('border-green-500', 'bg-green-100');
        feedback.className = 'text-center p-4 rounded-lg mb-4 bg-green-100 border-2 border-green-500';
        feedbackText.textContent = 'Tama! ✓';
        feedbackText.className = 'text-lg font-bold text-green-700';
    } else {
        button.classList.remove('border-gray-200');
        button.classList.add('border-red-500', 'bg-red-100');
        feedback.className = 'text-center p-4 rounded-lg mb-4 bg-red-100 border-2 border-red-500';
        feedbackText.textContent = 'Mali! ✗';
        feedbackText.className = 'text-lg font-bold text-red-700';

        // Highlight correct answer
        allButtons.forEach(btn => {
            if (btn.textContent === correct) {
                btn.classList.remove('border-gray-200');
                btn.classList.add('border-green-500', 'bg-green-100');
            }
        });
    }

    // Update score display
    document.getElementById('score-text').textContent = `Puntos: ${score}`;

    // Wait 1.5 seconds then next question
    setTimeout(() => {
        currentQuestionIndex++;
        if (currentQuestionIndex < totalQuestions) {
            loadQuestion();
        } else {
            showResults();
        }
    }, 1500);
}

// Show results (Buhay pattern - alert and reload)
function showResults() {
    // Calculate XP and coins (Buhay pattern)
    const xp = Math.floor((score / totalQuestions) * 50);
    const coins = Math.floor((score / totalQuestions) * 30);

    // Save score to API (Buhay pattern)
    saveScoreToAPI(xp, coins);
}

// Save score to API (Buhay pattern)
function saveScoreToAPI(xp, coins) {
    fetch('../../api/save_game_score.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            game_type: 'wikain-' + currentMode,
            score: score,
            total: totalQuestions,
            xp: xp,
            coins: coins
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Score: ${score}/${totalQuestions}\nXP: +${xp}\nCoins: +${coins}`);
            location.reload();
        } else {
            alert('Error saving score: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(err => {
        alert('Error: ' + err.message);
    });
}

// Back to menu
function backToMenu() {
    document.getElementById('mode-selection').classList.remove('hidden');
    document.getElementById('quiz-area').classList.add('hidden');
}

// Shuffle array (Fisher-Yates)
function shuffleArray(array) {
    const newArray = [...array];
    for (let i = newArray.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [newArray[i], newArray[j]] = [newArray[j], newArray[i]];
    }
    return newArray;
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
