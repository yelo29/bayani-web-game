<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/functions.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Refresh session data from database
refreshSessionData();

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
       'map_image' => 'https://images.pexels.com/photos/33076681/pexels-photo-33076681.jpeg', // Idagdag ang URL ng imahe ng mapa ng Maynila rito
        'history' => 'Ang Maynila ang kabiserang lungsod ng Pilipinas at naging sentro ng kasaysayan ng bansa sa loob ng maraming siglo. Itinatag noong 1571 ng konkistador na Kastila na si Miguel López de Legazpi, naging sentro ito ng pamahalaang kolonyal ng Espanya sa Asya. Nasaksihan ng lungsod ang mga mahahalagang kaganapan kabilang ang Sigaw ng Balintawak, ang Rebolusyong Pilipino laban sa Espanya, at ang Labanan sa Maynila noong Ikalawang Digmaang Pandaigdig. Ang Intramuros, ang makasaysayang napapaderang lungsod, ay nagsisilbing patunay sa kolonyal na arkitektura ng Espanya at sa katatagan ng mamamayang Pilipino.',
        'enemy_images' => [
            'https://amuraworld.com/images/articles/119-manila/118-miguel-lopez/118-miguel-lopez1.jpg', // Idagdag ang URL ng imahe ng kaaway 1 rito
            'https://upload.wikimedia.org/wikipedia/commons/8/89/Retrato_de_Rafael_Izquierdo_y_Guti%C3%A9rrez_%28cropped%29.jpg', // Idagdag ang URL ng imahe ng kaaway 2 rito
            'https://xiaochua.net/wp-content/uploads/2013/10/24-ang-lahat-mismo-padre-mariano-gil.jpg'  // Idagdag ang URL ng imahe ng kaaway 3 rito
        ],
        'enemy_facts' => [
            'Miguel López de Legazpi - Pinangunahan ang pagsakop ng mga Kastila sa Maynila noong 1571. Nagtatag ng permanenteng kolonyal na pamamahala ng Espanya sa Pilipinas. Ipinakilala ang sistemang encomienda, kung saan ang mga katutubo ay pinilit magbayad ng buwis at magtrabaho. Tumulong sa pagbuwag ng mga umiiral na lokal na kaharian at katutubong sistemang pampolitika.',
            'Rafael Izquierdo y Gutiérrez - Nagpatupad ng mahigpit na kolonyal na kontrol pagkatapos ng Pag-aalsa sa Cavite noong 1872. Tinanggal ang mga pribilehiyo ng mga manggagawa at sundalong Pilipino, na nagdulot ng higit na kawalang-kasiyahan. Nag-utos sa pagbitay sa mga paring Pilipino na iniugnay sa mga kilusang reporma. Nagpatibay sa panunupil ng Espanya laban sa nasyonalismong Pilipino.',
            'Mariano Gil - Nagbunyag ng Katipunan sa mga awtoridad na Kastila noong 1896. Nag-umpisa ng malawakang pag-aresto, pagpapahirap, at pagbitay sa mga pinaghihinalaang rebolusyonaryo. Naging simbolo ng pakikialam ng mga prayle sa politika at pagsupil sa mga kilusang pangkasarinlan ng Pilipinas.'
        ]
    ],
    2 => [
        'map_image' => 'https://images.pexels.com/photos/36703366/pexels-photo-36703366.jpeg', // Idagdag ang URL ng imahe ng mapa ng Cebu rito
        'history' => 'Ang Cebu ay kilala bilang "Reyna na Lungsod ng Timog" at may espesyal na bahagi sa kasaysayan ng Pilipinas bilang pook ng unang pamayanang Kastila at ng pagbibinyag sa mga unang Kristiyanong Pilipino. Dito dumaong si Fernando Magallanes (Ferdinand Magellan) noong 1521, ngunit tinalo siya ng lokal na pinuno na si Lapulapu sa Labanan sa Mactan — ang unang matagumpay na pagtutol laban sa mga dayuhang mananakop. Ang Cebu ay naging sentro ng kalakalan at Kristiyanismo, kung saan matatagpuan sa Basilica Minore del Santo Niño ang pinakamatandang relihiyosong relikya sa Pilipinas.',
        'enemy_images' => [
            'https://pbs.twimg.com/media/DkNEvgWU0AA59IH.jpg', // Idagdag ang URL ng imahe ng kaaway 1 rito
            'https://scontent.fmnl17-5.fna.fbcdn.net/v/t39.30808-6/472268638_1138085781014518_8905430817681953385_n.jpg?_nc_cat=110&ccb=1-7&_nc_sid=f727a1&_nc_eui2=AeF3F3xpl1jJvPwjZe0FghXeCBfF9231ozAIF8X3bfWjMEes8j2-B7sOvyuXNNkGKYA0Wvi6KcdS_E3OqOABJpng&_nc_ohc=ZSlhHqjbCn0Q7kNvwFRuBBN&_nc_oc=AdoiCjB5JN9Rhxt5uuY-Ib08hI9z37mVm5Fx0SxqyJtDCHQ-qUgGXI6WA7cYNHWshyg&_nc_zt=23&_nc_ht=scontent.fmnl17-5.fna&_nc_gid=n23Y2lz5H-ogNqA4vDPMVw&_nc_ss=7b2a8&oh=00_Af6u5aSGQzceVUxfR7YQ98l_V8BdK8Wx4aETtG5-gBzNxg&oe=6A15E1DE', // Idagdag ang URL ng imahe ng kaaway 2 rito
            'https://alchetron.com/cdn/rajah-humabon-4a37ebfa-cc96-42c9-bfa4-47c2c2f38be-resize-750.jpeg'  // Idagdag ang URL ng imahe ng kaaway 3 rito
        ],
        'enemy_facts' => [
            'Ferdinand Magellan - Dumating sa Pilipinas noong 1521 upang angkinin ang mga lupain para sa Espanya. Nagdala ng dayuhang pakikialam sa militar at namilit ng mga alyansa. Siniil ang mga lokal na pinuno upang tanggapin ang awtoridad ng Espanya at ang Kristiyanismo. Ang kanyang ekspedisyon ang nagmarka ng simula ng susunod na kolonisasyon ng Espanya.',
            'Limahong - Nanguna sa mga pagsalakay ng pirata sa mga baybaying pamayanan ng Pilipinas. Atakehin ang mga lugar na kontrolado ng mga Kastila at nambasag ng mga ruta ng kalakalan. Nagdulot ng pagkawasak at kawalan ng katatagan sa ilang mga komunidad.',
            'Rajah Humabon - Nakipag-alyansa kay Magellan at tinanggap ang impluwensya ng Espanya. Pinayagan ang pagpapalawak ng presensya ng mga Kastila sa Cebu. Ang pakikipag-alitan sa iba pang katutubong pinuno ay nakatulong sa maagang pagkakaroon ng kapangyarihan ng mga kolonyalista sa Visayas.'
        ]
    ],
    3 => [
        'map_image' => 'https://images.pexels.com/photos/32047037/pexels-photo-32047037.jpeg', // Idagdag ang URL ng imahe ng mapa ng Davao rito
        'history' => 'Ang Davao ang pinakamalaking lungsod sa Pilipinas base sa lawak ng lupain at nagsisilbing pasukan patungong Mindanao. Tahanan ng Bundok Apo, ang pinakamataas na taluktok sa bansa, ang Davao ay may mayaman na kasaysayan ng katutubong kultura at paglaban. Noong Ikalawang Digmaang Pandaigdig, naging pangunahing larangan ito ng labanan sa pagitan ng mga pwersang Pilipino at Amerikano laban sa pananakop ng Hapon. Kilala ang lungsod sa magkakaiba nitong pamanang kultura, na nagpapasama sa mga tradisyong katutubong Lumad, Muslim, at Kristiyano. Ngayon, nakatayo ito bilang simbolo ng katatagan at likas na kagandahan ng Mindanao.',
        'enemy_images' => [
            'https://upload.wikimedia.org/wikipedia/commons/thumb/6/62/Yamashita_Tomoyuki_%28cropped%29_%282%29.jpg/500px-Yamashita_Tomoyuki_%28cropped%29_%282%29.jpg', // Idagdag ang URL ng imahe ng kaaway 1 rito
            'https://alchetron.com/cdn/benigno-ramos-4323a809-6b04-449e-bee0-27f4bf9cbaa-resize-750.jpeg', // Idagdag ang URL ng imahe ng kaaway 2 rito
            'https://upload.wikimedia.org/wikipedia/commons/thumb/4/44/Portrait_of_General_Masaharu_Homma%2C_1943.jpg/250px-Portrait_of_General_Masaharu_Homma%2C_1943.jpg'  // Idagdag ang URL ng imahe ng kaaway 3 rito
        ],
        'enemy_facts' => [
            'Tomoyuki Yamashita - Pinamunuan ang mga pwersang mananakop ng Hapon sa Pilipinas noong Ikalawang Digmaang Pandaigdig. Nangasiwa sa mga kampanyang militar na iniugnay sa mga masaker, pagpapahirap, at pagkawasak. Ang kanyang mga pwersa ay gumawa ng malubhang pag-abuso laban sa mga sibilyang Pilipino at mga bilanggo.',
            'Benigno Ramos - Sumuporta sa mga awtoridad ng pananakop ng Hapon. Tumulong sa pag-organisa ng Makapili, isang grupong maka-Hapon na bumatikos at nagturo sa mga gerilyang Pilipino. Itinuring ng maraming Pilipino bilang isang kolaborator laban sa kilusang paglaban.',
            'Masaharu Homma - Pinangunahan ang pagsalakay sa Pilipinas noong 1941–1942. Iniugnay sa Bataan Death March, kung saan libu-libong Pilipino at Amerikanong bilanggo ang namatay. Hinatulan ng mga krimen sa digmaan pagkatapos ng Ikalawang Digmaang Pandaigdig.'
        ]
    ],
    4 => [
        'map_image' => 'https://images.pexels.com/photos/4175000/pexels-photo-4175000.jpeg', // Idagdag ang URL ng imahe ng mapa ng Vigan rito
        'history' => 'Ang Vigan ay isang UNESCO World Heritage Site na kilala sa maayos nitong napangalagaang kolonyal na arkitektura ng Espanya. Itinatag noong 1572 ni Juan de Salcedo, naging sentro ito ng kalakalan at kultura sa Hilagang Luzon. Ang Calle Crisologo ng lungsod, kasama ang mga kalsadang gawa sa cobblestone at mga ancestral house, ay nagbibigay ng sulyap sa nakaraang kolonyal ng Pilipinas. Ang Vigan din ang tinalagang sinilangan ng mga kilalang personalidad kabilang si Padre Jose Burgos, isa sa mga martir ng GOMBURZA na nagbigay-inspirasyon sa Rebolusyong Pilipino.',
        'enemy_images' => [
            'https://upload.wikimedia.org/wikipedia/commons/3/3f/Jos%C3%A9_Basco_y_Vargas.jpg', // Idagdag ang URL ng imahe ng kaaway 1 rito
            'https://scontent.fmnl17-1.fna.fbcdn.net/v/t39.30808-6/577559288_1286024163568408_4718905600654558562_n.webp?stp=dst-jpg_tt6&_nc_cat=101&ccb=1-7&_nc_sid=127cfc&_nc_eui2=AeGRo95exqd-QY8bUObRUl12EfC9HYOxwTgR8L0dg7HBOFScvq_JuIVkND7Y1T3TOcsa7-Y6pxaiOhH5Qy-bxi4y&_nc_ohc=A5kf1tGHu0cQ7kNvwHJueJ7&_nc_oc=AdoaWyYMJyxVCORX1fJAS7_Ppauzo4Jb3ARDNjcVprGpQA6ahRhyJg66a4YIoxbXu6I&_nc_zt=23&_nc_ht=scontent.fmnl17-1.fna&_nc_gid=476YVC0jFzkHKMlL4VJm9g&_nc_ss=7b2a8&oh=00_Af6RH1SZnjX4ICSc97NMjOg7jizJi7wWkEt5wUCgwxzetQ&oe=6A15CA4C', // Idagdag ang URL ng imahe ng kaaway 2 rito
            'https://upload.wikimedia.org/wikipedia/commons/b/bd/Valeriano_Weyler_bust.jpg'  // Idagdag ang URL ng imahe ng kaaway 3 rito
        ],
        'enemy_facts' => [
            'José Basco y Vargas - Nagpatupad ng monopolyo sa tabako na nagpahirap nang husto sa mga magsasakang Pilipino. Pinilit ang mga magsasaka na magtanim ng tabako sa ilalim ng mahigpit na kolonyal na kota. Pinarusahan ang mga tumutol sa mga patakarang pang-ekonomiya ng kolonya. Nagpalakas sa kontrol ng ekonomiya ng Espanya sa Hilagang Luzon.',
            'Esteban Rodríguez de Figueroa - Lumahok sa mga kampanyang militar na may kaugnayan sa sapilitang pagtatrabaho at pang-aalipin. Sinasamantala ang mga katutubong komunidad sa panahon ng mga pagsisikap sa pagpapalawak ng kolonya. Gumamit ng mga armadong ekspedisyon upang patatagin ang kontrol sa teritoryo ng Espanya.',
            'Valeriano Weyler - Gumamit ng marahas na taktika ng militar laban sa mga rebeldeng Pilipino. Nagpalawak ng pagmamanman at panunupil sa panahon ng paglaban sa Espanya. Naging kilala sa mga brutal na pamamaraan ng counterinsurgency.'
        ]
    ],
    5 => [
        'map_image' => 'https://preview.redd.it/zamboanga-del-sur-v0-qlcr3tksmkee1.jpg?width=1080&crop=smart&auto=webp&s=9a100f9461b605b39ade42ac7d8705796e1bfc39', // Idagdag ang URL ng imahe ng mapa ng Zamboanga rito
        'history' => 'Ang Zamboanga, na kilala bilang "Lungsod ng mga Bulaklak," ay isang natatanging tagpuan ng mga kultura na may malakas na impluwensya ng Kastila, Muslim, at katutubo. Itinatag noong 1635 bilang isang muog-militar upang ipagtanggol laban sa mga pagsalakay ng Moro, naging pinakatimog itong tanggulan ng kolonyal na pamamahala ng Espanya. Ang Fort Pilar ng lungsod, na itinayo noong 1718, ay nakatayo bilang simbolo ng kolonyal na presensya ng Espanya. Ang natatanging wikang Chavacano ng Zamboanga, isang creole na batay sa Espanyol, ay sumasalamin sa mayaman nitong pamanang kultura at makasaysayang kahalagahan bilang sangandaan ng mga sibilisasyon.',
        'enemy_images' => [
            'https://upload.wikimedia.org/wikipedia/commons/0/0a/Sultan_Utto_Anwaruddin.png', // Idagdag ang URL ng imahe ng kaaway 1 rito
            'https://alchetron.com/cdn/pascual-cervera-y-topete-8ffc898f-2dfc-41f1-a43d-66bef870c32-resize-750.jpeg', // Idagdag ang URL ng imahe ng kaaway 2 rito
            'https://upload.wikimedia.org/wikipedia/commons/thumb/8/80/Sultan_of_Sulu_with_others_LCCN2014685226_%28cropped%29.jpg/500px-Sultan_of_Sulu_with_others_LCCN2014685226_%28cropped%29.jpg'  // Idagdag ang URL ng imahe ng kaaway 3 rito
        ],
        'enemy_facts' => [
            'Datu Uto - Nanguna sa mga pagsalakay at armadong paglaban sa Mindanao. Nasangkot sa mga hidwaan na nambulabog sa mga pamayanan at kalakalan. Tinutulan ang pagpapalawak ng Espanya sa pamamagitan ng pakikidigma at mga pag-atake.',
            'Pascual Cervera y Topete - Sumuporta sa mga operasyon ng pagtatanggol ng militar ng Espanya sa mga kolonyal na teritoryo. Tumulong sa pagpapanatili ng presensya ng militar ng Espanya sa mga rehiyon ng Mindanao. Kumatawan sa patuloy na pagpapatupad ng kolonyal na batas sa panahon ng mga kilusang paglaban.',
            'Amirul Kiram - Lumahok sa mga armadong salungatan na may kaugnayan sa mga teritoryal at pampolitikang pakikibaka. Nanguna sa mga aksyon ng paglaban na nagdulot ng kawalan ng katatagan sa ilang bahagi ng Mindanao at Sulu. Nasangkot sa matagal na sagupaan laban sa kolonyal at karibal na rehiyonal na pwersa.'
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
