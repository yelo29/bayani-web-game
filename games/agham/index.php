<?php
/**
 * Agham - Science Matching Game
 * For Grades 7-10 Filipino Students
 * 
 * Game modes:
 * 1. Siyensya - Match cell organelles with their functions (formerly "Sihay")
 * 2. Likhaan - Match organisms with their habitats/roles
 * 3. Elemento - Match element symbols with their names
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
$cellData = json_decode(file_get_contents(__DIR__ . '/data/cell.json'), true);
$ecosystemData = json_decode(file_get_contents(__DIR__ . '/data/ecosystem.json'), true);
$elementData = json_decode(file_get_contents(__DIR__ . '/data/elemento.json'), true);

// Get current mode from URL
$mode = $_GET['mode'] ?? 'menu';
?>

<main class="min-h-screen bg-gradient-to-br from-green-800 via-teal-700 to-blue-800 py-8 px-4">
    <div class="max-w-6xl mx-auto">
        <!-- Mode Selection Menu -->
        <?php if ($mode === 'menu'): ?>
        <div class="text-center mb-8">
            <h1 class="text-4xl md:text-5xl font-bold font-serif text-white mb-4">Agham Pilipinas</h1>
            <p class="text-xl text-white/80 mb-2"><?php echo t('science_tech'); ?> - Grades 7-10</p>
            <p class="text-white/60"><?php echo t('choose_mode_play'); ?></p>
            <!-- Aralin/Wiki Button -->
            <div class="mt-6 text-center">
                <button onclick="location.href='?mode=aralin'" class="inline-block bg-purple-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-purple-700 transition shadow-lg">
                    <i class="fas fa-book-open mr-2"></i> <?php echo t('lesson_wiki'); ?>
                </button>
                <br><br>
                <p class="text-xs text-white/80"><?php echo t('read_info_here'); ?></p>
                <p class="text-xs text-white/80"><?php echo t('learn_game_answers'); ?></p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Mode 1: Siyensya (Cell Biology) -->
            <div class="bg-white rounded-3xl shadow-2xl p-6 transform hover:scale-105 transition-transform duration-300 cursor-pointer" onclick="location.href='?mode=siyensya'">
                <div class="text-center mb-4">
                    <div class="w-20 h-20 bg-green-100 rounded-full mx-auto mb-3 flex items-center justify-center">
                        <i class="fas fa-microscope text-green-600 text-4xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-green-600 mb-2">Siyensya</h2>
                    <p class="text-gray-600 text-sm">Mga Bahagi ng Sihay</p>
                </div>
                <div class="mb-4">
                    <div class="flex flex-wrap gap-2 justify-center">
                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">8 Organelles</span>
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">Matching Game</span>
                    </div>
                </div>
                <button class="w-full bg-green-600 text-white py-3 rounded-xl font-bold hover:bg-green-700 transition">
                    <i class="fas fa-play mr-2"></i> <?php echo t('play'); ?>
                </button>
            </div>

            <!-- Mode 2: Likhaan (Ecosystem) -->
            <div class="bg-white rounded-3xl shadow-2xl p-6 transform hover:scale-105 transition-transform duration-300 cursor-pointer" onclick="location.href='?mode=likhaan'">
                <div class="text-center mb-4">
                    <div class="w-20 h-20 bg-teal-100 rounded-full mx-auto mb-3 flex items-center justify-center">
                        <i class="fas fa-tree text-teal-600 text-4xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-teal-600 mb-2">Likhaan</h2>
                    <p class="text-gray-600 text-sm">Ecosystem at mga Organismo</p>
                </div>
                <div class="mb-4">
                    <div class="flex flex-wrap gap-2 justify-center">
                        <span class="px-2 py-1 bg-teal-100 text-teal-800 rounded-full text-xs">8 Organisms</span>
                        <span class="px-2 py-1 bg-orange-100 text-orange-800 rounded-full text-xs">Matching Game</span>
                    </div>
                </div>
                <button class="w-full bg-teal-600 text-white py-3 rounded-xl font-bold hover:bg-teal-700 transition">
                    <i class="fas fa-play mr-2"></i> <?php echo t('play'); ?>
                </button>
            </div>

            <!-- Mode 3: Elemento -->
            <div class="bg-white rounded-3xl shadow-2xl p-6 transform hover:scale-105 transition-transform duration-300 cursor-pointer" onclick="location.href='?mode=elemento'">
                <div class="text-center mb-4">
                    <div class="w-20 h-20 bg-blue-100 rounded-full mx-auto mb-3 flex items-center justify-center">
                        <i class="fas fa-flask text-blue-600 text-4xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-blue-600 mb-2">Elemento</h2>
                    <p class="text-gray-600 text-sm">Mga Elementong Kimikal</p>
                </div>
                <div class="mb-4">
                    <div class="flex flex-wrap gap-2 justify-center">
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">8 Elements</span>
                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Matching Game</span>
                    </div>
                </div>
                <button class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 transition">
                    <i class="fas fa-play mr-2"></i> <?php echo t('play'); ?>
                </button>
            </div>
        </div>

        <!-- Back Button -->
        <div class="mt-4 text-center">
            <a href="../../maglaro.php" class="inline-block bg-white/20 text-white px-8 py-3 rounded-xl font-bold hover:bg-white/30 transition">
                <i class="fas fa-arrow-left mr-2"></i> <?php echo t('back'); ?>
            </a>
        </div>

        <?php elseif ($mode === 'siyensya'): ?>
        <!-- Mode 1: Cell Organelles Matching (formerly sihay) -->
        <div class="bg-white rounded-3xl shadow-2xl p-4 md:p-6">
            <div class="flex flex-col md:flex-row justify-between items-center mb-4 md:mb-6 gap-4">
                <h2 class="text-xl md:text-2xl font-bold text-green-600"><?php echo t('science'); ?> - <?php echo t('match'); ?> <?php echo t('cell_organelles'); ?></h2>
                <a href="?mode=menu" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-times text-2xl"></i>
                </a>
            </div>

            <div class="bg-green-50 border-l-4 border-green-500 p-3 mb-4 rounded">
                <p class="text-sm text-green-800"><i class="fas fa-info-circle mr-2"></i><strong><?php echo t('instructions'); ?>:</strong> <?php echo t('tap_organelle'); ?>. Kapag tama ang tugma, mawawala ang pares at makakakuha ka ng <?php echo t('points'); ?>.</p>
            </div>

            <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
                <div class="bg-gray-100 rounded-xl px-6 py-3">
                    <span class="text-gray-700 font-bold"><?php echo t('points'); ?>: </span>
                    <span id="score" class="text-3xl font-bold text-green-600">0</span>
                    <span class="text-gray-500"> / <?php echo count($cellData); ?></span>
                </div>
                <button id="submitBtn" onclick="submitGameScore('siyensya')" disabled class="bg-green-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-check-circle mr-2"></i> <?php echo t('submit'); ?> <?php echo t('points'); ?>
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-gray-50 rounded-2xl p-4">
                    <h3 class="font-bold text-gray-800 mb-4 text-center text-lg border-b pb-2">
                        <i class="fas fa-microscope text-green-500 mr-2"></i> ORGANELLE
                    </h3>
                    <div id="itemsContainer" class="space-y-3 max-h-[600px] overflow-y-auto pr-2"></div>
                </div>
                <div class="bg-gray-50 rounded-2xl p-4">
                    <h3 class="font-bold text-gray-800 mb-4 text-center text-lg border-b pb-2">
                        <i class="fas fa-puzzle-piece text-green-600 mr-2"></i> TUNGKULIN
                    </h3>
                    <div id="targetsContainer" class="space-y-3 max-h-[600px] overflow-y-auto pr-2"></div>
                </div>
            </div>
        </div>

        <?php elseif ($mode === 'likhaan'): ?>
        <!-- Mode 2: Ecosystem Matching -->
        <div class="bg-white rounded-3xl shadow-2xl p-4 md:p-6">
            <div class="flex flex-col md:flex-row justify-between items-center mb-4 md:mb-6 gap-4">
                <h2 class="text-xl md:text-2xl font-bold text-teal-600"><?php echo t('ecosystem'); ?> - <?php echo t('match'); ?> <?php echo t('organism'); ?> <?php echo t('habitat'); ?></h2>
                <a href="?mode=menu" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-times text-2xl"></i>
                </a>
            </div>

            <div class="bg-teal-50 border-l-4 border-teal-500 p-3 mb-4 rounded">
                <p class="text-sm text-teal-800"><i class="fas fa-info-circle mr-2"></i><strong><?php echo t('instructions'); ?>:</strong> <?php echo t('tap_organism'); ?>. Kapag tama ang tugma, mawawala ang pares at makakakuha ka ng <?php echo t('points'); ?>.</p>
            </div>

            <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
                <div class="bg-gray-100 rounded-xl px-6 py-3">
                    <span class="text-gray-700 font-bold"><?php echo t('points'); ?>: </span>
                    <span id="score" class="text-3xl font-bold text-teal-600">0</span>
                    <span class="text-gray-500"> / <?php echo count($ecosystemData); ?></span>
                </div>
                <button id="submitBtn" onclick="submitGameScore('likhaan')" disabled class="bg-teal-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-teal-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-check-circle mr-2"></i> <?php echo t('submit'); ?> <?php echo t('points'); ?>
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-gray-50 rounded-2xl p-4">
                    <h3 class="font-bold text-gray-800 mb-4 text-center text-lg border-b pb-2">
                        <i class="fas fa-leaf text-teal-500 mr-2"></i> ORGANISMO
                    </h3>
                    <div id="itemsContainer" class="space-y-3 max-h-[600px] overflow-y-auto pr-2"></div>
                </div>
                <div class="bg-gray-50 rounded-2xl p-4">
                    <h3 class="font-bold text-gray-800 mb-4 text-center text-lg border-b pb-2">
                        <i class="fas fa-home text-teal-600 mr-2"></i> TIRAHAN O TUNGKULIN
                    </h3>
                    <div id="targetsContainer" class="space-y-3 max-h-[600px] overflow-y-auto pr-2"></div>
                </div>
            </div>
        </div>

        <?php elseif ($mode === 'elemento'): ?>
        <!-- Mode 3: Element Matching -->
        <div class="bg-white rounded-3xl shadow-2xl p-4 md:p-6">
            <div class="flex flex-col md:flex-row justify-between items-center mb-4 md:mb-6 gap-4">
                <h2 class="text-xl md:text-2xl font-bold text-blue-600"><?php echo t('element'); ?> - <?php echo t('match'); ?> <?php echo t('element_symbol'); ?> <?php echo t('element_name'); ?></h2>
                <a href="?mode=menu" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-times text-2xl"></i>
                </a>
            </div>

            <div class="bg-blue-50 border-l-4 border-blue-500 p-3 mb-4 rounded">
                <p class="text-sm text-blue-800"><i class="fas fa-info-circle mr-2"></i><strong><?php echo t('instructions'); ?>:</strong> <?php echo t('tap_element'); ?>. Kapag tama ang tugma, mawawala ang pares at makakakuha ka ng <?php echo t('points'); ?>.</p>
            </div>

            <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
                <div class="bg-gray-100 rounded-xl px-6 py-3">
                    <span class="text-gray-700 font-bold"><?php echo t('points'); ?>: </span>
                    <span id="score" class="text-3xl font-bold text-blue-600">0</span>
                    <span class="text-gray-500"> / <?php echo count($elementData); ?></span>
                </div>
                <button id="submitBtn" onclick="submitGameScore('elemento')" disabled class="bg-blue-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-check-circle mr-2"></i> <?php echo t('submit'); ?> <?php echo t('points'); ?>
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-gray-50 rounded-2xl p-4">
                    <h3 class="font-bold text-gray-800 mb-4 text-center text-lg border-b pb-2">
                        <i class="fas fa-atom text-blue-500 mr-2"></i> SIMBOLO NG ELEMENTO
                    </h3>
                    <div id="itemsContainer" class="space-y-3 max-h-[600px] overflow-y-auto pr-2"></div>
                </div>
                <div class="bg-gray-50 rounded-2xl p-4">
                    <h3 class="font-bold text-gray-800 mb-4 text-center text-lg border-b pb-2">
                        <i class="fas fa-tag text-blue-600 mr-2"></i> PANGALAN NG ELEMENTO
                    </h3>
                    <div id="targetsContainer" class="space-y-3 max-h-[600px] overflow-y-auto pr-2"></div>
                </div>
            </div>
        </div>

        <?php elseif ($mode === 'aralin'): ?>
        <!-- Aralin/Wiki Mode: Educational Content -->
        <div class="bg-white rounded-3xl shadow-2xl p-4 md:p-6">
            <div class="flex flex-col md:flex-row justify-between items-center mb-4 md:mb-6 gap-4">
                <h2 class="text-xl md:text-2xl font-bold text-purple-600"><?php echo t('lesson_wiki'); ?></h2>
                <a href="?mode=menu" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-times text-2xl"></i>
                </a>
            </div>

            <div class="bg-purple-50 border-l-4 border-purple-500 p-3 mb-4 rounded">
                <p class="text-sm text-purple-800"><i class="fas fa-info-circle mr-2"></i><strong><?php echo t('instructions'); ?>:</strong> <?php echo t('read_info_here'); ?> <?php echo t('learn_game_answers'); ?></p>
            </div>

            <!-- Tab Navigation -->
            <div class="flex flex-wrap gap-2 mb-4">
                <button onclick="showWikiTab('siyensya')" id="tab-siyensya" class="wiki-tab px-4 py-2 rounded-lg font-bold bg-green-600 text-white"><?php echo t('science'); ?></button>
                <button onclick="showWikiTab('likhaan')" id="tab-likhaan" class="wiki-tab px-4 py-2 rounded-lg font-bold bg-gray-200 text-gray-700 hover:bg-gray-300"><?php echo t('ecosystem'); ?></button>
                <button onclick="showWikiTab('elemento')" id="tab-elemento" class="wiki-tab px-4 py-2 rounded-lg font-bold bg-gray-200 text-gray-700 hover:bg-gray-300"><?php echo t('element'); ?></button>
            </div>

            <!-- Siyensya Content (formerly Sihay) -->
            <div id="wiki-siyensya" class="wiki-content">
                <h3 class="font-bold text-gray-800 mb-4 text-lg">Mga Bahagi ng Sihay (Cell Organelles)</h3>
                <div class="space-y-3">
                    <?php foreach ($cellData as $item): ?>
                    <div class="bg-green-50 p-3 rounded-lg border-l-4 border-green-500">
                        <div class="font-bold text-green-800"><?php echo htmlspecialchars($item['item']); ?></div>
                        <p class="text-sm text-gray-700"><?php echo htmlspecialchars($item['target']); ?></p>
                        <p class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars($item['explanation']); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Likhaan Content -->
            <div id="wiki-likhaan" class="wiki-content hidden">
                <h3 class="font-bold text-gray-800 mb-4 text-lg">Ecosystem at mga Organismo</h3>
                <div class="space-y-3">
                    <?php foreach ($ecosystemData as $item): ?>
                    <div class="bg-teal-50 p-3 rounded-lg border-l-4 border-teal-500">
                        <div class="font-bold text-teal-800"><?php echo htmlspecialchars($item['item']); ?></div>
                        <p class="text-sm text-gray-700"><?php echo htmlspecialchars($item['target']); ?></p>
                        <p class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars($item['explanation']); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Elemento Content -->
            <div id="wiki-elemento" class="wiki-content hidden">
                <h3 class="font-bold text-gray-800 mb-4 text-lg">Mga Elementong Kimikal</h3>
                <div class="space-y-3">
                    <?php foreach ($elementData as $item): ?>
                    <div class="bg-blue-50 p-3 rounded-lg border-l-4 border-blue-500">
                        <div class="font-bold text-blue-800"><?php echo htmlspecialchars($item['item']); ?></div>
                        <p class="text-sm text-gray-700"><?php echo htmlspecialchars($item['target']); ?></p>
                        <p class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars($item['explanation']); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</main>

<script>
// Wiki tab switching
function showWikiTab(tabName) {
    document.querySelectorAll('.wiki-content').forEach(content => {
        content.classList.add('hidden');
    });
    document.querySelectorAll('.wiki-tab').forEach(tab => {
        tab.classList.remove('bg-green-600', 'bg-teal-600', 'bg-blue-600', 'text-white');
        tab.classList.add('bg-gray-200', 'text-gray-700');
    });
    document.getElementById('wiki-' + tabName).classList.remove('hidden');
    const selectedTab = document.getElementById('tab-' + tabName);
    selectedTab.classList.remove('bg-gray-200', 'text-gray-700');
    if (tabName === 'siyensya') {
        selectedTab.classList.add('bg-green-600', 'text-white');
    } else if (tabName === 'likhaan') {
        selectedTab.classList.add('bg-teal-600', 'text-white');
    } else if (tabName === 'elemento') {
        selectedTab.classList.add('bg-blue-600', 'text-white');
    }
}

// Game state variables
let gameData = [];
let totalItems = 0;
let currentScore = 0;
let matchedItems = [];
let matchedTargets = [];
let selectedItemIndex = null;
let selectedTargetIndex = null;
let isProcessing = false;
let isSubmittingFlag = false;
let targetOrder = [];

function shuffleArray(arr) {
    for (let i = arr.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [arr[i], arr[j]] = [arr[j], arr[i]];
    }
    return arr;
}

function initTargetOrder() {
    targetOrder = [];
    for (let i = 0; i < totalItems; i++) targetOrder.push(i);
    targetOrder = shuffleArray([...targetOrder]);
}

function renderGame() {
    const itemsContainer = document.getElementById('itemsContainer');
    const targetsContainer = document.getElementById('targetsContainer');
    if (!itemsContainer || !targetsContainer) return;

    itemsContainer.innerHTML = '';
    for (let i = 0; i < totalItems; i++) {
        if (!matchedItems[i]) {
            const card = document.createElement('div');
            card.className = `item-card bg-white rounded-xl p-3 shadow-md border-2 transition-all cursor-pointer hover:shadow-lg ${selectedItemIndex === i ? 'ring-4 ring-blue-500 border-blue-500 scale-105' : 'border-gray-200 hover:border-blue-300'}`;
            card.setAttribute('data-item-index', i);
            card.innerHTML = `<div class="flex-1"><p class="text-gray-800 text-sm md:text-base font-medium">${escapeHtml(gameData[i].item)}</p><p class="text-xs text-gray-400 mt-1"><i class="fas fa-lightbulb"></i> I-tap para piliin</p></div>`;
            card.addEventListener('click', (e) => { e.stopPropagation(); if (!isProcessing) handleItemClick(i); });
            itemsContainer.appendChild(card);
        }
    }
    if (itemsContainer.children.length === 0) {
        itemsContainer.innerHTML = '<div class="text-center text-green-600 py-8 bg-green-50 rounded-xl"><i class="fas fa-trophy text-4xl mb-2"></i><p>Napakahusay! Natapos mo na ang lahat ng item</p></div>';
    }

    targetsContainer.innerHTML = '';
    for (let orderIdx = 0; orderIdx < targetOrder.length; orderIdx++) {
        const targetIdx = targetOrder[orderIdx];
        if (!matchedTargets[targetIdx]) {
            const card = document.createElement('div');
            card.className = `target-card bg-white rounded-xl p-3 shadow-md border-2 transition-all cursor-pointer hover:shadow-lg ${selectedTargetIndex === targetIdx ? 'ring-4 ring-green-500 border-green-500 scale-105' : 'border-gray-200 hover:border-green-300'}`;
            card.setAttribute('data-target-index', targetIdx);
            card.innerHTML = `<div class="flex-1"><p class="text-gray-800 text-sm md:text-base">${escapeHtml(gameData[targetIdx].target)}</p><p class="text-xs text-gray-400 mt-1"><i class="fas fa-lightbulb"></i> I-tap para itugma</p></div>`;
            card.addEventListener('click', (e) => { e.stopPropagation(); if (!isProcessing) handleTargetClick(targetIdx); });
            targetsContainer.appendChild(card);
        }
    }
    if (targetsContainer.children.length === 0) {
        targetsContainer.innerHTML = '<div class="text-center text-green-600 py-8 bg-green-50 rounded-xl"><i class="fas fa-check-circle text-4xl mb-2"></i><p>Lahat ng target ay naitugma na</p></div>';
    }
}

function escapeHtml(str) {
    if (!str) return '';
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

function handleItemClick(index) {
    if (matchedItems[index]) return;
    if (selectedTargetIndex !== null) {
        attemptMatch(index, selectedTargetIndex);
        selectedTargetIndex = null;
    } else {
        selectedItemIndex = index;
        renderGame();
        showFeedback(`Napili: "${gameData[index].item}"`, false, 'Pumili ng tamang target');
    }
}

function handleTargetClick(index) {
    if (matchedTargets[index]) return;
    if (selectedItemIndex !== null) {
        attemptMatch(selectedItemIndex, index);
        selectedItemIndex = null;
    } else {
        selectedTargetIndex = index;
        renderGame();
        showFeedback(`Napili: "${gameData[index].target}"`, false, 'Pumili ng item para itugma');
    }
}

function attemptMatch(itemIdx, targetIdx) {
    if (isProcessing) return;
    isProcessing = true;

    if (matchedItems[itemIdx] || matchedTargets[targetIdx]) {
        showFeedback('<?php echo t('item_matched'); ?>', true, '<?php echo t('try_other'); ?>');
        isProcessing = false;
        renderGame();
        return;
    }

    const isCorrect = (itemIdx === targetIdx);
    
    // Lock both items permanently
    matchedItems[itemIdx] = true;
    matchedTargets[targetIdx] = true;

    if (isCorrect) {
        currentScore++;
        document.getElementById('score').textContent = currentScore;
        showFeedback(`<?php echo t('correct_match'); ?>`, false, gameData[itemIdx].explanation);
    } else {
        showFeedback(`<?php echo t('wrong_match'); ?>`, true, `<?php echo t('correct_target'); ?> "${gameData[itemIdx].item}" ay: "${gameData[itemIdx].target}". ${gameData[itemIdx].explanation || ''}`);
    }

    selectedItemIndex = null;
    selectedTargetIndex = null;
    
    // Check if all items are matched
    if (matchedItems.every(v => v === true)) {
        showFeedback('<?php echo t('all_pairs_matched'); ?> <?php echo t('submit_score'); ?>.', false, '<?php echo t('submit_score'); ?>');
        const btn = document.getElementById('submitBtn');
        if (btn) { btn.disabled = false; btn.classList.remove('disabled:opacity-50'); }
    }
    renderGame();
    isProcessing = false;
}

function submitGameScore(gameType) {
    if (isSubmittingFlag) return;
    if (!matchedItems.every(v => v === true)) {
        showFeedback('<?php echo t('match_all_items'); ?>', true, '<?php echo t('complete_all_pairs'); ?>');
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
        body: JSON.stringify({ game_type: `agham-${gameType}`, score: currentScore, total: totalItems, xp: xp, coins: coins })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`<?php echo t('points'); ?>: ${currentScore}/${totalItems}\n<?php echo t('xp_gained'); ?>: +${xp}\n<?php echo t('coins_gained'); ?>: +${coins}\n\n<?php echo t('continue_learning'); ?>`);
            location.reload();
        } else {
            alert('Error: ' + (data.error || '<?php echo t('score_not_saved'); ?>'));
            if (btn) { btn.disabled = false; btn.textContent = '<?php echo t('submit'); ?> <?php echo t('points'); ?>'; }
            isSubmittingFlag = false;
        }
    })
    .catch(err => {
        alert('Network error: ' + err.message);
        if (btn) { btn.disabled = false; btn.textContent = '<?php echo t('submit'); ?> <?php echo t('points'); ?>'; }
        isSubmittingFlag = false;
    });
}

<?php if ($mode === 'siyensya'): ?>
gameData = <?php echo json_encode($cellData); ?>;
totalItems = gameData.length;
matchedItems = new Array(totalItems).fill(false);
matchedTargets = new Array(totalItems).fill(false);
initTargetOrder();
renderGame();
<?php elseif ($mode === 'likhaan'): ?>
gameData = <?php echo json_encode($ecosystemData); ?>;
totalItems = gameData.length;
matchedItems = new Array(totalItems).fill(false);
matchedTargets = new Array(totalItems).fill(false);
initTargetOrder();
renderGame();
<?php elseif ($mode === 'elemento'): ?>
gameData = <?php echo json_encode($elementData); ?>;
totalItems = gameData.length;
matchedItems = new Array(totalItems).fill(false);
matchedTargets = new Array(totalItems).fill(false);
initTargetOrder();
renderGame();
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>