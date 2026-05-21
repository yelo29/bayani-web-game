<?php
session_start();
require_once 'includes/header.php';
require_once 'includes/functions.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Redirect if hero already chosen
if (isset($_SESSION['hero_class']) && $_SESSION['hero_class']) {
    header('Location: profile.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hero_class = $_POST['hero_class'] ?? '';
    
    if (!in_array($hero_class, ['mandirigma', 'lakambini', 'mangkukulam'])) {
        $error = 'Invalid hero class selected.';
    } else {
        // Update user in database
        $pdo = getDB();
        $stmt = $pdo->prepare("UPDATE users SET hero_class = ? WHERE id = ?");
        
        if ($stmt->execute([$hero_class, $_SESSION['user_id']])) {
            $_SESSION['hero_class'] = $hero_class;
            header('Location: profile.php');
            exit;
        } else {
            $error = 'Failed to select hero. Please try again.';
        }
    }
}

// Hero class definitions
$heroes = [
    'mandirigma' => [
        'name' => 'Mandirigma',
        'title' => 'Warrior',
        'description' => 'Bonus XP on history questions. Strong and brave, the Mandirigma excels in battles of knowledge about Philippine history.',
        'color' => '#CE1126',
        'icon' => 'fa-shield-alt',
        'bonus' => '+5 XP per history question'
    ],
    'lakambini' => [
        'name' => 'Lakambini',
        'title' => 'Scholar',
        'description' => 'Bonus XP on perfect scores. Wise and learned, the Lakambini rewards excellence and perfect knowledge.',
        'color' => '#0038A8',
        'icon' => 'fa-book',
        'bonus' => '+20 XP for perfect scores'
    ],
    'mangkukulam' => [
        'name' => 'Mangkukulam',
        'title' => 'Mystic',
        'description' => 'Bonus XP on speed. Quick and clever, the Mangkukulam rewards fast thinkers and swift answers.',
        'color' => '#FCD116',
        'icon' => 'fa-bolt',
        'bonus' => '+10 XP for finishing under 2 minutes'
    ]
];
?>

<main class="min-h-screen bg-gray-50 py-12 px-4">
    <div class="max-w-5xl mx-auto">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold font-serif text-[#0038A8] mb-4">
                Choose Your Hero
            </h1>
            <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                Select your hero class to begin your journey. Each class has unique abilities and bonuses.
            </p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 border-2 border-red-500 rounded-xl p-4 mb-8 text-center max-w-md mx-auto">
                <i class="fas fa-exclamation-circle text-red-500 text-xl mb-2"></i>
                <p class="text-red-700 font-medium"><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php foreach ($heroes as $class => $hero): ?>
                <div class="hero-card cursor-pointer" onclick="selectHero('<?php echo $class; ?>')">
                    <input type="radio" name="hero_class" value="<?php echo $class; ?>" id="hero_<?php echo $class; ?>" class="hidden" required>
                    <label for="hero_<?php echo $class; ?>" class="block">
                        <div class="bg-white rounded-2xl shadow-lg p-8 hover:shadow-2xl transition transform hover:-translate-y-2 border-4 border-transparent hover:border-[<?php echo $hero['color']; ?>] h-full">
                            <div class="text-center">
                                <div class="w-24 h-24 rounded-full mx-auto mb-6 flex items-center justify-center text-white text-4xl" style="background: <?php echo $hero['color']; ?>;">
                                    <i class="fas <?php echo $hero['icon']; ?>"></i>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-800 mb-2"><?php echo $hero['name']; ?></h3>
                                <p class="text-sm font-medium text-gray-500 mb-4"><?php echo $hero['title']; ?></p>
                                <p class="text-gray-600 mb-6"><?php echo $hero['description']; ?></p>
                                <div class="bg-gray-50 rounded-xl p-4">
                                    <p class="text-sm font-bold" style="color: <?php echo $hero['color']; ?>;">
                                        <i class="fas fa-star mr-2"></i><?php echo $hero['bonus']; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </label>
                </div>
            <?php endforeach; ?>
        </form>

        <div class="text-center mt-12">
            <button type="submit" form="heroForm" class="bg-[#0038A8] text-white px-12 py-4 rounded-full font-bold text-lg hover:bg-[#002870] transition transform hover:scale-105 shadow-lg">
                <i class="fas fa-check-circle mr-2"></i>Confirm Hero Selection
            </button>
        </div>
    </div>
</main>

<script>
function selectHero(heroClass) {
    document.getElementById('hero_' + heroClass).checked = true;
    
    // Remove active class from all cards
    document.querySelectorAll('.hero-card').forEach(card => {
        card.querySelector('div').classList.remove('border-[#CE1126]', 'border-[#0038A8]', 'border-[#FCD116]');
        card.querySelector('div').classList.add('border-transparent');
    });
    
    // Add active class to selected card
    const selectedCard = document.getElementById('hero_' + heroClass).closest('.hero-card');
    const colors = {
        'mandirigma': '#CE1126',
        'lakambini': '#0038A8',
        'mangkukulam': '#FCD116'
    };
    selectedCard.querySelector('div').classList.remove('border-transparent');
    selectedCard.querySelector('div').classList.add('border-4');
    selectedCard.querySelector('div').style.borderColor = colors[heroClass];
}

// Add form ID to form
document.querySelector('form').id = 'heroForm';
</script>

<?php require_once 'includes/footer.php'; ?>
