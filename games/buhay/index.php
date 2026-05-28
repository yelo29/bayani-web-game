<?php
error_reporting(0);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/header.php';

// Load data files
$animals = json_decode(file_get_contents(__DIR__ . '/data/animals.json'), true);
$events = json_decode(file_get_contents(__DIR__ . '/data/events.json'), true);
$provinces = json_decode(file_get_contents(__DIR__ . '/data/provinces.json'), true);

// Get current mode from URL
$mode = $_GET['mode'] ?? 'menu';
?>

<main class="min-h-screen bg-gradient-to-br from-red-600 via-red-700 to-red-800 py-8 px-4">
    <div class="max-w-6xl mx-auto">
        <!-- Mode Selection Menu -->
        <?php if ($mode === 'menu'): ?>
        <div class="text-center mb-8">
            <h1 class="text-4xl md:text-5xl font-bold font-serif text-white mb-4">Buhay Pilipinas</h1>
            <p class="text-xl text-white/80 mb-2"><?php echo t('social_studies_grades'); ?> 4-10</p>
            <p class="text-white/60"><?php echo t('choose_mode_play'); ?></p>
            <!-- Wiki/Aralin Button -->
        <div class="mt-6 text-center">
            <button onclick="location.href='?mode=wiki'" class="inline-block bg-purple-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-purple-700 transition shadow-lg">
                <i class="fas fa-book-open mr-2"></i> <?php echo t('lesson_wiki'); ?>
            </button>
            <br><br>
            <p class="text-xs text-white/80"><?php echo t('read_info_here'); ?></p>
                <p class="text-xs text-white/80"><?php echo t('learn_game_answers'); ?></p>
        </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Mode 1: Mapa -->
            <div class="bg-white rounded-3xl shadow-2xl p-6 transform hover:scale-105 transition-transform duration-300 cursor-pointer" onclick="location.href='?mode=mapa'">
                <div class="text-center mb-4">
                    <div class="w-20 h-20 bg-red-100 rounded-full mx-auto mb-3 flex items-center justify-center">
                        <i class="fas fa-map-marked-alt text-red-600 text-4xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-red-600 mb-2"><?php echo t('map'); ?></h2>
                    <p class="text-gray-600 text-sm"><?php echo t('map_puzzle'); ?></p>
                </div>
                <div class="mb-4">
                    <div class="flex flex-wrap gap-2 justify-center">
                        <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs"><?php echo t('17_regions'); ?></span>
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs"><?php echo t('drag_drop'); ?></span>
                    </div>
                </div>
                <button class="w-full bg-red-600 text-white py-3 rounded-xl font-bold hover:bg-red-700 transition">
                    <i class="fas fa-play mr-2"></i> <?php echo t('play'); ?>
                </button>
            </div>

            <!-- Mode 2: Kasaysayan -->
            <div class="bg-white rounded-3xl shadow-2xl p-6 transform hover:scale-105 transition-transform duration-300 cursor-pointer" onclick="location.href='?mode=kasaysayan'">
                <div class="text-center mb-4">
                    <div class="w-20 h-20 bg-yellow-100 rounded-full mx-auto mb-3 flex items-center justify-center">
                        <i class="fas fa-history text-yellow-600 text-4xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-yellow-600 mb-2"><?php echo t('history'); ?></h2>
                    <p class="text-gray-600 text-sm"><?php echo t('history_timeline'); ?></p>
                </div>
                <div class="mb-4">
                    <div class="flex flex-wrap gap-2 justify-center">
                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs"><?php echo t('10_events'); ?></span>
                        <span class="px-2 py-1 bg-orange-100 text-orange-800 rounded-full text-xs"><?php echo t('chronological'); ?></span>
                    </div>
                </div>
                <button class="w-full bg-yellow-600 text-white py-3 rounded-xl font-bold hover:bg-yellow-700 transition">
                    <i class="fas fa-play mr-2"></i> <?php echo t('play'); ?>
                </button>
            </div>

            <!-- Mode 3: Hayop -->
            <div class="bg-white rounded-3xl shadow-2xl p-6 transform hover:scale-105 transition-transform duration-300 cursor-pointer" onclick="location.href='?mode=hayop'">
                <div class="text-center mb-4">
                    <div class="w-20 h-20 bg-green-100 rounded-full mx-auto mb-3 flex items-center justify-center">
                        <i class="fas fa-paw text-green-600 text-4xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-green-600 mb-2"><?php echo t('animal'); ?></h2>
                    <p class="text-gray-600 text-sm"><?php echo t('animal_match'); ?></p>
                </div>
                <div class="mb-4">
                    <div class="flex flex-wrap gap-2 justify-center">
                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs"><?php echo t('12_animals'); ?></span>
                        <span class="px-2 py-1 bg-teal-100 text-teal-800 rounded-full text-xs"><?php echo t('endemic_species'); ?></span>
                    </div>
                </div>
                <button class="w-full bg-green-600 text-white py-3 rounded-xl font-bold hover:bg-green-700 transition">
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

        <?php elseif ($mode === 'mapa'): ?>
        <!-- Mode 1: Map Puzzle -->
        <div class="bg-white rounded-3xl shadow-2xl p-4 md:p-6">
            <div class="flex flex-col md:flex-row justify-between items-center mb-4 md:mb-6 gap-4">
                <h2 class="text-xl md:text-2xl font-bold text-red-600">Mapa - Philippine Regions</h2>
                <a href="?mode=menu" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-times text-2xl"></i>
                </a>
            </div>

            <div class="bg-blue-50 border-l-4 border-blue-500 p-3 mb-4 rounded">
                <p class="text-sm text-blue-800"><i class="fas fa-info-circle mr-2"></i><strong><?php echo t('instructions'); ?>:</strong> <?php echo t('tap_region'); ?>. Pwede mong ilagay kahit saang lugar para matuto, pero tamang lokasyon lang ang may puntos.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6">
                <!-- Map Area with Drop Zones -->
                <div class="space-y-4">
                    <div class="text-center text-gray-500 text-sm md:text-base"><?php echo t('tap_region'); ?></div>

                    <!-- Luzon Drop Zone -->
                    <div class="bg-gray-100 rounded-2xl p-4 relative drop-zone-container" data-region="Luzon">
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <img src="https://www.philatlas.com/images/island-groups/luzonRegions.png" alt="Luzon Map" class="w-full h-24 object-cover rounded-lg opacity-30">
                        </div>
                        <div class="relative z-10 min-h-[100px] flex flex-wrap gap-2 items-center justify-center pt-2" id="luzonDropZone">
                            <span class="text-gray-400 text-sm">I-drop ang Luzon regions dito</span>
                        </div>
                    </div>

                    <!-- Visayas Drop Zone -->
                    <div class="bg-gray-100 rounded-2xl p-4 relative drop-zone-container" data-region="Visayas">
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <img src="https://www.philatlas.com/images/island-groups/visayasRegions.png" alt="Visayas Map" class="w-full h-24 object-cover rounded-lg opacity-30">
                        </div>
                        <div class="relative z-10 min-h-[100px] flex flex-wrap gap-2 items-center justify-center pt-2" id="visayasDropZone">
                            <span class="text-gray-400 text-sm">I-drop ang Visayas regions dito</span>
                        </div>
                    </div>

                    <!-- Mindanao Drop Zone -->
                    <div class="bg-gray-100 rounded-2xl p-4 relative drop-zone-container" data-region="Mindanao">
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <img src="https://www.philatlas.com/images/island-groups/mindanaoRegions.png" alt="Mindanao Map" class="w-full h-24 object-cover rounded-lg opacity-30">
                        </div>
                        <div class="relative z-10 min-h-[100px] flex flex-wrap gap-2 items-center justify-center pt-2" id="mindanaoDropZone">
                            <span class="text-gray-400 text-sm">I-drop ang Mindanao regions dito</span>
                        </div>
                    </div>
                </div>

                <!-- Region Labels -->
                <div class="bg-gray-50 rounded-2xl p-4">
                    <h3 class="font-bold text-gray-800 mb-4">Mga Rehiyon:</h3>
                    <div class="grid grid-cols-2 gap-2" id="regionLabels">
                        <?php foreach ($provinces as $province): ?>
                        <div class="bg-white p-2 md:p-3 rounded-lg shadow cursor-grab active:cursor-grabbing border-2 border-gray-200 hover:border-red-400 transition text-xs md:text-sm" draggable="true" data-region-id="<?php echo $province['id']; ?>" data-name="<?php echo $province['name']; ?>" data-island="<?php echo $province['island_group']; ?>">
                            <span class="font-medium text-gray-800"><?php echo $province['name']; ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="mt-4 md:mt-6 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-gray-600 text-sm md:text-base">
                    Score: <span id="score" class="font-bold text-red-600">0</span> / <?php echo count($provinces); ?>
                </div>
                <button onclick="submitMapScore()" class="w-full md:w-auto bg-red-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-red-700 transition">
                    <i class="fas fa-check mr-2"></i> I-submit
                </button>
            </div>
        </div>

        <?php elseif ($mode === 'kasaysayan'): ?>
        <!-- Mode 2: History Timeline -->
        <div class="bg-white rounded-3xl shadow-2xl p-4 md:p-6">
            <div class="flex flex-col md:flex-row justify-between items-center mb-4 md:mb-6 gap-4">
                <h2 class="text-xl md:text-2xl font-bold text-yellow-600">Kasaysayan - History Timeline</h2>
                <a href="?mode=menu" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-times text-2xl"></i>
                </a>
            </div>

            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-3 mb-4 rounded">
                <p class="text-sm text-yellow-800"><i class="fas fa-info-circle mr-2"></i><strong><?php echo t('instructions'); ?>:</strong> <?php echo t('tap_event'); ?>. Mula sa pinakauna hanggang sa pinakabago.</p>
            </div>

            <div class="bg-white rounded-2xl p-4 md:p-6 mb-4 md:mb-6 border-2 border-gray-200">
                <h3 class="font-bold text-gray-800 mb-4">Timeline (1521 - 1986):</h3>
                <div class="flex flex-wrap gap-3" id="timelineSlots">
                    <?php for ($i = 0; $i < count($events); $i++): ?>
                    <div class="timeline-slot bg-gray-50 border-2 border-dashed border-yellow-300 rounded-xl p-3 min-w-[100px] md:min-w-[140px] h-20 md:h-24 flex flex-col items-center justify-center text-xs text-gray-500 hover:border-yellow-400 transition" data-slot="<?php echo $i; ?>">
                        <div class="text-xs text-gray-400 font-bold mb-1"><?php echo $i + 1; ?></div>
                        <div class="text-xs text-gray-400">Drop event</div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="bg-gray-50 rounded-2xl p-4 mb-4 md:mb-6">
                <h3 class="font-bold text-gray-800 mb-4">Mga Kaganapan:</h3>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-2 md:gap-3" id="eventCards">
                    <?php foreach ($events as $index => $event): ?>
                    <div class="bg-white p-2 md:p-3 rounded-lg shadow cursor-grab active:cursor-grabbing border-2 border-gray-200 hover:border-yellow-400 transition text-xs md:text-sm" draggable="true" data-year="<?php echo $event['year']; ?>" data-event="<?php echo $event['event']; ?>" data-index="<?php echo $index; ?>">
                        <div class="text-xs text-gray-500 mb-1 font-bold"><?php echo $event['year']; ?></div>
                        <div class="text-xs md:text-sm font-medium text-gray-800"><?php echo $event['event']; ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-gray-600 text-sm md:text-base">
                    Score: <span id="score" class="font-bold text-yellow-600">0</span> / <?php echo count($events); ?>
                </div>
                <button onclick="submitHistoryScore()" class="w-full md:w-auto bg-yellow-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-yellow-700 transition">
                    <i class="fas fa-check mr-2"></i> I-submit
                </button>
            </div>
        </div>

        <?php elseif ($mode === 'hayop'): ?>
        <!-- Mode 3: Animal Match -->
        <div class="bg-white rounded-3xl shadow-2xl p-4 md:p-6">
            <div class="flex flex-col md:flex-row justify-between items-center mb-4 md:mb-6 gap-4">
                <h2 class="text-xl md:text-2xl font-bold text-green-600">Hayop - Philippine Animals</h2>
                <a href="?mode=menu" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-times text-2xl"></i>
                </a>
            </div>

            <div class="bg-green-50 border-l-4 border-green-500 p-3 mb-4 rounded">
                <p class="text-sm text-green-800"><i class="fas fa-info-circle mr-2"></i><strong><?php echo t('instructions'); ?>:</strong> <?php echo t('tap_animal'); ?>. Pwede mong ilagay kahit saang rehiyon para matuto, pero tamang rehiyon lang ang may puntos.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6">
                <!-- Map Regions -->
                <div class="bg-gray-100 rounded-2xl p-4">
                    <h3 class="font-bold text-gray-800 mb-4">I-drag sa tamang rehiyon:</h3>
                    <div class="space-y-2 md:space-y-3" id="regionDropZones">
                        <div class="bg-blue-200 p-3 md:p-4 rounded-lg border-2 border-blue-400 min-h-[70px] md:min-h-[80px] flex items-center justify-center" data-region="Luzon">
                            <span class="font-bold text-blue-800 text-sm md:text-base">Luzon</span>
                        </div>
                        <div class="bg-yellow-200 p-3 md:p-4 rounded-lg border-2 border-yellow-400 min-h-[70px] md:min-h-[80px] flex items-center justify-center" data-region="Visayas">
                            <span class="font-bold text-yellow-800 text-sm md:text-base">Visayas</span>
                        </div>
                        <div class="bg-green-200 p-3 md:p-4 rounded-lg border-2 border-green-400 min-h-[70px] md:min-h-[80px] flex items-center justify-center" data-region="Mindanao">
                            <span class="font-bold text-green-800 text-sm md:text-base">Mindanao</span>
                        </div>
                        <div class="bg-purple-200 p-3 md:p-4 rounded-lg border-2 border-purple-400 min-h-[70px] md:min-h-[80px] flex items-center justify-center" data-region="Bohol">
                            <span class="font-bold text-purple-800 text-sm md:text-base">Bohol</span>
                        </div>
                        <div class="bg-orange-200 p-3 md:p-4 rounded-lg border-2 border-orange-400 min-h-[70px] md:min-h-[80px] flex items-center justify-center" data-region="Mindoro">
                            <span class="font-bold text-orange-800 text-sm md:text-base">Mindoro</span>
                        </div>
                        <div class="bg-teal-200 p-3 md:p-4 rounded-lg border-2 border-teal-400 min-h-[70px] md:min-h-[80px] flex items-center justify-center" data-region="Palawan">
                            <span class="font-bold text-teal-800 text-sm md:text-base">Palawan</span>
                        </div>
                    </div>
                </div>

                <!-- Animals -->
                <div class="bg-gray-50 rounded-2xl p-4">
                    <h3 class="font-bold text-gray-800 mb-4">Mga Hayop:</h3>
                    <div class="grid grid-cols-2 gap-2 md:gap-3" id="animalCards">
                        <?php foreach ($animals as $index => $animal): ?>
                        <div class="bg-white p-3 md:p-4 rounded-lg shadow cursor-grab active:cursor-grabbing border-2 border-gray-200 hover:border-green-400 transition text-center" draggable="true" data-animal="<?php echo $animal['name']; ?>" data-region="<?php echo $animal['region']; ?>" data-index="<?php echo $index; ?>">
                            <div class="text-3xl md:text-4xl mb-1 md:mb-2"><?php echo $animal['emoji']; ?></div>
                            <div class="text-xs md:text-sm font-medium text-gray-800"><?php echo $animal['name']; ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="mt-4 md:mt-6 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-gray-600 text-sm md:text-base">
                    Score: <span id="score" class="font-bold text-green-600">0</span> / <?php echo count($animals); ?>
                </div>
                <button onclick="submitAnimalScore()" class="w-full md:w-auto bg-green-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-green-700 transition">
                    <i class="fas fa-check mr-2"></i> I-submit
                </button>
            </div>
        </div>

        <?php elseif ($mode === 'wiki'): ?>
        <!-- Wiki/Aralin Mode -->
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
                <button onclick="showWikiTab('mapa')" id="tab-mapa" class="wiki-tab px-4 py-2 rounded-lg font-bold bg-red-600 text-white"><?php echo t('map'); ?></button>
                <button onclick="showWikiTab('kasaysayan')" id="tab-kasaysayan" class="wiki-tab px-4 py-2 rounded-lg font-bold bg-gray-200 text-gray-700 hover:bg-gray-300"><?php echo t('history'); ?></button>
                <button onclick="showWikiTab('hayop')" id="tab-hayop" class="wiki-tab px-4 py-2 rounded-lg font-bold bg-gray-200 text-gray-700 hover:bg-gray-300"><?php echo t('animal'); ?></button>
            </div>

            <!-- Mapa Content -->
            <div id="wiki-mapa" class="wiki-content">
                <h3 class="font-bold text-gray-800 mb-4 text-lg">Mga Rehiyon ng Pilipinas</h3>
                <div class="space-y-3">
                    <div class="bg-blue-50 p-3 rounded-lg border-l-4 border-blue-500">
                        <h4 class="font-bold text-blue-800 mb-2">Luzon (North)</h4>
                        <p class="text-sm text-gray-700 mb-2">Ang pinakamalaking isla sa hilaga. Dito matatagpuan ang Metro Manila.</p>
                        <div class="text-sm text-gray-600">
                            <strong>Mga Rehiyon:</strong> Ilocos Region, Cagayan Valley, Central Luzon, CALABARZON, MIMAROPA, Bicol Region, Cordillera, National Capital Region
                        </div>
                    </div>
                    <div class="bg-yellow-50 p-3 rounded-lg border-l-4 border-yellow-500">
                        <h4 class="font-bold text-yellow-800 mb-2">Visayas (Central)</h4>
                        <p class="text-sm text-gray-700 mb-2">Ang mga isla sa gitna. Kilala sa mga beach at festivals.</p>
                        <div class="text-sm text-gray-600">
                            <strong>Mga Rehiyon:</strong> Western Visayas, Central Visayas, Eastern Visayas
                        </div>
                    </div>
                    <div class="bg-green-50 p-3 rounded-lg border-l-4 border-green-500">
                        <h4 class="font-bold text-green-800 mb-2">Mindanao (South)</h4>
                        <p class="text-sm text-gray-700 mb-2">Ang pangalawang pinakamalaking isla sa timog. Mayaman sa kultura at自然资源.</p>
                        <div class="text-sm text-gray-600">
                            <strong>Mga Rehiyon:</strong> Zamboanga Peninsula, Northern Mindanao, Davao Region, SOCCSKSARGEN, Caraga, ARMM, BARMM
                        </div>
                    </div>
                </div>

                <h3 class="font-bold text-gray-800 mb-4 mt-6 text-lg">Listahan ng mga Rehiyon</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <?php
                    $luzonRegions = ['Ilocos Region', 'Cagayan Valley', 'Central Luzon', 'CALABARZON', 'MIMAROPA', 'Bicol Region', 'Cordillera', 'NCR'];
                    $visayasRegions = ['Western Visayas', 'Central Visayas', 'Eastern Visayas'];
                    $mindanaoRegions = ['Zamboanga Peninsula', 'Northern Mindanao', 'Davao Region', 'SOCCSKSARGEN', 'Caraga', 'ARMM', 'BARMM'];

                    foreach ($provinces as $province) {
                        $island = $province['island_group'];
                        $color = 'blue';
                        if ($island === 'Visayas') $color = 'yellow';
                        if ($island === 'Mindanao') $color = 'green';
                    ?>
                    <div class="bg-<?php echo $color; ?>-100 p-2 rounded text-sm">
                        <span class="font-bold text-<?php echo $color; ?>-800"><?php echo $province['name']; ?></span>
                        <span class="text-gray-600 text-xs ml-2">(<?php echo $island; ?>)</span>
                    </div>
                    <?php } ?>
                </div>
            </div>

            <!-- Kasaysayan Content -->
            <div id="wiki-kasaysayan" class="wiki-content hidden">
                <h3 class="font-bold text-gray-800 mb-4 text-lg">Timeline ng Kasaysayan ng Pilipinas</h3>
                <div class="space-y-3">
                    <?php foreach ($events as $event): ?>
                    <div class="bg-yellow-50 p-3 rounded-lg border-l-4 border-yellow-500">
                        <div class="font-bold text-yellow-800 mb-1"><?php echo $event['year']; ?> - <?php echo $event['event']; ?></div>
                        <p class="text-sm text-gray-700"><?php echo $event['description']; ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Hayop Content -->
            <div id="wiki-hayop" class="wiki-content hidden">
                <h3 class="font-bold text-gray-800 mb-4 text-lg">Mga Endemic na Hayop ng Pilipinas</h3>
                <p class="text-sm text-gray-600 mb-4">Ang mga hayop na ito ay matatagpuan lang sa Pilipinas at hindi sa ibang bansa.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <?php foreach ($animals as $animal): ?>
                    <div class="bg-green-50 p-3 rounded-lg border-l-4 border-green-500">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-3xl"><?php echo $animal['emoji']; ?></span>
                            <div>
                                <div class="font-bold text-green-800"><?php echo $animal['name']; ?></div>
                                <div class="text-xs text-gray-600"><?php echo $animal['region']; ?></div>
                            </div>
                        </div>
                        <p class="text-sm text-gray-700 mb-2"><strong>Habitat:</strong> <?php echo $animal['habitat']; ?></p>
                        <p class="text-xs text-gray-600 italic"><?php echo $animal['description']; ?></p>
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
    // Hide all content
    document.querySelectorAll('.wiki-content').forEach(content => {
        content.classList.add('hidden');
    });

    // Reset all tabs
    document.querySelectorAll('.wiki-tab').forEach(tab => {
        tab.classList.remove('bg-red-600', 'bg-yellow-600', 'bg-green-600', 'text-white');
        tab.classList.add('bg-gray-200', 'text-gray-700');
    });

    // Show selected content
    document.getElementById('wiki-' + tabName).classList.remove('hidden');

    // Highlight selected tab
    const selectedTab = document.getElementById('tab-' + tabName);
    selectedTab.classList.remove('bg-gray-200', 'text-gray-700');

    if (tabName === 'mapa') {
        selectedTab.classList.add('bg-red-600', 'text-white');
    } else if (tabName === 'kasaysayan') {
        selectedTab.classList.add('bg-yellow-600', 'text-white');
    } else if (tabName === 'hayop') {
        selectedTab.classList.add('bg-green-600', 'text-white');
    }
}

