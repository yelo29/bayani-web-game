<?php
session_start();
require_once 'includes/functions.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get region ID from URL
$regionId = isset($_GET['region_id']) ? (int)$_GET['region_id'] : null;
if (!$regionId) {
    header('Location: mundo.php');
    exit;
}

// Get region details
$pdo = getDB();
$stmt = $pdo->prepare("SELECT * FROM regions WHERE id = ?");
$stmt->execute([$regionId]);
$region = $stmt->fetch();

if (!$region) {
    header('Location: mundo.php');
    exit;
}

// Check if region is locked
$isLocked = $_SESSION['level'] < $region['min_level'];

// Get enemies for this region
$stmt = $pdo->prepare("SELECT * FROM enemies WHERE region_id = ? ORDER BY id ASC");
$stmt->execute([$regionId]);
$enemies = $stmt->fetchAll();

// Get defeated enemy IDs for this region
$stmt = $pdo->prepare("
    SELECT DISTINCT enemy_id
    FROM battle_log
    WHERE user_id = ? AND enemy_id IN (SELECT id FROM enemies WHERE region_id = ?) AND won = 1
");
$stmt->execute([$_SESSION['user_id'], $regionId]);
$defeatedEnemyIds = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

// Get player progress for this region (count unique enemies defeated)
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT enemy_id) as unique_enemies_defeated
    FROM battle_log
    WHERE user_id = ? AND enemy_id IN (SELECT id FROM enemies WHERE region_id = ?) AND won = 1
");
$stmt->execute([$_SESSION['user_id'], $regionId]);
$uniqueDefeated = $stmt->fetchColumn() ?: 0;

// Get completed status from region_progress
$stmt = $pdo->prepare("SELECT completed FROM region_progress WHERE user_id = ? AND region_id = ?");
$stmt->execute([$_SESSION['user_id'], $regionId]);
$progress = $stmt->fetch() ?: ['completed' => 0];

