<?php
/**
 * Handa - Disaster Preparedness Learning Games
 * For Grades 3-6 Filipino Students
 * 
 * Games:
 * 1. Itugma - Matching situations with correct actions (wrong matches are permanent)
 * 2. Tama o Mali - True/False quiz on disaster readiness
 * XP and Coins reward system integrated
 */

error_reporting(0);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/header.php';

// Load data files
$challenges = json_decode(file_get_contents(__DIR__ . '/data/challenges.json'), true);
$quizQuestions = json_decode(file_get_contents(__DIR__ . '/data/quiz.json'), true);

// Get current mode from URL
$mode = $_GET['mode'] ?? 'menu';
?>

<main class="min-h-screen bg-gradient-to-br from-orange-600 via-red-600 to-yellow-700 py-8 px-4">
    <div class="max-w-6xl mx-auto">
        <!-- Mode Selection Menu -->
        <?php if ($mode === 'menu'): ?>
        <div class="text-center mb-8">
            <h1 class="text-4xl md:text-5xl font-bold font-serif text-white mb-4">Handa Pilipinas</h1>
            <p class="text-xl text-white/80 mb-2"><?php echo t('health_safety_edu'); ?> - Grades 3-6</p>
            <p class="text-white/60"><?php echo t('learn_disaster_preparedness'); ?></p>
            <!-- Aralin/Wiki Button -->
            <div class="mt-6 text-center">
                <button onclick="location.href='?mode=aralin'" class="inline-block bg-purple-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-purple-700 transition shadow-lg">
                    <i class="fas fa-book-open mr-2"></i> <?php echo t('lesson_wiki'); ?>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
            <!-- Game 1: Matching Game -->
            <div class="bg-white rounded-3xl shadow-2xl p-6 transform hover:scale-105 transition-transform duration-300 cursor-pointer" onclick="location.href='?mode=laro'">
                <div class="text-center mb-4">
                    <div class="w-24 h-24 bg-orange-100 rounded-full mx-auto mb-3 flex items-center justify-center">
                        <i class="fas fa-hand-peace text-orange-600 text-5xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-orange-600 mb-2">Laro 1: <?php echo t('match'); ?></h2>
                    <p class="text-gray-600 text-sm"><?php echo t('match_situation_action'); ?></p>
                </div>
                <div class="mb-4">
                    <div class="flex flex-wrap gap-2 justify-center">
                        <span class="px-2 py-1 bg-orange-100 text-orange-800 rounded-full text-xs">8 Pares</span>
                        <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">Sakuna</span>
                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Matching</span>
                    </div>
                </div>
                <button class="w-full bg-orange-600 text-white py-3 rounded-xl font-bold hover:bg-orange-700 transition">
                    <i class="fas fa-play mr-2"></i> <?php echo t('play'); ?>
                </button>
            </div>

            <!-- Game 2: Tama o Mali Quiz -->
            <div class="bg-white rounded-3xl shadow-2xl p-6 transform hover:scale-105 transition-transform duration-300 cursor-pointer" onclick="location.href='?mode=quiz'">
                <div class="text-center mb-4">
                    <div class="w-24 h-24 bg-blue-100 rounded-full mx-auto mb-3 flex items-center justify-center">
                        <i class="fas fa-question-circle text-blue-600 text-5xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-blue-600 mb-2">Laro 2: <?php echo t('true_false'); ?></h2>
                    <p class="text-gray-600 text-sm"><?php echo t('check_statement'); ?></p>
                </div>
                <div class="mb-4">
                    <div class="flex flex-wrap gap-2 justify-center">
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs"><?php echo t('10_questions'); ?></span>
                        <span class="px-2 py-1 bg-teal-100 text-teal-800 rounded-full text-xs"><?php echo t('true_false_quiz'); ?></span>
                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs"><?php echo t('true_false'); ?></span>
                    </div>
                </div>
                <button class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 transition">
                    <i class="fas fa-play mr-2"></i> <?php echo t('play'); ?>
                </button>
            </div>
        </div>

        <!-- Back Button -->
        <div class="mt-8 text-center">
            <a href="../../maglaro.php" class="inline-block bg-white/20 text-white px-8 py-3 rounded-xl font-bold hover:bg-white/30 transition">
                <i class="fas fa-arrow-left mr-2"></i> <?php echo t('back'); ?>
            </a>
        </div>

        <?php elseif ($mode === 'laro'): ?>
        <!-- Game Mode 1: Matching Game (Itugma) - No reset, wrong matches are permanent -->
        <div class="bg-white rounded-3xl shadow-2xl p-4 md:p-6">
            <div class="flex flex-col md:flex-row justify-between items-center mb-4 md:mb-6 gap-4">
                <h2 class="text-xl md:text-2xl font-bold text-orange-600"><?php echo t('match'); ?> - <?php echo t('situation'); ?> <?php echo t('action'); ?></h2>
                <a href="?mode=menu" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-times text-2xl"></i>
                </a>
            </div>

            <div class="bg-orange-50 border-l-4 border-orange-500 p-3 mb-4 rounded">
                <p class="text-sm text-orange-800"><i class="fas fa-info-circle mr-2"></i><strong><?php echo t('instructions'); ?>:</strong> <?php echo t('tap'); ?> ang <?php echo t('situation'); ?> (kaliwang kahon), pagkatapos <?php echo t('tap'); ?> ang <?php echo t('correct_action'); ?> (kanang kahon). Kapag mali ang napili mo, hindi mo na ito mababago – kaya mag-isip muna bago pumili! Magpatuloy hanggang matapos ang lahat ng pares, pagkatapos <?php echo t('submit'); ?> ang iyong <?php echo t('points'); ?>.</p>
            </div>

            <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
                <div class="bg-gray-100 rounded-xl px-6 py-3">
                    <span class="text-gray-700 font-bold"><?php echo t('points'); ?>: </span>
                    <span id="score" class="text-3xl font-bold text-orange-600">0</span>
                    <span class="text-gray-500"> / <?php echo count($challenges); ?></span>
                </div>
                <button id="submitBtn" onclick="submitGameScore()" disabled class="bg-green-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-check-circle mr-2"></i> <?php echo t('submit'); ?> <?php echo t('points'); ?>
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-gray-50 rounded-2xl p-4">
                    <h3 class="font-bold text-gray-800 mb-4 text-center text-lg border-b pb-2">
                        <i class="fas fa-exclamation-triangle text-orange-500 mr-2"></i> <?php echo strtoupper(t('situation')); ?>
                    </h3>
                    <div id="situationsContainer" class="space-y-3 max-h-[600px] overflow-y-auto pr-2"></div>
                </div>
                <div class="bg-gray-50 rounded-2xl p-4">
                    <h3 class="font-bold text-gray-800 mb-4 text-center text-lg border-b pb-2">
                        <i class="fas fa-shield-alt text-green-600 mr-2"></i> <?php echo strtoupper(t('correct_action')); ?>
                    </h3>
                    <div id="actionsContainer" class="space-y-3 max-h-[600px] overflow-y-auto pr-2"></div>
                </div>
            </div>

            <!-- No reset button - students cannot cheat by restarting mid-round -->
        </div>

        <script>
            const challenges = <?php echo json_encode($challenges); ?>;
            const totalItems = challenges.length;
            let currentScore = 0;
            // matchedSituations[i] = true if used (either correct or wrong)
            // matchedActions[i] = true if used (either correct or wrong)
            let matchedSituations = new Array(totalItems).fill(false);
            let matchedActions = new Array(totalItems).fill(false);
            // track which action index was matched to which situation index (for wrong matches)
            let matchedPairs = new Array(totalItems).fill(null); // situationIndex -> actionIndex (or -1 if wrong)
            let selectedSituationIndex = null;
            let selectedActionIndex = null;
            let isProcessing = false;
            let isSubmittingFlag = false;
            let actionOrder = [];

            function shuffleArray(arr) {
                for (let i = arr.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));
                    [arr[i], arr[j]] = [arr[j], arr[i]];
                }
                return arr;
            }

            function initActionOrder() {
                actionOrder = [];
                for (let i = 0; i < totalItems; i++) actionOrder.push(i);
                actionOrder = shuffleArray([...actionOrder]);
            }

            function renderGame() {
                const situationsContainer = document.getElementById('situationsContainer');
                const actionsContainer = document.getElementById('actionsContainer');
                if (!situationsContainer || !actionsContainer) return;

                // Render situations (only those not yet matched)
                situationsContainer.innerHTML = '';
                for (let i = 0; i < totalItems; i++) {
                    if (!matchedSituations[i]) {
                        const card = document.createElement('div');
                        // If this situation has been incorrectly matched (should not happen because matchedSituations[i] would be true), but just in case:
                        card.className = `situation-card bg-white rounded-xl p-3 shadow-md border-2 transition-all cursor-pointer hover:shadow-lg ${selectedSituationIndex === i ? 'ring-4 ring-orange-500 border-orange-500 scale-105' : 'border-gray-200 hover:border-orange-300'}`;
                        card.setAttribute('data-situation-index', i);
                        card.innerHTML = `<div class="flex items-start gap-2"><div class="text-2xl">⚠️</div><div class="flex-1"><p class="text-gray-800 text-sm md:text-base font-medium">${escapeHtml(challenges[i].situation)}</p><p class="text-xs text-gray-400 mt-1"><i class="fas fa-lightbulb"></i> <?php echo t('tap'); ?> <?php echo t('tap_to_select'); ?></p></div></div>`;
                        card.addEventListener('click', (e) => { e.stopPropagation(); if (!isProcessing) handleSituationClick(i); });
                        situationsContainer.appendChild(card);
                    }
                }
                if (situationsContainer.children.length === 0) {
                    situationsContainer.innerHTML = '<div class="text-center text-green-600 py-8 bg-green-50 rounded-xl"><i class="fas fa-trophy text-4xl mb-2"></i><p><?php echo t('all_pairs_matched'); ?></p></div>';
                }

                // Render actions (only those not yet matched)
                actionsContainer.innerHTML = '';
                for (let orderIdx = 0; orderIdx < actionOrder.length; orderIdx++) {
                    const actionIdx = actionOrder[orderIdx];
                    if (!matchedActions[actionIdx]) {
                        const card = document.createElement('div');
                        card.className = `action-card bg-white rounded-xl p-3 shadow-md border-2 transition-all cursor-pointer hover:shadow-lg ${selectedActionIndex === actionIdx ? 'ring-4 ring-green-500 border-green-500 scale-105' : 'border-gray-200 hover:border-green-300'}`;
                        card.setAttribute('data-action-index', actionIdx);
                        card.innerHTML = `<div class="flex items-start gap-2"><div class="text-2xl">🛡️</div><div class="flex-1"><p class="text-gray-800 text-sm md:text-base">${escapeHtml(challenges[actionIdx].correctAction)}</p><p class="text-xs text-gray-400 mt-1"><i class="fas fa-lightbulb"></i> <?php echo t('tap'); ?> <?php echo t('match'); ?></p></div></div>`;
                        card.addEventListener('click', (e) => { e.stopPropagation(); if (!isProcessing) handleActionClick(actionIdx); });
                        actionsContainer.appendChild(card);
                    }
                }
                if (actionsContainer.children.length === 0) {
                    actionsContainer.innerHTML = '<div class="text-center text-green-600 py-8 bg-green-50 rounded-xl"><i class="fas fa-check-circle text-4xl mb-2"></i><p><?php echo t('all_pairs_matched'); ?></p></div>';
                }
            }

            function escapeHtml(str) {
                return str.replace(/[&<>]/g, function(m) {
                    if (m === '&') return '&amp;';
                    if (m === '<') return '&lt;';
                    if (m === '>') return '&gt;';
                    return m;
                });
            }

            function showFeedback(message, isError = false, explanation = '') {
                const existing = document.getElementById('game-feedback');
                if (existing) existing.remove();
                const div = document.createElement('div');
                div.id = 'game-feedback';
                div.className = `fixed bottom-4 left-1/2 transform -translate-x-1/2 z-50 px-6 py-3 rounded-xl shadow-lg text-white font-bold text-center max-w-sm transition-all duration-300 ${isError ? 'bg-red-600' : 'bg-green-600'}`;
                div.innerHTML = `<div class="flex items-center gap-2"><i class="fas ${isError ? 'fa-times-circle' : 'fa-check-circle'}"></i><span>${message}</span></div>${explanation ? `<p class="text-xs mt-1 font-normal opacity-90">${escapeHtml(explanation)}</p>` : ''}`;
                document.body.appendChild(div);
                setTimeout(() => div.remove(), 4000);
            }

            function handleSituationClick(index) {
                if (matchedSituations[index]) return;
                if (selectedActionIndex !== null) {
                    attemptMatch(index, selectedActionIndex);
                    selectedActionIndex = null;
                } else {
                    selectedSituationIndex = index;
                    renderGame();
                    showFeedback(`<?php echo t('select_action'); ?>: "${challenges[index].situation.substring(0, 50)}..."`, false, '<?php echo t('select_action'); ?>');
                }
            }

            function handleActionClick(index) {
                if (matchedActions[index]) return;
                if (selectedSituationIndex !== null) {
                    attemptMatch(selectedSituationIndex, index);
                    selectedSituationIndex = null;
                } else {
                    selectedActionIndex = index;
                    renderGame();
                    showFeedback(`<?php echo t('select_action'); ?>: "${challenges[index].correctAction.substring(0, 50)}..."`, false, '<?php echo t('select_action'); ?>');
                }
            }

            function attemptMatch(situationIdx, actionIdx) {
                if (isProcessing) return;
                isProcessing = true;

                // If either is already matched, cannot match again
                if (matchedSituations[situationIdx] || matchedActions[actionIdx]) {
                    showFeedback('<?php echo t('all_pairs_matched'); ?>', true, '<?php echo t('select_action'); ?>');
                    isProcessing = false;
                    renderGame();
                    return;
                }

                const isCorrect = (situationIdx === actionIdx);
                
                // Lock both items permanently (whether correct or wrong)
                matchedSituations[situationIdx] = true;
                matchedActions[actionIdx] = true;
                matchedPairs[situationIdx] = actionIdx; // store for reference

                if (isCorrect) {
                    currentScore++;
                    document.getElementById('score').textContent = currentScore;
                    showFeedback(`<?php echo t('correct_match'); ?>`, false, challenges[situationIdx].explanation || '<?php echo t('correct_match'); ?>');
                } else {
                    // Wrong match - no points, but both are now permanently locked.
                    showFeedback(`<?php echo t('wrong_match'); ?> <?php echo t('correct_answer'); ?> "${challenges[situationIdx].correctAction}"`, true, challenges[situationIdx].explanation || '<?php echo t('correct_answer'); ?>');
                }

                // Clear selections
                selectedSituationIndex = null;
                selectedActionIndex = null;
                
                // Check if all items are now matched (all situations used)
                const allMatched = matchedSituations.every(v => v === true);
                if (allMatched) {
                    showFeedback('<?php echo t('all_pairs_matched'); ?> <?php echo t('submit_score'); ?>.', false, '<?php echo t('submit_score'); ?>');
                    const btn = document.getElementById('submitBtn');
                    if (btn) {
                        btn.disabled = false;
                        btn.classList.remove('disabled:opacity-50');
                    }
                }
                
                renderGame();
                isProcessing = false;
            }

            function submitGameScore() {
                if (isSubmittingFlag) return;
                // Check if all pairs have been made (all situations matched)
                if (!matchedSituations.every(v => v === true)) {
                    showFeedback('Kailangan muna itugma ang LAHAT ng sitwasyon (kahit mali) bago isumite ang puntos.', true, 'Kumpletuhin ang lahat ng pares. Ang mali ay hindi na mababago, pero ituloy lang hanggang matapos.');
                    return;
                }
                isSubmittingFlag = true;
                const btn = document.getElementById('submitBtn');
                if (btn) { btn.disabled = true; btn.textContent = 'Isinusumite...'; }
                const xp = Math.floor((currentScore / totalItems) * 50);
                const coins = Math.floor((currentScore / totalItems) * 30);
                fetch('../../api/save_game_score.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ game_type: 'handa-matching', score: currentScore, total: totalItems, xp: xp, coins: coins })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`Puntos: ${currentScore}/${totalItems}\nNakuhang XP: +${xp}\nNakuhang Coins: +${coins}\n\nPatuloy na maging handa. Maaari ka nang maglaro muli sa pamamagitan ng pag-refresh ng pahina o pagbalik sa menu.`);
                        location.reload(); // Reload to start fresh (reset game)
                    } else {
                        alert('Error: ' + (data.error || 'Hindi na-save ang puntos. Subukan muli.'));
                        if (btn) { btn.disabled = false; btn.textContent = 'I-submit ang Puntos'; }
                        isSubmittingFlag = false;
                    }
                })
                .catch(err => {
                    alert('Network error: ' + err.message);
                    if (btn) { btn.disabled = false; btn.textContent = 'I-submit ang Puntos'; }
                    isSubmittingFlag = false;
                });
            }

            document.addEventListener('DOMContentLoaded', () => { 
                initActionOrder(); 
                renderGame(); 
            });
        </script>

        <?php elseif ($mode === 'quiz'): ?>
        <!-- Game Mode 2: Tama o Mali Quiz -->
        <div class="bg-white rounded-3xl shadow-2xl p-4 md:p-6">
            <div class="flex flex-col md:flex-row justify-between items-center mb-4 md:mb-6 gap-4">
                <h2 class="text-xl md:text-2xl font-bold text-blue-600"><?php echo t('true_false'); ?>: <?php echo t('check_statement'); ?></h2>
                <a href="?mode=menu" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-times text-2xl"></i>
                </a>
            </div>

            <div class="bg-blue-50 border-l-4 border-blue-500 p-3 mb-4 rounded">
                <p class="text-sm text-blue-800"><i class="fas fa-info-circle mr-2"></i><strong><?php echo t('instructions'); ?>:</strong> <?php echo t('check_statement'); ?>. Piliin kung ito ay <?php echo t('correct'); ?> o <?php echo t('incorrect'); ?>. Makakakuha ka ng <?php echo t('points'); ?> sa bawat tamang sagot. Pagkatapos ng lahat ng <?php echo t('question'); ?>, ipapakita ang iyong kabuuang <?php echo t('points'); ?>.</p>
            </div>

            <?php
            // Shuffle questions for variety
            $shuffledQuestions = $quizQuestions;
            shuffle($shuffledQuestions);
            ?>
            <div id="quiz-container" class="space-y-6">
                <div id="question-card" class="bg-gray-50 rounded-2xl p-6 shadow-md">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-sm text-gray-500">Tanong <span id="current-q-num">1</span> ng <span id="total-q"><?php echo count($shuffledQuestions); ?></span></span>
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-bold">Puntos: <span id="quiz-score">0</span></span>
                    </div>
                    <div id="question-text" class="text-xl md:text-2xl font-semibold text-gray-800 text-center py-6 px-4"></div>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center mt-6">
                        <button id="answer-tama" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-xl transition text-lg shadow-md">TAMA</button>
                        <button id="answer-mali" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-8 rounded-xl transition text-lg shadow-md">MALI</button>
                    </div>
                </div>
                <div id="result-area" class="hidden bg-gray-100 rounded-2xl p-6 text-center"></div>
            </div>

            <div class="mt-6 text-center">
                <button id="reset-quiz" class="bg-gray-500 text-white px-6 py-2 rounded-xl font-bold hover:bg-gray-600 transition hidden">
                    <i class="fas fa-undo-alt mr-2"></i> Ulitin ang Quiz
                </button>
            </div>
        </div>

        <script>
            const quizQuestions = <?php echo json_encode($shuffledQuestions); ?>;
            let quizCurrentIndex = 0;
            let quizScore = 0;
            let quizAnswered = false;
            let quizFinished = false;

            const questionTextEl = document.getElementById('question-text');
            const currentQNumEl = document.getElementById('current-q-num');
            const quizScoreEl = document.getElementById('quiz-score');
            const answerTamaBtn = document.getElementById('answer-tama');
            const answerMaliBtn = document.getElementById('answer-mali');
            const resultArea = document.getElementById('result-area');
            const resetQuizBtn = document.getElementById('reset-quiz');
            const questionCard = document.getElementById('question-card');

            function loadQuestion() {
                if (quizCurrentIndex >= quizQuestions.length) {
                    finishQuiz();
                    return;
                }
                const q = quizQuestions[quizCurrentIndex];
                questionTextEl.textContent = q.statement;
                currentQNumEl.textContent = quizCurrentIndex + 1;
                quizAnswered = false;
                answerTamaBtn.disabled = false;
                answerMaliBtn.disabled = false;
                answerTamaBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                answerMaliBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                const existingFeedback = questionCard.querySelector('.answer-feedback');
                if (existingFeedback) existingFeedback.remove();
                resultArea.classList.add('hidden');
                resetQuizBtn.classList.add('hidden');
            }

            function handleAnswer(selectedIsCorrect) {
                if (quizAnswered || quizFinished) return;
                const q = quizQuestions[quizCurrentIndex];
                const isCorrect = (selectedIsCorrect === q.isCorrect);
                quizAnswered = true;
                answerTamaBtn.disabled = true;
                answerMaliBtn.disabled = true;
                answerTamaBtn.classList.add('opacity-50', 'cursor-not-allowed');
                answerMaliBtn.classList.add('opacity-50', 'cursor-not-allowed');

                const feedbackDiv = document.createElement('div');
                feedbackDiv.className = `answer-feedback mt-4 p-3 rounded-lg text-center ${isCorrect ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`;
                if (isCorrect) {
                    quizScore++;
                    quizScoreEl.textContent = quizScore;
                    feedbackDiv.innerHTML = `<i class="fas fa-check-circle mr-2"></i> Tama! +1 puntos.<br><span class="text-sm">${q.explanation || ''}</span>`;
                } else {
                    const correctAnswerText = q.isCorrect ? 'TAMA' : 'MALI';
                    feedbackDiv.innerHTML = `<i class="fas fa-times-circle mr-2"></i> Mali. Ang tamang sagot ay ${correctAnswerText}.<br><span class="text-sm">${q.explanation || ''}</span>`;
                }
                questionCard.appendChild(feedbackDiv);

                setTimeout(() => {
                    quizCurrentIndex++;
                    if (quizCurrentIndex < quizQuestions.length) {
                        loadQuestion();
                    } else {
                        finishQuiz();
                    }
                }, 2500);
            }

            function finishQuiz() {
                quizFinished = true;
                const total = quizQuestions.length;
                const xp = Math.floor((quizScore / total) * 50);
                const coins = Math.floor((quizScore / total) * 30);
                
                fetch('../../api/save_game_score.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ game_type: 'handa-quiz', score: quizScore, total: total, xp: xp, coins: coins })
                })
                .then(response => response.json())
                .then(data => {
                    let message = `Natapos mo ang quiz!\nPuntos: ${quizScore}/${total}\nXP na nakuha: +${xp}\nCoins na nakuha: +${coins}`;
                    if (!data.success) message += `\n(Pero may problema sa pag-save: ${data.error || 'unknown'})`;
                    alert(message);
                })
                .catch(err => {
                    alert(`Natapos mo ang quiz! Puntos: ${quizScore}/${total}\nNgunit may error sa pag-save: ${err.message}`);
                });

                resultArea.classList.remove('hidden');
                resultArea.innerHTML = `
                    <h3 class="text-2xl font-bold mb-2">Tapos na ang Quiz</h3>
                    <p class="text-lg">Iyong puntos: ${quizScore} / ${total}</p>
                    <p class="text-md text-gray-600 mt-2">Nakakuha ka ng ${xp} XP at ${coins} Coins.</p>
                    <button onclick="location.reload()" class="mt-4 bg-blue-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-blue-700">Maglaro muli</button>
                `;
                resetQuizBtn.classList.remove('hidden');
                answerTamaBtn.disabled = true;
                answerMaliBtn.disabled = true;
                answerTamaBtn.classList.add('opacity-50');
                answerMaliBtn.classList.add('opacity-50');
            }

            answerTamaBtn.addEventListener('click', () => handleAnswer(true));
            answerMaliBtn.addEventListener('click', () => handleAnswer(false));
            resetQuizBtn.addEventListener('click', () => location.reload());

            loadQuestion();
        </script>

        <?php elseif ($mode === 'aralin'): ?>
        <!-- Aralin/Wiki Mode: Educational Content -->
        <div class="bg-white rounded-3xl shadow-2xl p-4 md:p-6">
            <div class="flex flex-col md:flex-row justify-between items-center mb-4 md:mb-6 gap-4">
                <h2 class="text-xl md:text-2xl font-bold text-purple-600"><?php echo t('lesson_wiki'); ?>: <?php echo t('disaster_readiness'); ?></h2>
                <a href="?mode=menu" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-times text-2xl"></i>
                </a>
            </div>

            <div class="bg-purple-50 border-l-4 border-purple-500 p-3 mb-6 rounded">
                <p class="text-sm text-purple-800"><i class="fas fa-info-circle mr-2"></i><strong>Mahalagang Paalala:</strong> Basahin ang mga sumusunod na impormasyon upang malaman ang mga tamang gagawin sa panahon ng sakuna. Makakatulong ito sa iyong pagsagot sa mga laro.</p>
            </div>

            <div class="space-y-6">
                <?php foreach ($challenges as $index => $item): ?>
                <div class="bg-gradient-to-r from-orange-50 to-red-50 rounded-xl p-4 shadow-md border-l-8 border-orange-500">
                    <div class="flex items-start gap-3">
                     
                        <div class="flex-1">
                            <h3 class="font-bold text-orange-800 text-lg mb-1"><?php echo t('situation'); ?> <?php echo $index + 1; ?>: <?php echo htmlspecialchars($item['situation']); ?></h3>
                            <div class="bg-green-100 rounded-lg p-3 mt-2">
                                <p class="text-green-800 font-semibold"><i class="fas fa-check-circle mr-1"></i> <?php echo t('correct_action'); ?>:</p>
                                <p class="text-gray-800"><?php echo htmlspecialchars($item['correctAction']); ?></p>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-3 mt-2">
                                <p class="text-blue-800 font-semibold"><i class="fas fa-lightbulb mr-1"></i> Bakit</p>
                                <p class="text-gray-700 text-sm"><?php echo htmlspecialchars($item['explanation']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-8 bg-yellow-50 rounded-xl p-5 border-2 border-yellow-400">
                <h3 class="font-bold text-yellow-800 text-lg mb-3"><i class="fas fa-star-of-life mr-2"></i> Mga Dapat Tandaan sa Lahat ng Sakuna</h3>
                <ul class="list-disc list-inside space-y-2 text-gray-700">
                    <li>Manatiling kalmado at huwag mag-panic.</li>
                    <li>Laging makinig sa balita at sumunod sa mga awtoridad.</li>
                    <li>Maghanda ng emergency go bag na may pagkain, tubig, first aid kit, flashlight, at whistle.</li>
                    <li>Alamin ang evacuation plan ng inyong lugar.</li>
                    <li>Turuan ang pamilya kung ano ang gagawin sa iba't ibang uri ng sakuna.</li>
                </ul>
            </div>

            <div class="mt-6 text-center">
                <a href="?mode=menu" class="inline-block bg-purple-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-purple-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i> <?php echo t('back'); ?> sa Menu
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>