// Drag and Drop functionality
let score = 0;
let totalItems = 0;
let droppedItems = new Set();
let placedItems = new Map(); // Track which items are placed where
let zoneContents = new Map(); // Track contents of each zone for piling
let isSubmitting = false; // Prevent multiple submissions

// Touch support for mobile - tap to select, tap to drop
let selectedItem = null;
let selectedData = null;

function handleTap(e) {
    e.preventDefault();

    // Check if tapping on a draggable item
    if (this.hasAttribute('draggable') && !droppedItems.has(this.dataset.regionId || this.dataset.index)) {
        // If tapping the same item, deselect it
        if (selectedItem === this) {
            selectedItem.classList.remove('ring-4', 'ring-blue-500', 'scale-105');
            selectedItem = null;
            selectedData = null;
            hideSelectionIndicator();
            return;
        }

        // Deselect previous selection
        if (selectedItem) {
            selectedItem.classList.remove('ring-4', 'ring-blue-500', 'scale-105');
        }

        // Select this item
        selectedItem = this;
        selectedItem.classList.add('ring-4', 'ring-blue-500', 'scale-105');

        // Store data
        if (this.dataset.regionId) {
            selectedData = {
                id: this.dataset.regionId,
                name: this.dataset.name,
                island: this.dataset.island,
                type: 'mapa'
            };
        } else if (this.dataset.year) {
            selectedData = {
                index: this.dataset.index,
                year: this.dataset.year,
                event: this.dataset.event,
                type: 'kasaysayan'
            };
        } else if (this.dataset.animal) {
            selectedData = {
                index: this.dataset.index,
                animal: this.dataset.animal,
                region: this.dataset.region,
                emoji: this.querySelector('.text-3xl, .text-4xl').textContent,
                type: 'hayop'
            };
        }

        // Show selection indicator
        showSelectionIndicator(selectedData.name || selectedData.event || selectedData.animal);

        // Scroll to top to show drop zones
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

function showSelectionIndicator(itemName) {
    // Remove existing indicator
    hideSelectionIndicator();

    // Create indicator
    const indicator = document.createElement('div');
    indicator.id = 'selection-indicator';
    indicator.className = 'fixed top-4 left-1/2 transform -translate-x-1/2 bg-blue-600 text-white px-4 py-2 rounded-full shadow-lg z-50 flex items-center gap-2';
    indicator.innerHTML = `
        <span class="text-sm">Selected: <strong>${itemName}</strong></span>
        <button onclick="cancelSelection()" class="ml-2 bg-white/20 hover:bg-white/30 rounded-full w-6 h-6 flex items-center justify-center">
            <i class="fas fa-times text-xs"></i>
        </button>
    `;
    document.body.appendChild(indicator);
}

function hideSelectionIndicator() {
    const indicator = document.getElementById('selection-indicator');
    if (indicator) {
        indicator.remove();
    }
}

function cancelSelection() {
    if (selectedItem) {
        selectedItem.classList.remove('ring-4', 'ring-blue-500', 'scale-105');
    }
    selectedItem = null;
    selectedData = null;
    hideSelectionIndicator();
}

function handleDropZoneTap(e) {
    e.preventDefault();

    if (selectedItem && selectedData) {
        handleDrop(this, selectedData);

        // Deselect after drop
        selectedItem.classList.remove('ring-4', 'ring-blue-500', 'scale-105');
        selectedItem = null;
        selectedData = null;
        hideSelectionIndicator();
    }
}

function handleDrop(dropZone, data) {
    if (data.type === 'mapa') {
        const targetRegion = dropZone.dataset.region;
        if (!droppedItems.has(data.id)) {
            droppedItems.add(data.id);
            placedItems.set(data.id, targetRegion);

            const label = document.querySelector(`[data-region-id="${data.id}"]`);
            if (label) {
                label.draggable = false;
                label.classList.add('opacity-50', 'cursor-not-allowed');
                label.classList.remove('cursor-grab', 'active:cursor-grabbing');
            }

            let dropZoneId = '';
            if (targetRegion === 'Luzon') dropZoneId = 'luzonDropZone';
            if (targetRegion === 'Visayas') dropZoneId = 'visayasDropZone';
            if (targetRegion === 'Mindanao') dropZoneId = 'mindanaoDropZone';

            const zoneDiv = document.getElementById(dropZoneId);
            if (zoneDiv) {
                const placeholder = zoneDiv.querySelector('span.text-gray-400');
                if (placeholder) placeholder.remove();

                if (data.island === targetRegion) {
                    score++;
                    document.getElementById('score').textContent = score;
                    dropZone.style.backgroundColor = '#D1FAE5';
                    dropZone.style.borderColor = '#10B981';
                } else {
                    dropZone.style.backgroundColor = '#FEE2E2';
                    dropZone.style.borderColor = '#EF4444';
                }

                if (!zoneContents.has(targetRegion)) zoneContents.set(targetRegion, []);
                zoneContents.get(targetRegion).push(data.name);

                const badge = document.createElement('div');
                badge.className = 'px-2 py-1 rounded text-xs font-medium ' +
                    (data.island === targetRegion ? 'bg-green-500 text-white' : 'bg-red-500 text-white opacity-70');
                badge.textContent = (data.island === targetRegion ? '✓ ' : '✗ ') + data.name.substring(0, 12);
                zoneDiv.appendChild(badge);

                updateSubmitButton();
            }
        }
    } else if (data.type === 'kasaysayan') {
        const slotIndex = parseInt(dropZone.dataset.slot);
        const eventsData = <?php echo json_encode($events ?? []); ?>;

        if (!droppedItems.has(data.index)) {
            droppedItems.add(data.index);
            placedItems.set(data.index, slotIndex);

            const card = document.querySelector(`[data-index="${data.index}"]`);
            if (card) {
                card.draggable = false;
                card.classList.add('opacity-50', 'cursor-not-allowed');
                card.classList.remove('cursor-grab', 'active:cursor-grabbing');
            }

            dropZone.innerHTML = `
                <div class="text-center">
                    <div class="text-xs font-bold text-yellow-600">${data.year}</div>
                    <div class="text-xs text-gray-800">${data.event.substring(0, 18)}...</div>
                </div>
            `;

            const correctYear = eventsData[slotIndex]?.year;
            if (parseInt(data.year) === correctYear) {
                score++;
                document.getElementById('score').textContent = score;
                dropZone.style.backgroundColor = '#D1FAE5';
                dropZone.style.borderColor = '#10B981';
                dropZone.style.borderStyle = 'solid';
            } else {
                dropZone.style.backgroundColor = '#FEE2E2';
                dropZone.style.borderColor = '#EF4444';
                dropZone.style.borderStyle = 'solid';
            }

            updateSubmitButton();
        }
    } else if (data.type === 'hayop') {
        const targetRegion = dropZone.dataset.region;

        if (!droppedItems.has(data.index)) {
            droppedItems.add(data.index);
            placedItems.set(data.index, targetRegion);

            const card = document.querySelector(`[data-index="${data.index}"]`);
            if (card) {
                card.draggable = false;
                card.classList.add('opacity-50', 'cursor-not-allowed');
                card.classList.remove('cursor-grab', 'active:cursor-grabbing');
            }

            if (data.region === targetRegion) {
                score++;
                document.getElementById('score').textContent = score;
                dropZone.style.backgroundColor = '#10B981';
                dropZone.style.borderColor = '#065F46';
            } else {
                dropZone.style.backgroundColor = '#EF4444';
                dropZone.style.borderColor = '#991B1B';
                setTimeout(() => {
                    dropZone.style.backgroundColor = '#FCA5A5';
                    dropZone.style.borderColor = '#991B1B';
                }, 500);
            }

            if (!zoneContents.has(targetRegion)) {
                zoneContents.set(targetRegion, []);
                dropZone.innerHTML = '<div class="flex flex-wrap gap-1 justify-center items-center animal-container"></div>';
            }
            zoneContents.get(targetRegion).push({ emoji: data.emoji, name: data.animal });

            const container = dropZone.querySelector('.animal-container');
            if (container) {
                const animalSpan = document.createElement('span');
                animalSpan.className = 'text-lg';
                animalSpan.textContent = data.emoji;
                if (data.region !== targetRegion) {
                    animalSpan.style.opacity = '0.5';
                }
                container.appendChild(animalSpan);
            }

            updateSubmitButton();
        }
    }
}

// Add touch event listeners for mobile tap-to-select
document.addEventListener('DOMContentLoaded', () => {
    const draggables = document.querySelectorAll('[draggable="true"]');
    draggables.forEach(el => {
        el.addEventListener('click', handleTap);
    });

    // Add tap listeners to drop zones
    const dropZones = document.querySelectorAll('.drop-zone-container, .timeline-slot, #regionDropZones > div');
    dropZones.forEach(zone => {
        zone.addEventListener('click', handleDropZoneTap);
    });
});

// Update submit button state
function updateSubmitButton() {
    const submitBtn = document.querySelector('button[onclick^="submit"]');
    if (submitBtn) {
        // Enable submit button only if ALL items have been placed
        if (droppedItems.size === totalItems) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }
}

// Initialize submit button state
document.addEventListener('DOMContentLoaded', updateSubmitButton);

<?php if ($mode === 'mapa'): ?>
totalItems = <?php echo count($provinces); ?>;

// Map drag and drop
const regionLabels = document.querySelectorAll('#regionLabels > div');
const mapDropZones = document.querySelectorAll('.drop-zone-container');

regionLabels.forEach(label => {
    label.addEventListener('dragstart', (e) => {
        e.dataTransfer.setData('text/plain', JSON.stringify({
            id: label.dataset.regionId,
            name: label.dataset.name,
            island: label.dataset.island
        }));
        label.style.opacity = '0.5';
    });

    label.addEventListener('dragend', (e) => {
        label.style.opacity = '1';
    });
});

mapDropZones.forEach(zone => {
    zone.addEventListener('dragover', (e) => {
        e.preventDefault();
        zone.style.borderColor = '#10B981';
        zone.style.borderWidth = '3px';
    });

    zone.addEventListener('dragleave', (e) => {
        zone.style.borderColor = '';
        zone.style.borderWidth = '';
    });

    zone.addEventListener('drop', (e) => {
        e.preventDefault();
        zone.style.borderColor = '';
        zone.style.borderWidth = '';

        const data = JSON.parse(e.dataTransfer.getData('text/plain'));
        const targetRegion = zone.dataset.region;

        // Allow dropping even on wrong locations for learning
        if (!droppedItems.has(data.id)) {
            droppedItems.add(data.id);
            placedItems.set(data.id, targetRegion);

            // Disable the label
            const label = document.querySelector(`[data-region-id="${data.id}"]`);
            if (label) {
                label.draggable = false;
                label.classList.add('opacity-50', 'cursor-not-allowed');
                label.classList.remove('cursor-grab', 'active:cursor-grabbing');
            }

            // Get the correct drop zone div
            let dropZoneId = '';
            if (targetRegion === 'Luzon') dropZoneId = 'luzonDropZone';
            if (targetRegion === 'Visayas') dropZoneId = 'visayasDropZone';
            if (targetRegion === 'Mindanao') dropZoneId = 'mindanaoDropZone';

            const dropZone = document.getElementById(dropZoneId);
            if (dropZone) {
                // Remove placeholder text if it exists
                const placeholder = dropZone.querySelector('span.text-gray-400');
                if (placeholder) {
                    placeholder.remove();
                }

                if (data.island === targetRegion) {
                    // Correct match - give points
                    score++;
                    document.getElementById('score').textContent = score;
                    zone.style.backgroundColor = '#D1FAE5';
                    zone.style.borderColor = '#10B981';
                } else {
                    // Wrong match - no points, but still place item for learning
                    zone.style.backgroundColor = '#FEE2E2';
                    zone.style.borderColor = '#EF4444';
                }

                // Add to zone contents for piling
                if (!zoneContents.has(targetRegion)) {
                    zoneContents.set(targetRegion, []);
                }
                zoneContents.get(targetRegion).push(data.name);

                // Create region badge
                const badge = document.createElement('div');
                badge.className = 'px-2 py-1 rounded text-xs font-medium ' +
                    (data.island === targetRegion ? 'bg-green-500 text-white' : 'bg-red-500 text-white opacity-70');
                badge.textContent = (data.island === targetRegion ? '✓ ' : '✗ ') + data.name.substring(0, 12);
                dropZone.appendChild(badge);

                // Update submit button state
                updateSubmitButton();
            }
        }
    });
});

function submitMapScore() {
    if (score > totalItems) score = totalItems;
    saveScore('buhay-mapa', score, totalItems);
}

<?php elseif ($mode === 'kasaysayan'): ?>
totalItems = <?php echo count($events); ?>;

// History timeline drag and drop
const eventCards = document.querySelectorAll('#eventCards > div');
const timelineSlots = document.querySelectorAll('.timeline-slot');
const eventsData = <?php echo json_encode($events); ?>;

eventCards.forEach(card => {
    card.addEventListener('dragstart', (e) => {
        e.dataTransfer.setData('text/plain', JSON.stringify({
            index: card.dataset.index,
            year: card.dataset.year,
            event: card.dataset.event
        }));
        card.style.opacity = '0.5';
    });

    card.addEventListener('dragend', (e) => {
        card.style.opacity = '1';
    });
});

timelineSlots.forEach(slot => {
    slot.addEventListener('dragover', (e) => {
        e.preventDefault();
        // Only highlight if slot is empty
        if (!slot.querySelector('.text-center')) {
            slot.style.borderColor = '#10B981';
            slot.style.backgroundColor = '#D1FAE5';
        }
    });

    slot.addEventListener('dragleave', (e) => {
        // Only clear styles if slot is empty
        if (!slot.querySelector('.text-center')) {
            slot.style.borderColor = '';
            slot.style.backgroundColor = '';
        }
    });

    slot.addEventListener('drop', (e) => {
        e.preventDefault();
        // Don't clear styles on drop - let the result set them

        const data = JSON.parse(e.dataTransfer.getData('text/plain'));
        const slotIndex = parseInt(slot.dataset.slot);

        if (!droppedItems.has(data.index)) {
            droppedItems.add(data.index);
            placedItems.set(data.index, slotIndex);

            // Disable the card
            const card = document.querySelector(`[data-index="${data.index}"]`);
            if (card) {
                card.draggable = false;
                card.classList.add('opacity-50', 'cursor-not-allowed');
                card.classList.remove('cursor-grab', 'active:cursor-grabbing');
            }

            // Show event in slot
            slot.innerHTML = `
                <div class="text-center">
                    <div class="text-xs font-bold text-yellow-600">${data.year}</div>
                    <div class="text-xs text-gray-800">${data.event.substring(0, 18)}...</div>
                </div>
            `;

            // Check if correct order
            const correctYear = eventsData[slotIndex].year;
            if (parseInt(data.year) === correctYear) {
                score++;
                document.getElementById('score').textContent = score;
                slot.style.backgroundColor = '#D1FAE5';
                slot.style.borderColor = '#10B981';
                slot.style.borderStyle = 'solid';
            } else {
                slot.style.backgroundColor = '#FEE2E2';
                slot.style.borderColor = '#EF4444';
                slot.style.borderStyle = 'solid';
            }

            // Update submit button state
            updateSubmitButton();
        }
    });
});

function submitHistoryScore() {
    if (score > totalItems) score = totalItems;
    saveScore('buhay-kasaysayan', score, totalItems);
}

<?php elseif ($mode === 'hayop'): ?>
totalItems = <?php echo count($animals); ?>;

// Animal drag and drop
const animalCards = document.querySelectorAll('#animalCards > div');
const animalDropZones = document.querySelectorAll('#regionDropZones > div');

animalCards.forEach(card => {
    card.addEventListener('dragstart', (e) => {
        e.dataTransfer.setData('text/plain', JSON.stringify({
            index: card.dataset.index,
            animal: card.dataset.animal,
            region: card.dataset.region,
            emoji: card.querySelector('.text-3xl, .text-4xl').textContent
        }));
        card.style.opacity = '0.5';
    });

    card.addEventListener('dragend', (e) => {
        card.style.opacity = '1';
    });
});

animalDropZones.forEach(zone => {
    zone.addEventListener('dragover', (e) => {
        e.preventDefault();
        zone.style.borderColor = '#10B981';
        zone.style.transform = 'scale(1.02)';
    });

    zone.addEventListener('dragleave', (e) => {
        zone.style.borderColor = '';
        zone.style.transform = '';
    });

    zone.addEventListener('drop', (e) => {
        e.preventDefault();
        zone.style.borderColor = '';
        zone.style.transform = '';

        const data = JSON.parse(e.dataTransfer.getData('text/plain'));
        const targetRegion = zone.dataset.region;

        // Allow dropping even on wrong locations for learning
        if (!droppedItems.has(data.index)) {
            droppedItems.add(data.index);
            placedItems.set(data.index, targetRegion);

            // Disable the card
            const card = document.querySelector(`[data-index="${data.index}"]`);
            if (card) {
                card.draggable = false;
                card.classList.add('opacity-50', 'cursor-not-allowed');
                card.classList.remove('cursor-grab', 'active:cursor-grabbing');
            }

            if (data.region === targetRegion) {
                // Correct match - give points
                score++;
                document.getElementById('score').textContent = score;
                zone.style.backgroundColor = '#10B981';
                zone.style.borderColor = '#065F46';
            } else {
                // Wrong match - no points, but still place item for learning
                zone.style.backgroundColor = '#EF4444';
                zone.style.borderColor = '#991B1B';
                setTimeout(() => {
                    zone.style.backgroundColor = '#FCA5A5';
                    zone.style.borderColor = '#991B1B';
                }, 500);
            }

            // Add to zone contents for piling
            if (!zoneContents.has(targetRegion)) {
                zoneContents.set(targetRegion, []);
                // Initialize zone with container
                zone.innerHTML = '<div class="flex flex-wrap gap-1 justify-center items-center animal-container"></div>';
            }
            zoneContents.get(targetRegion).push({ emoji: data.emoji, name: data.animal });

            // Append new animal to container
            const container = zone.querySelector('.animal-container');
            if (container) {
                const animalSpan = document.createElement('span');
                animalSpan.className = 'text-lg';
                animalSpan.textContent = data.emoji;
                if (data.region !== targetRegion) {
                    animalSpan.style.opacity = '0.5'; // Dim wrong matches
                }
                container.appendChild(animalSpan);
            }

            // Update submit button state
            updateSubmitButton();
        }
    });
});

function submitAnimalScore() {
    if (score > totalItems) score = totalItems;
    saveScore('buhay-hayop', score, totalItems);
}
<?php endif; ?>

function saveScore(gameType, score, total) {
    if (isSubmitting) return; // Prevent multiple submissions
    isSubmitting = true;

    const xp = Math.floor((score / total) * 50);
    const coins = Math.floor((score / total) * 30);

    // Disable all submit buttons
    const submitButtons = document.querySelectorAll('button[onclick^="submit"]');
    submitButtons.forEach(btn => {
        btn.disabled = true;
        btn.textContent = 'Isinasasa...';
    });

    fetch('../../api/save_game_score.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ game_type: gameType, score: score, total: total, xp: xp, coins: coins })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Score: ${score}/${total}\nXP: +${xp}\nCoins: +${coins}`);
            // Reload current page instead of going to maglaro.php
            location.reload();
        } else {
            alert('Error saving score: ' + (data.error || 'Unknown error'));
            // Re-enable buttons on error
            submitButtons.forEach(btn => {
                btn.disabled = false;
                btn.textContent = 'Isumit';
            });
            isSubmitting = false;
        }
    })
    .catch(err => {
        alert('Error: ' + err.message);
        // Re-enable buttons on error
        submitButtons.forEach(btn => {
            btn.disabled = false;
            btn.textContent = 'Isumit';
        });
        isSubmitting = false;
    });
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