// Region-specific data (maps, history background, enemy images, enemy facts)
$regionData = [
    1 => [
        'map_image' => 'https://images.pexels.com/photos/33076681/pexels-photo-33076681.jpeg', // Add your Manila map image URL here
        'history' => 'Maynila (Manila) is the capital city of the Philippines and has been the center of Philippine history for centuries. Founded in 1571 by Spanish conquistador Miguel López de Legazpi, it became the seat of Spanish colonial government in Asia. The city witnessed key events including the Cry of Balintawak, the Philippine Revolution against Spain, and the Battle of Manila during World War II. Intramuros, the historic walled city, stands as a testament to Spanish colonial architecture and the resilience of the Filipino people.',
        'enemy_images' => [
            'https://amuraworld.com/images/articles/119-manila/118-miguel-lopez/118-miguel-lopez1.jpg', // Add enemy 1 image URL here
            'https://upload.wikimedia.org/wikipedia/commons/8/89/Retrato_de_Rafael_Izquierdo_y_Guti%C3%A9rrez_%28cropped%29.jpg', // Add enemy 2 image URL here
            'https://xiaochua.net/wp-content/uploads/2013/10/24-ang-lahat-mismo-padre-mariano-gil.jpg'  // Add enemy 3 image URL here
        ],
        'enemy_facts' => [
            'Miguel López de Legazpi - Led the Spanish conquest of Manila in 1571. Established permanent Spanish colonial rule in the Philippines. Introduced the encomienda system, where natives were forced to pay tribute and labor. Helped dismantle existing local kingdoms and indigenous political systems.',
            'Rafael Izquierdo y Gutiérrez - Enforced strict colonial control after the 1872 Cavite Mutiny. Removed privileges of Filipino workers and soldiers, increasing unrest. Ordered the execution of Filipino priests linked to reform movements. Strengthened Spanish repression against Filipino nationalism.',
            'Mariano Gil - Exposed the Katipunan to Spanish authorities in 1896. Triggered mass arrests, torture, and executions of suspected revolutionaries. Became a symbol of friar interference in politics and suppression of Filipino independence movements.'
        ]
    ],
    2 => [
        'map_image' => 'https://images.pexels.com/photos/36703366/pexels-photo-36703366.jpeg', // Add your Cebu map image URL here
        'history' => 'Cebu is known as the "Queen City of the South" and holds a special place in Philippine history as the site of the first Spanish settlement and the baptism of the first Filipino Christians. It was here in 1521 that Ferdinand Magellan arrived, only to be defeated by the local chieftain Lapu-Lapu in the Battle of Mactan - the first successful resistance against foreign invaders. Cebu became a center of trade and Christianity, with the Basilica Minore del Santo Niño housing the oldest religious relic in the Philippines.',
        'enemy_images' => [
            'https://pbs.twimg.com/media/DkNEvgWU0AA59IH.jpg', // Add enemy 1 image URL here
            'https://scontent.fmnl17-5.fna.fbcdn.net/v/t39.30808-6/472268638_1138085781014518_8905430817681953385_n.jpg?_nc_cat=110&ccb=1-7&_nc_sid=f727a1&_nc_eui2=AeF3F3xpl1jJvPwjZe0FghXeCBfF9231ozAIF8X3bfWjMEes8j2-B7sOvyuXNNkGKYA0Wvi6KcdS_E3OqOABJpng&_nc_ohc=ZSlhHqjbCn0Q7kNvwFRuBBN&_nc_oc=AdoiCjB5JN9Rhxt5uuY-Ib08hI9z37mVm5Fx0SxqyJtDCHQ-qUgGXI6WA7cYNHWshyg&_nc_zt=23&_nc_ht=scontent.fmnl17-5.fna&_nc_gid=n23Y2lz5H-ogNqA4vDPMVw&_nc_ss=7b2a8&oh=00_Af6u5aSGQzceVUxfR7YQ98l_V8BdK8Wx4aETtG5-gBzNxg&oe=6A15E1DE', // Add enemy 2 image URL here
            'https://alchetron.com/cdn/rajah-humabon-4a37ebfa-cc96-42c9-bfa4-47c2c2f38be-resize-750.jpeg'  // Add enemy 3 image URL here
        ],
        'enemy_facts' => [
            'Ferdinand Magellan - Arrived in the Philippines in 1521 to claim lands for Spain. Introduced foreign military intervention and forced alliances. Pressured local rulers into accepting Spanish authority and Christianity. His expedition marked the beginning of future Spanish colonization.',
            'Limahong - Conducted pirate raids across Philippine coastal settlements. Attacked Spanish-controlled areas and disrupted trade routes. Caused destruction and instability in several communities.',
            'Rajah Humabon - Allied with Magellan and accepted Spanish influence. Allowed Spanish presence to expand in Cebu. Rivalries with other native leaders contributed to early colonial footholds in the Visayas.'
        ]
    ],
    3 => [
        'map_image' => 'https://images.pexels.com/photos/32047037/pexels-photo-32047037.jpeg', // Add your Davao map image URL here
        'history' => 'Davao is the largest city in the Philippines by land area and serves as the gateway to Mindanao. Home to Mount Apo, the country\'s highest peak, Davao has a rich history of indigenous culture and resistance. During World War II, it was a major battleground between Filipino and American forces against Japanese occupation. The city is known for its diverse cultural heritage, blending indigenous Lumad, Muslim, and Christian traditions. Today, it stands as a symbol of Mindanao\'s resilience and natural beauty.',
        'enemy_images' => [
            'https://upload.wikimedia.org/wikipedia/commons/thumb/6/62/Yamashita_Tomoyuki_%28cropped%29_%282%29.jpg/500px-Yamashita_Tomoyuki_%28cropped%29_%282%29.jpg', // Add enemy 1 image URL here
            'https://alchetron.com/cdn/benigno-ramos-4323a809-6b04-449e-bee0-27f4bf9cbaa-resize-750.jpeg', // Add enemy 2 image URL here
            'https://upload.wikimedia.org/wikipedia/commons/thumb/4/44/Portrait_of_General_Masaharu_Homma%2C_1943.jpg/250px-Portrait_of_General_Masaharu_Homma%2C_1943.jpg'  // Add enemy 3 image URL here
        ],
        'enemy_facts' => [
            'Tomoyuki Yamashita - Commanded Japanese occupation forces in the Philippines during World War II. Oversaw military campaigns linked to massacres, torture, and destruction. His forces committed severe abuses against Filipino civilians and prisoners.',
            'Benigno Ramos - Supported Japanese occupation authorities. Helped organize the Makapili, a pro-Japanese group accused of identifying Filipino guerrillas. Viewed by many Filipinos as a collaborator against the resistance movement.',
            'Masaharu Homma - Led the invasion of the Philippines in 1941–1942. Associated with the Bataan Death March, where thousands of Filipino and American prisoners died. Convicted of war crimes after World War II.'
        ]
    ],
    4 => [
        'map_image' => 'https://images.pexels.com/photos/4175000/pexels-photo-4175000.jpeg', // Add your Vigan map image URL here
        'history' => 'Vigan is a UNESCO World Heritage Site renowned for its well-preserved Spanish colonial architecture. Founded in 1572 by Juan de Salcedo, it became a center of trade and culture in Northern Luzon. The city\'s Calle Crisologo, with its cobblestone streets and ancestral houses, offers a glimpse into the Philippines\' colonial past. Vigan was also the birthplace of notable figures including Father Jose Burgos, one of the GOMBURZA martyrs who inspired the Philippine Revolution.',
        'enemy_images' => [
            'https://upload.wikimedia.org/wikipedia/commons/3/3f/Jos%C3%A9_Basco_y_Vargas.jpg', // Add enemy 1 image URL here
            'https://scontent.fmnl17-1.fna.fbcdn.net/v/t39.30808-6/577559288_1286024163568408_4718905600654558562_n.webp?stp=dst-jpg_tt6&_nc_cat=101&ccb=1-7&_nc_sid=127cfc&_nc_eui2=AeGRo95exqd-QY8bUObRUl12EfC9HYOxwTgR8L0dg7HBOFScvq_JuIVkND7Y1T3TOcsa7-Y6pxaiOhH5Qy-bxi4y&_nc_ohc=A5kf1tGHu0cQ7kNvwHJueJ7&_nc_oc=AdoaWyYMJyxVCORX1fJAS7_Ppauzo4Jb3ARDNjcVprGpQA6ahRhyJg66a4YIoxbXu6I&_nc_zt=23&_nc_ht=scontent.fmnl17-1.fna&_nc_gid=476YVC0jFzkHKMlL4VJm9g&_nc_ss=7b2a8&oh=00_Af6RH1SZnjX4ICSc97NMjOg7jizJi7wWkEt5wUCgwxzetQ&oe=6A15CA4C', // Add enemy 2 image URL here
            'https://upload.wikimedia.org/wikipedia/commons/b/bd/Valeriano_Weyler_bust.jpg'  // Add enemy 3 image URL here
        ],
        'enemy_facts' => [
            'José Basco y Vargas - Implemented the tobacco monopoly that heavily burdened Filipino farmers. Forced farmers to plant tobacco under strict colonial quotas. Punished those who resisted colonial economic policies. Increased Spain\'s economic control over Northern Luzon.',
            'Esteban Rodríguez de Figueroa - Participated in military campaigns tied to forced labor and slave-taking practices. Exploited native communities during colonial expansion efforts. Used armed expeditions to strengthen Spanish territorial control.',
            'Valeriano Weyler - Used harsh military tactics against Filipino rebels. Expanded surveillance and suppression during anti-Spanish resistance. Became known for brutal counterinsurgency methods.'
        ]
    ],
    5 => [
        'map_image' => 'https://preview.redd.it/zamboanga-del-sur-v0-qlcr3tksmkee1.jpg?width=1080&crop=smart&auto=webp&s=9a100f9461b605b39ade42ac7d8705796e1bfc39', // Add your Zamboanga map image URL here
        'history' => 'Zamboanga, known as the "City of Flowers," is a melting pot of cultures with strong Spanish, Muslim, and indigenous influences. Founded in 1635 as a military fort to defend against Moro raids, it became the southernmost outpost of Spanish colonial rule. The city\'s Fort Pilar, built in 1718, stands as a symbol of Spanish colonial presence. Zamboanga\'s unique Chavacano language, a Spanish-based creole, reflects its rich cultural heritage and historical significance as a crossroads of civilizations.',
        'enemy_images' => [
            'https://upload.wikimedia.org/wikipedia/commons/0/0a/Sultan_Utto_Anwaruddin.png', // Add enemy 1 image URL here
            'https://alchetron.com/cdn/pascual-cervera-y-topete-8ffc898f-2dfc-41f1-a43d-66bef870c32-resize-750.jpeg', // Add enemy 2 image URL here
            'https://upload.wikimedia.org/wikipedia/commons/thumb/8/80/Sultan_of_Sulu_with_others_LCCN2014685226_%28cropped%29.jpg/500px-Sultan_of_Sulu_with_others_LCCN2014685226_%28cropped%29.jpg'  // Add enemy 3 image URL here
        ],
        'enemy_facts' => [
            'Datu Uto - Conducted raids and armed resistance in Mindanao. Involved in conflicts that disrupted settlements and trade. Opposed Spanish expansion through warfare and attacks.',
            'Pascual Cervera y Topete - Supported Spanish military defense operations in colonial territories. Helped maintain Spanish military presence in Mindanao regions. Represented continued colonial enforcement during resistance movements.',
            'Amirul Kiram - Participated in armed conflicts connected to territorial and political struggles. Led resistance actions that caused instability in parts of Mindanao and Sulu. Involved in prolonged clashes with colonial and rival regional forces.'
        ]
    ]
];

