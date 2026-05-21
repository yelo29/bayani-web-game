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
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Quiz Card -->
            <div class="bg-white rounded-3xl shadow-2xl p-8 transform hover:scale-105 transition-transform duration-300">
                <div class="text-center mb-6">
                    <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-book-open text-4xl text-[#0038A8]"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-[#0038A8] mb-2">📝 <?php echo t('quiz_title'); ?></h2>
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
                    <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-swords text-4xl text-[#CE1126]"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-[#CE1126] mb-2">⚔️ <?php echo t('battle_title'); ?></h2>
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
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
