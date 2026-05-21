// Quiz JavaScript - Timer, Answer Selection, Score Tracking

let timeLeft = 30;
let timerInterval;
let currentScore = 0;
let correctAnswer = '';
let currentQuestionIndex = 0;

// Initialize quiz state from PHP
function initQuiz() {
    const quizData = document.getElementById('quizData');
    if (quizData) {
        currentScore = parseInt(quizData.dataset.score);
        correctAnswer = quizData.dataset.correctAnswer;
        currentQuestionIndex = parseInt(quizData.dataset.questionIndex);
    }
    startTimer();
}

// Countdown timer
function startTimer() {
    timeLeft = 30;
    updateTimerDisplay();
    
    timerInterval = setInterval(() => {
        timeLeft--;
        updateTimerDisplay();
        
        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            handleTimeout();
        }
    }, 1000);
}

function updateTimerDisplay() {
    const timerText = document.getElementById('timerText');
    const timerBar = document.getElementById('timerBar');
    
    if (timerText && timerBar) {
        timerText.textContent = timeLeft;
        const percentage = (timeLeft / 30) * 100;
        timerBar.style.width = percentage + '%';
        
        // Change color based on time
        if (timeLeft > 20) {
            timerBar.className = 'bg-green-500 h-3 rounded-full transition-all duration-1000';
        } else if (timeLeft > 10) {
            timerBar.className = 'bg-yellow-500 h-3 rounded-full transition-all duration-1000';
        } else {
            timerBar.className = 'bg-red-500 h-3 rounded-full transition-all duration-1000';
        }
    }
}

function handleTimeout() {
    selectAnswer('timeout');
}

// Answer selection
function selectAnswer(selectedOption) {
    // Clear timer
    clearInterval(timerInterval);
    
    // Lock all buttons
    const buttons = document.querySelectorAll('.answer-btn');
    buttons.forEach(btn => {
        btn.disabled = true;
        btn.classList.add('pointer-events-none');
    });
    
    // Get selected button
    let selectedBtn;
    if (selectedOption !== 'timeout') {
        selectedBtn = document.querySelector(`[data-option="${selectedOption}"]`);
    }
    
    // Show correct answer
    const correctBtn = document.querySelector(`[data-option="${correctAnswer}"]`);
    if (correctBtn) {
        correctBtn.classList.remove('bg-gray-100');
        correctBtn.classList.add('bg-green-500', 'text-white', 'border-green-500');
    }
    
    // Handle wrong answer
    if (selectedOption !== 'timeout' && selectedOption !== correctAnswer && selectedBtn) {
        selectedBtn.classList.remove('bg-gray-100');
        selectedBtn.classList.add('bg-red-500', 'text-white', 'border-red-500', 'shake');
    }
    
    // Update score
    if (selectedOption === correctAnswer) {
        currentScore++;
        if (typeof triggerConfetti === 'function') {
            triggerConfetti();
        }
    }
    
    // Show fun fact modal after delay
    setTimeout(() => {
        const modal = document.getElementById('funFactModal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }, 1500);
}

function nextQuestion() {
    // Update session via AJAX
    const formData = new FormData();
    formData.append('current_score', currentScore);
    formData.append('current_index', currentQuestionIndex + 1);
    
    const quizData = document.getElementById('quizData');
    if (quizData) {
        formData.append('question_id', quizData.dataset.questionId);
        formData.append('correct_option', quizData.dataset.correctAnswer);
    }
    
    fetch('quiz.php', {
        method: 'POST',
        body: formData
    }).then(() => {
        location.reload();
    });
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initQuiz);
} else {
    initQuiz();
}