$data = $regionData[$regionId] ?? [
    'map_image' => '',
    'history' => 'No historical information available.',
    'enemy_images' => [],
    'enemy_facts' => []
];

require_once 'includes/header.php';
?>

<main class="min-h-screen bg-gray-50 py-8 px-4">
    <div class="max-w-6xl mx-auto">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="mundo.php" class="inline-flex items-center text-gray-600 hover:text-[#0038A8] transition">
                <i class="fas fa-arrow-left mr-2"></i> Bumalik sa Mundo
            </a>
        </div>

        <!-- Region Header -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-8">
            <div class="p-8" style="background: <?php echo $region['background_color'] ?? '#0038A8'; ?>;">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-4xl font-bold text-white mb-2"><?php echo htmlspecialchars($region['name']); ?></h1>
                        <p class="text-white/80 text-lg mb-4"><?php echo htmlspecialchars($region['province']); ?></p>
                        <div class="flex gap-2">
                            <span class="px-4 py-2 bg-white/20 text-white rounded-full text-sm font-bold uppercase">
                                <?php echo htmlspecialchars($region['island_group']); ?>
                            </span>
                            <span class="px-4 py-2 bg-white/20 text-white rounded-full text-sm">
                                Min Level: <?php echo $region['min_level']; ?>
                            </span>
                        </div>
                    </div>
                    <?php if ($isLocked): ?>
                        <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                            <i class="fas fa-lock text-white text-2xl"></i>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($isLocked): ?>
            <!-- Locked Message -->
            <div class="bg-white rounded-2xl shadow-lg p-8 text-center mb-8">
                <i class="fas fa-lock text-6xl text-gray-400 mb-4"></i>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Rehiyon ay Nakakandado</h2>
                <p class="text-gray-600 mb-4">Kailangan mong umabot sa Level <?php echo $region['min_level']; ?> upang makapasok sa rehiyong ito.</p>
                <p class="text-gray-600">Kasalukuyang Level mo: <?php echo $_SESSION['level']; ?></p>
            </div>
        <?php else: ?>
            <!-- Map Section -->
            <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-bold text-[#0038A8] mb-4">
                    <i class="fas fa-image mr-2"></i> Itsura ng Rehiyon
                </h2>
                <?php if ($data['map_image']): ?>
                    <div class="rounded-xl overflow-hidden border-2 border-gray-200" style="height: 400px; cursor: pointer;" onclick="openImageModal('<?php echo htmlspecialchars($data['map_image']); ?>')">
                        <img src="<?php echo htmlspecialchars($data['map_image']); ?>"
                             alt="Image of <?php echo htmlspecialchars($region['name']); ?>"
                             class="w-full h-full object-contain">
                    </div>
                    <p class="text-sm text-gray-500 mt-2 text-center"><i class="fas fa-expand mr-1"></i> Click to view full image</p>
                <?php else: ?>
                    <p class="text-gray-500">Image not available</p>
                <?php endif; ?>
            </div>

            <!-- History Section -->
            <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-bold text-[#0038A8] mb-4">
                    <i class="fas fa-book-open mr-2"></i> Kasaysayan
                </h2>
                <p class="text-gray-700 leading-relaxed text-lg">
                    <?php echo htmlspecialchars($data['history']); ?>
                </p>
            </div>

            <!-- Enemies Section -->
            <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-[#0038A8]">
                        <i class="fas fa-skull mr-2"></i> Mga Kaaway
                    </h2>
                    <div class="text-sm text-gray-600">
                        <i class="fas fa-medal mr-1"></i> <?php echo $uniqueDefeated; ?>/<?php echo count($enemies); ?> Panalo
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php foreach ($enemies as $index => $enemy): ?>
                        <div class="border-2 border-gray-200 rounded-xl overflow-hidden hover:border-[#0038A8] transition">
                            <?php if (isset($data['enemy_images'][$index])): ?>
                                <div class="h-48 overflow-hidden bg-gray-100 flex items-center justify-center">
                                    <img src="<?php echo htmlspecialchars($data['enemy_images'][$index]); ?>"
                                         alt="<?php echo htmlspecialchars($enemy['name']); ?>"
                                         class="w-full h-full object-contain">
                                </div>
                            <?php endif; ?>
                            <div class="p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="font-bold text-gray-800 text-lg"><?php echo htmlspecialchars($enemy['name']); ?></h3>
                                    <div class="flex gap-2">
                                        <?php if (in_array($enemy['id'], $defeatedEnemyIds)): ?>
                                            <span class="px-2 py-1 bg-green-500 text-white rounded-full text-xs font-bold">
                                                <i class="fas fa-check mr-1"></i> Talo
                                            </span>
                                        <?php endif; ?>
                                        <span class="px-2 py-1 bg-gray-200 text-gray-700 rounded-full text-xs font-bold">
                                            <?php echo htmlspecialchars($enemy['era']); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="flex gap-4 text-sm text-gray-600 mb-3">
                                    <div><i class="fas fa-heart text-red-500 mr-1"></i> <?php echo $enemy['hp']; ?> HP</div>
                                    <div><i class="fas fa-fist-raised text-orange-500 mr-1"></i> <?php echo $enemy['attack']; ?> ATK</div>
                                    <div><i class="fas fa-shield-alt text-blue-500 mr-1"></i> <?php echo $enemy['defense']; ?> DEF</div>
                                </div>
                                
                                <p class="text-gray-600 text-sm mb-4"><?php echo htmlspecialchars($enemy['description']); ?></p>

                                <div class="flex gap-2">
                                    <a href="battle.php?region_id=<?php echo $regionId; ?>&enemy_id=<?php echo $enemy['id']; ?>"
                                       class="flex-1 bg-[#CE1126] text-white py-2 rounded-xl font-bold text-center hover:bg-[#a00d1a] transition">
                                        <i class="fas fa-sword mr-2"></i> Labanan
                                    </a>
                                    <button onclick="showFactModal(<?php echo $index; ?>)"
                                            class="flex-1 bg-[#0038A8] text-white py-2 rounded-xl font-bold hover:bg-[#002870] transition">
                                        <i class="fas fa-question-circle mr-2"></i> Sino?
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-90 hidden items-center justify-center z-50" onclick="closeImageModal()">
    <div class="max-w-5xl max-h-screen p-4">
        <img id="modalImage" src="" alt="Full size image" class="max-w-full max-h-screen object-contain">
        <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white text-3xl hover:text-gray-300">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>

<!-- Fact Modal -->
<div id="factModal" class="fixed inset-0 bg-black bg-opacity-90 hidden items-center justify-center z-50" onclick="closeFactModal()">
    <div class="max-w-2xl mx-4 bg-white rounded-2xl p-6" onclick="event.stopPropagation()">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-2xl font-bold text-[#0038A8]">
                <i class="fas fa-info-circle mr-2"></i> Sino?
            </h3>
            <button onclick="closeFactModal()" class="text-gray-500 hover:text-gray-700 text-2xl">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <p id="factContent" class="text-gray-700 text-lg leading-relaxed"></p>
    </div>
</div>

<script>
function openImageModal(imageUrl) {
    document.getElementById('modalImage').src = imageUrl;
    document.getElementById('imageModal').classList.remove('hidden');
    document.getElementById('imageModal').classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
    document.getElementById('imageModal').classList.remove('flex');
    document.body.style.overflow = 'auto';
}

// Enemy facts data
const enemyFacts = <?php echo json_encode($data['enemy_facts'] ?? []); ?>;

function showFactModal(enemyIndex) {
    const fact = enemyFacts[enemyIndex];
    if (fact) {
        document.getElementById('factContent').textContent = fact;
        document.getElementById('factModal').classList.remove('hidden');
        document.getElementById('factModal').classList.add('flex');
        document.body.style.overflow = 'hidden';
    }
}

function closeFactModal() {
    document.getElementById('factModal').classList.add('hidden');
    document.getElementById('factModal').classList.remove('flex');
    document.body.style.overflow = 'auto';
}

// Close modal on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeImageModal();
        closeFactModal();
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
