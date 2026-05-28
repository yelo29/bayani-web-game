<?php
require_once 'includes/header.php';
?>

<main class="min-h-screen bg-gradient-to-br from-[#0038A8] via-[#0052A3] to-[#0066CC] py-12 px-4">
    <div class="max-w-6xl mx-auto">
        <!-- Hero Section -->
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-6xl font-bold font-serif text-white mb-4"><?php echo t('choose_mode'); ?></h1>
            <p class="text-xl text-white/80"><?php echo t('tagline'); ?></p>
        </div>

        <!-- Game Mode Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Quiz Card -->
            <div class="bg-white rounded-3xl shadow-2xl p-8 transform hover:scale-105 transition-transform duration-300">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-blue-100 rounded-full mx-auto mb-3 flex items-center justify-center">
                            <i class="fa-solid fa-question text-blue-600 text-3xl"></i>
                        </div>
                    <h2 class="text-3xl font-bold text-[#0038A8] mb-2"> <?php echo t('quiz_title'); ?></h2>
                    <p class="text-gray-600"><?php echo t('quiz_desc'); ?></p>
                </div>

                <div class="mb-6">
                    <h3 class="font-bold text-gray-800 mb-3"><?php echo t('categories'); ?>:</h3>
                    <div class="flex flex-wrap gap-2">
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">Kasaysayan</span>
                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm">Kultura</span>
                        <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm">Heograpiya</span>
                        <span class="px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-sm">Sining</span>
                    </div>
                </div>

                <a href="piliin.php" class="block w-full bg-[#0038A8] text-white text-center py-4 rounded-xl font-bold text-lg hover:bg-[#002870] transition">
                    <i class="fas fa-play mr-2"></i> <?php echo t('magsimula'); ?>
                </a>
            </div>

            <!-- Battle Card -->
            <div class="bg-white rounded-3xl shadow-2xl p-8 transform hover:scale-105 transition-transform duration-300">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-red-100 rounded-full mx-auto mb-3 flex items-center justify-center">
                            <i class="fa-solid fa-person-military-rifle text-red-600 text-3xl"></i>
                        </div>
                    <h2 class="text-3xl font-bold text-[#CE1126] mb-2"> <?php echo t('battle_title'); ?></h2>
                    <p class="text-gray-600"><?php echo t('battle_desc'); ?></p>
                </div>

                <div class="mb-6">
                    <h3 class="font-bold text-gray-800 mb-3"><?php echo t('regions'); ?>:</h3>
                    <div class="flex flex-wrap gap-2">
                        <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm">Maynila</span>
                        <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm">Cebu</span>
                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm">Davao</span>
                        <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm">Vigan</span>
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">Zamboanga</span>
                    </div>
                </div>

                <a href="mundo.php" class="block w-full bg-[#CE1126] text-white text-center py-4 rounded-xl font-bold text-lg hover:bg-[#A00D1A] transition">
                    <i class="fas fa-play mr-2"></i> <?php echo t('magsimula'); ?>
                </a>
            </div>

            <!-- Kwento Card -->
            <div class="bg-white rounded-3xl shadow-2xl p-8 transform hover:scale-105 transition-transform duration-300">
                <div class="text-center mb-6">
                  <div class="w-16 h-16 bg-purple-100 rounded-full mx-auto mb-3 flex items-center justify-center">
                            <i class="fa-solid fa-book text-purple-600 text-3xl"></i>
                        </div>
                    <h2 class="text-3xl font-bold text-purple-600 mb-2"><?php echo t('kwento_mode'); ?></h2>
                    <p class="text-gray-600"><?php echo t('2d_story_rpg'); ?></p>
                </div>

                <div class="mb-6">
                    <h3 class="font-bold text-gray-800 mb-3"><?php echo t('chapters'); ?>:</h3>
                    <div class="flex flex-wrap gap-2">
                        <span class="px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-sm"><?php echo t('spanish_colonial'); ?></span>
                        <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm"><?php echo t('revolution'); ?></span>
                        <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm"><?php echo t('world_war_ii'); ?></span>
                    </div>
                </div>

                <a href="kwento/index.php" class="block w-full bg-purple-600 text-white text-center py-4 rounded-xl font-bold text-lg hover:bg-purple-700 transition">
                    <i class="fas fa-play mr-2"></i> <?php echo t('magsimula'); ?>
                </a>
            </div>
        </div>

        <!-- Educational Games Section -->
        <div class="mt-16">
         

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Handa Ka Na Card -->
                <div class="bg-white rounded-3xl shadow-2xl p-6 transform hover:scale-105 transition-transform duration-300">
                    <div class="text-center mb-4">
                        <div class="w-16 h-16 bg-green-100 rounded-full mx-auto mb-3 flex items-center justify-center">
                            <i class="fas fa-calculator text-green-600 text-3xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-green-600 mb-2"><?php echo t('handa_ka_na'); ?></h3>
                        <p class="text-gray-600 text-sm"><?php echo t('math_grades'); ?> 3-6</p>
                    </div>

                    <div class="mb-4">
                        <div class="flex flex-wrap gap-2 justify-center">
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs"><?php echo t('money_math'); ?></span>
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs"><?php echo t('fractions'); ?></span>
                            <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs"><?php echo t('math_racing'); ?></span>
                        </div>
                    </div>

                    <a href="games/handa/index.php" class="block w-full bg-green-600 text-white text-center py-3 rounded-xl font-bold hover:bg-green-700 transition">
                        <i class="fas fa-play mr-2"></i> <?php echo t('play'); ?>
                    </a>
                </div>

                <!-- Wikain Card -->
                <div class="bg-white rounded-3xl shadow-2xl p-6 transform hover:scale-105 transition-transform duration-300">
                    <div class="text-center mb-4">
                        <div class="w-16 h-16 bg-blue-100 rounded-full mx-auto mb-3 flex items-center justify-center">
                            <i class="fas fa-language text-blue-600 text-3xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-blue-600 mb-2">Wikain</h3>
                        <p class="text-gray-600 text-sm"><?php echo t('filipino_grades'); ?> 1-8</p>
                    </div>

                    <div class="mb-4">
                        <div class="flex flex-wrap gap-2 justify-center">
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs"><?php echo t('words'); ?></span>
                            <span class="px-2 py-1 bg-indigo-100 text-indigo-800 rounded-full text-xs"><?php echo t('sentences'); ?></span>
                            <span class="px-2 py-1 bg-cyan-100 text-cyan-800 rounded-full text-xs"><?php echo t('finals'); ?></span>
                        </div>
                    </div>

                    <a href="games/wikain/index.php" class="block w-full bg-blue-600 text-white text-center py-3 rounded-xl font-bold hover:bg-blue-700 transition">
                        <i class="fas fa-play mr-2"></i> <?php echo t('play'); ?>
                    </a>
                </div>

                <!-- Buhay Pilipinas Card -->
                <div class="bg-white rounded-3xl shadow-2xl p-6 transform hover:scale-105 transition-transform duration-300">
                    <div class="text-center mb-4">
                        <div class="w-16 h-16 bg-red-100 rounded-full mx-auto mb-3 flex items-center justify-center">
                            <i class="fas fa-map-marked-alt text-red-600 text-3xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-red-600 mb-2">Buhay Pilipinas</h3>
                        <p class="text-gray-600 text-sm"><?php echo t('social_studies_grades'); ?> 4-10</p>
                    </div>

                    <div class="mb-4">
                        <div class="flex flex-wrap gap-2 justify-center">
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs"><?php echo t('map_puzzle'); ?></span>
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs"><?php echo t('history'); ?></span>
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs"><?php echo t('animal'); ?></span>
                        </div>
                    </div>

                    <a href="games/buhay/index.php" class="block w-full bg-red-600 text-white text-center py-3 rounded-xl font-bold hover:bg-red-700 transition">
                        <i class="fas fa-play mr-2"></i> <?php echo t('play'); ?>
                    </a>
                </div>

                <!-- Agham Card -->
                <div class="bg-white rounded-3xl shadow-2xl p-6 transform hover:scale-105 transition-transform duration-300">
                    <div class="text-center mb-4">
                        <div class="w-16 h-16 bg-purple-100 rounded-full mx-auto mb-3 flex items-center justify-center">
                            <i class="fas fa-flask text-purple-600 text-3xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-purple-600 mb-2">Agham</h3>
                        <p class="text-gray-600 text-sm"><?php echo t('science_grades'); ?> 7-10</p>
                    </div>

                    <div class="mb-4">
                        <div class="flex flex-wrap gap-2 justify-center">
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs"><?php echo t('biology'); ?></span>
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs"><?php echo t('chemistry'); ?></span>
                            <span class="px-2 py-1 bg-teal-100 text-teal-800 rounded-full text-xs"><?php echo t('ecosystem'); ?></span>
                        </div>
                    </div>

                    <a href="games/agham/index.php" class="block w-full bg-purple-600 text-white text-center py-3 rounded-xl font-bold hover:bg-purple-700 transition">
                        <i class="fas fa-play mr-2"></i> <?php echo t('play'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
