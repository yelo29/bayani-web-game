<?php
require_once 'includes/header.php';
?>

<!-- Hero Section -->
<section class="min-h-screen flex items-center justify-center relative overflow-hidden" style="background: linear-gradient(135deg, #0038A8 0%, #1a1a2e 50%, #CE1126 100%);">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-20 left-20 w-32 h-32 bg-yellow-400 rounded-full blur-3xl"></div>
        <div class="absolute bottom-20 right-20 w-48 h-48 bg-yellow-400 rounded-full blur-3xl"></div>
    </div>

    <div class="text-center z-10 px-4">
        <h1 class="text-5xl md:text-7xl lg:text-8xl font-bold font-serif text-white mb-6 drop-shadow-lg">
            Bayani World
        </h1>
        <p class="text-xl md:text-2xl lg:text-3xl text-yellow-400 font-medium mb-12 max-w-2xl mx-auto">
            <?php echo t('tagline'); ?>
        </p>
        <a href="<?php echo isset($_SESSION['user_id']) ? 'maglaro.php' : 'login.php'; ?>" class="inline-block bg-yellow-400 text-[#0038A8] px-8 py-4 md:px-12 md:py-5 rounded-full font-bold text-lg md:text-xl hover:bg-yellow-300 transition transform hover:scale-105 shadow-lg">
            <i class="fas fa-play mr-2"></i> <?php echo t('magsimula'); ?>
        </a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
