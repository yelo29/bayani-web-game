<?php
require_once 'includes/header.php';
require_once 'includes/functions.php';

// Get categories
$categories = getCategories();
?>

<main class="min-h-screen bg-gray-50 py-12 px-4">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl font-bold font-serif text-[#0038A8] mb-4">
                <?php echo t('choose_category'); ?>
            </h1>
            <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                <?php echo t('quiz_desc'); ?>
            </p>
        </div>

        <!-- Category Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($categories as $category): ?>
                <?php
                $colorClasses = [
                    'yellow' => 'from-yellow-400 to-yellow-600',
                    'red' => 'from-red-400 to-red-600',
                    'green' => 'from-green-400 to-green-600',
                    'blue' => 'from-blue-400 to-blue-600',
                    'purple' => 'from-purple-400 to-purple-600'
                ];
                $gradient = $colorClasses[$category['color']] ?? 'from-gray-400 to-gray-600';
                ?>
                <a href="quiz.php?category=<?php echo $category['id']; ?>"
                   class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-2xl transition transform hover:-translate-y-2 border-2 border-transparent hover:border-yellow-400 group">
                    <div class="flex items-center mb-4">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-br <?php echo $gradient; ?> flex items-center justify-center text-white text-2xl group-hover:scale-110 transition">
                            <i class="fas <?php echo $category['icon']; ?>"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-xl font-bold text-gray-800 group-hover:text-[#0038A8] transition">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </h3>
                            <span class="text-sm text-gray-500">
                                <i class="fas fa-question-circle mr-1"></i> 10 <?php echo t('categories'); ?>
                            </span>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-4">
                        <?php echo htmlspecialchars($category['description']); ?>
                    </p>
                    <div class="flex items-center justify-between">
                        <span class="px-3 py-1 bg-gray-100 rounded-full text-xs font-medium text-gray-600">
                            Medium
                        </span>
                        <span class="text-[#0038A8] font-medium group-hover:translate-x-2 transition">
                            <?php echo t('start_btn'); ?> <i class="fas fa-arrow-right ml-1"></i>
                        </span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Back Button -->
        <div class="text-center mt-12">
            <a href="index.php" class="inline-block bg-gray-200 text-gray-800 px-6 py-3 rounded-full font-medium hover:bg-gray-300 transition">
                <i class="fas fa-arrow-left mr-2"></i> <?php echo t('home'); ?>
            </a>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
