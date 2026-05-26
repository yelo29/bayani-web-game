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

if ($isLocked) {
    header('Location: rehiyon.php?region_id=' . $regionId);
    exit;
}

// Get enemies for this region
$stmt = $pdo->prepare("SELECT * FROM enemies WHERE region_id = ? ORDER BY id ASC");
$stmt->execute([$regionId]);
$enemies = $stmt->fetchAll();

// Region-specific data (maps, history background, enemy images, enemy facts)
$regionData = [
    1 => [
       'map_image' => 'https://images.pexels.com/photos/33076681/pexels-photo-33076681.jpeg',
        'history' => 'Ang Maynila ang kabiserang lungsod ng Pilipinas at naging sentro ng kasaysayan ng bansa sa loob ng maraming siglo. Itinatag noong 1571 ng konkistador na Kastila na si Miguel López de Legazpi, naging sentro ito ng pamahalaang kolonyal ng Espanya sa Asya. Nasaksihan ng lungsod ang mga mahahalagang kaganapan kabilang ang Sigaw ng Balintawak, ang Rebolusyong Pilipino laban sa Espanya, at ang Labanan sa Maynila noong Ikalawang Digmaang Pandaigdig. Ang Intramuros, ang makasaysayang napapaderang lungsod, ay nagsisilbing patunay sa kolonyal na arkitektura ng Espanya at sa katatagan ng mamamayang Pilipino.',
        'enemy_images' => [
            'https://amuraworld.com/images/articles/119-manila/118-miguel-lopez/118-miguel-lopez1.jpg',
            'https://upload.wikimedia.org/wikipedia/commons/8/89/Retrato_de_Rafael_Izquierdo_y_Guti%C3%A9rrez_%28cropped%29.jpg',
            'https://xiaochua.net/wp-content/uploads/2013/10/24-ang-lahat-mismo-padre-mariano-gil.jpg'
        ],
        'enemy_facts' => [
            'Miguel López de Legazpi - Pinangunahan ang pagsakop ng mga Kastila sa Maynila noong 1571. Nagtatag ng permanenteng kolonyal na pamamahala ng Espanya sa Pilipinas. Ipinakilala ang sistemang encomienda, kung saan ang mga katutubo ay pinilit magbayad ng buwis at magtrabaho. Tumulong sa pagbuwag ng mga umiiral na lokal na kaharian at katutubong sistemang pampolitika.',
            'Rafael Izquierdo y Gutiérrez - Nagpatupad ng mahigpit na kolonyal na kontrol pagkatapos ng Pag-aalsa sa Cavite noong 1872. Tinanggal ang mga pribilehiyo ng mga manggagawa at sundalong Pilipino, na nagdulot ng higit na kawalang-kasiyahan. Nag-utos sa pagbitay sa mga paring Pilipino na iniugnay sa mga kilusang reporma. Nagpatibay sa panunupil ng Espanya laban sa nasyonalismong Pilipino.',
            'Mariano Gil - Nagbunyag ng Katipunan sa mga awtoridad na Kastila noong 1896. Nag-umpisa ng malawakang pag-aresto, pagpapahirap, at pagbitay sa mga pinaghihinalaang rebolusyonaryo. Naging simbolo ng pakikialam ng mga prayle sa politika at pagsupil sa mga kilusang pangkasarinlan ng Pilipinas.'
        ]
    ],
    2 => [
        'map_image' => 'https://images.pexels.com/photos/36703366/pexels-photo-36703366.jpeg',
        'history' => 'Ang Cebu ay kilala bilang "Reyna na Lungsod ng Timog" at may espesyal na bahagi sa kasaysayan ng Pilipinas bilang pook ng unang pamayanang Kastila at ng pagbibinyag sa mga unang Kristiyanong Pilipino. Dito dumaong si Fernando Magallanes (Ferdinand Magellan) noong 1521, ngunit tinalo siya ng lokal na pinuno na si Lapulapu sa Labanan sa Mactan — ang unang matagumpay na pagtutol laban sa mga dayuhang mananakop. Ang Cebu ay naging sentro ng kalakalan at Kristiyanismo, kung saan matatagpuan sa Basilica Minore del Santo Niño ang pinakamatandang relihiyosong relikya sa Pilipinas.',
        'enemy_images' => [
            'https://pbs.twimg.com/media/DkNEvgWU0AA59IH.jpg',
            'https://scontent.fmnl17-5.fna.fbcdn.net/v/t39.30808-6/472268638_1138085781014518_8905430817681953385_n.jpg?_nc_cat=110&ccb=1-7&_nc_sid=f727a1&_nc_eui2=AeF3F3xpl1jJvPwjZe0FghXeCBfF9231ozAIF8X3bfWjMEes8j2-B7sOvyuXNNkGKYA0Wvi6KcdS_E3OqOABJpng&_nc_ohc=ZSlhHqjbCn0Q7kNvwFRuBBN&_nc_oc=AdoiCjB5JN9Rhxt5uuY-Ib08hI9z37mVm5Fx0SxqyJtDCHQ-qUgGXI6WA7cYNHWshyg&_nc_zt=23&_nc_ht=scontent.fmnl17-5.fna&_nc_gid=n23Y2lz5H-ogNqA4vDPMVw&_nc_ss=7b2a8&oh=00_Af6u5aSGQzceVUxfR7YQ98l_V8BdK8Wx4aETtG5-gBzNxg&oe=6A15E1DE',
            'https://alchetron.com/cdn/rajah-humabon-4a37ebfa-cc96-42c9-bfa4-47c2c2f38be-resize-750.jpeg'
        ],
        'enemy_facts' => [
            'Ferdinand Magellan - Dumating sa Pilipinas noong 1521 upang angkinin ang mga lupain para sa Espanya. Nagdala ng dayuhang pakikialam sa militar at namilit ng mga alyansa. Siniil ang mga lokal na pinuno upang tanggapin ang awtoridad ng Espanya at ang Kristiyanismo. Ang kanyang ekspedisyon ang nagmarka ng simula ng susunod na kolonisasyon ng Espanya.',
            'Limahong - Nanguna sa mga pagsalakay ng pirata sa mga baybaying pamayanan ng Pilipinas. Atakehin ang mga lugar na kontrolado ng mga Kastila at nambasag ng mga ruta ng kalakalan. Nagdulot ng pagkawasak at kawalan ng katatagan sa ilang mga komunidad.',
            'Rajah Humabon - Nakipag-alyansa kay Magellan at tinanggap ang impluwensya ng Espanya. Pinayagan ang pagpapalawak ng presensya ng mga Kastila sa Cebu. Ang pakikipag-alitan sa iba pang katutubong pinuno ay nakatulong sa maagang pagkakaroon ng kapangyarihan ng mga kolonyalista sa Visayas.'
        ]
    ],
    3 => [
        'map_image' => 'https://images.pexels.com/photos/32047037/pexels-photo-32047037.jpeg',
        'history' => 'Ang Davao ang pinakamalaking lungsod sa Pilipinas base sa lawak ng lupain at nagsisilbing pasukan patungong Mindanao. Tahanan ng Bundok Apo, ang pinakamataas na taluktok sa bansa, ang Davao ay may mayaman na kasaysayan ng katutubong kultura at paglaban. Noong Ikalawang Digmaang Pandaigdig, naging pangunahing larangan ito ng labanan sa pagitan ng mga pwersang Pilipino at Amerikano laban sa pananakop ng Hapon. Kilala ang lungsod sa magkakaiba nitong pamanang kultura, na nagpapasama sa mga tradisyong katutubong Lumad, Muslim, at Kristiyano. Ngayon, nakatayo ito bilang simbolo ng katatagan at likas na kagandahan ng Mindanao.',
        'enemy_images' => [
            'https://upload.wikimedia.org/wikipedia/commons/thumb/6/62/Yamashita_Tomoyuki_%28cropped%29_%282%29.jpg/500px-Yamashita_Tomoyuki_%28cropped%29_%282%29.jpg',
            'https://alchetron.com/cdn/benigno-ramos-4323a809-6b04-449e-bee0-27f4bf9cbaa-resize-750.jpeg',
            'https://upload.wikimedia.org/wikipedia/commons/thumb/4/44/Portrait_of_General_Masaharu_Homma%2C_1943.jpg/250px-Portrait_of_General_Masaharu_Homma%2C_1943.jpg'
        ],
        'enemy_facts' => [
            'Tomoyuki Yamashita - Pinamunuan ang mga pwersang mananakop ng Hapon sa Pilipinas noong Ikalawang Digmaang Pandaigdig. Nangasiwa sa mga kampanyang militar na iniugnay sa mga masaker, pagpapahirap, at pagkawasak. Ang kanyang mga pwersa ay gumawa ng malubhang pag-abuso laban sa mga sibilyang Pilipino at mga bilanggo.',
            'Benigno Ramos - Sumuporta sa mga awtoridad ng pananakop ng Hapon. Tumulong sa pag-organisa ng Makapili, isang grupong maka-Hapon na bumatikos at nagturo sa mga gerilyang Pilipino. Itinuring ng maraming Pilipino bilang isang kolaborator laban sa kilusang paglaban.',
            'Masaharu Homma - Pinangunahan ang pagsalakay sa Pilipinas noong 1941–1942. Iniugnay sa Bataan Death March, kung saan libu-libong Pilipino at Amerikanong bilanggo ang namatay. Hinatulan ng mga krimen sa digmaan pagkatapos ng Ikalawang Digmaang Pandaigdig.'
        ]
    ],
    4 => [
        'map_image' => 'https://images.pexels.com/photos/4175000/pexels-photo-4175000.jpeg',
        'history' => 'Ang Vigan ay isang UNESCO World Heritage Site na kilala sa maayos nitong napangalagaang kolonyal na arkitektura ng Espanya. Itinatag noong 1572 ni Juan de Salcedo, naging sentro ito ng kalakalan at kultura sa Hilagang Luzon. Ang Calle Crisologo ng lungsod, kasama ang mga kalsadang gawa sa cobblestone at mga ancestral house, ay nagbibigay ng sulyap sa nakaraang kolonyal ng Pilipinas. Ang Vigan din ang tinalagang sinilangan ng mga kilalang personalidad kabilang si Padre Jose Burgos, isa sa mga martir ng GOMBURZA na nagbigay-inspirasyon sa Rebolusyong Pilipino.',
        'enemy_images' => [
            'https://upload.wikimedia.org/wikipedia/commons/3/3f/Jos%C3%A9_Basco_y_Vargas.jpg',
            'https://scontent.fmnl17-1.fna.fbcdn.net/v/t39.30808-6/577559288_1286024163568408_4718905600654558562_n.webp?stp=dst-jpg_tt6&_nc_cat=101&ccb=1-7&_nc_sid=127cfc&_nc_eui2=AeGRo95exqd-QY8bUObRUl12EfC9HYOxwTgR8L0dg7HBOFScvq_JuIVkND7Y1T3TOcsa7-Y6pxaiOhH5Qy-bxi4y&_nc_ohc=A5kf1tGHu0cQ7kNvwHJueJ7&_nc_oc=AdoaWyYMJyxVCORX1fJAS7_Ppauzo4Jb3ARDNjcVprGpQA6ahRhyJg66a4YIoxbXu6I&_nc_zt=23&_nc_ht=scontent.fmnl17-1.fna&_nc_gid=476YVC0jFzkHKMlL4VJm9g&_nc_ss=7b2a8&oh=00_Af6RH1SZnjX4ICSc97NMjOg7jizJi7wWkEt5wUCgwxzetQ&oe=6A15CA4C',
            'https://upload.wikimedia.org/wikipedia/commons/b/bd/Valeriano_Weyler_bust.jpg'
        ],
        'enemy_facts' => [
            'José Basco y Vargas - Nagpatupad ng monopolyo sa tabako na nagpahirap nang husto sa mga magsasakang Pilipino. Pinilit ang mga magsasaka na magtanim ng tabako sa ilalim ng mahigpit na kolonyal na kota. Pinarusahan ang mga tumutol sa mga patakarang pang-ekonomiya ng kolonya. Nagpalakas sa kontrol ng ekonomiya ng Espanya sa Hilagang Luzon.',
            'Esteban Rodríguez de Figueroa - Lumahok sa mga kampanyang militar na may kaugnayan sa sapilitang pagtatrabaho at pang-aalipin. Sinasamantala ang mga katutubong komunidad sa panahon ng mga pagsisikap sa pagpapalawak ng kolonya. Gumamit ng mga armadong ekspedisyon upang patatagin ang kontrol sa teritoryo ng Espanya.',
            'Valeriano Weyler - Gumamit ng marahas na taktika ng militar laban sa mga rebeldeng Pilipino. Nagpalawak ng pagmamanman at panunupil sa panahon ng paglaban sa Espanya. Naging kilala sa mga brutal na pamamaraan ng counterinsurgency.'
        ]
    ],
    5 => [
        'map_image' => 'https://preview.redd.it/zamboanga-del-sur-v0-qlcr3tksmkee1.jpg?width=1080&crop=smart&auto=webp&s=9a100f9461b605b39ade42ac7d8705796e1bfc39',
        'history' => 'Ang Zamboanga, na kilala bilang "Lungsod ng mga Bulaklak," ay isang natatanging tagpuan ng mga kultura na may malakas na impluwensya ng Kastila, Muslim, at katutubo. Itinatag noong 1635 bilang isang muog-militar upang ipagtanggol laban sa mga pagsalakay ng Moro, naging pinakatimog itong tanggulan ng kolonyal na pamamahala ng Espanya. Ang Fort Pilar ng lungsod, na itinayo noong 1718, ay nakatayo bilang simbolo ng kolonyal na presensya ng Espanya. Ang natatanging wikang Chavacano ng Zamboanga, isang creole na batay sa Espanyol, ay sumasalamin sa mayaman nitong pamanang kultura at makasaysayang kahalagahan bilang sangandaan ng mga sibilisasyon.',
        'enemy_images' => [
            'https://upload.wikimedia.org/wikipedia/commons/0/0a/Sultan_Utto_Anwaruddin.png',
            'https://alchetron.com/cdn/pascual-cervera-y-topete-8ffc898f-2dfc-41f1-a43d-66bef870c32-resize-750.jpeg',
            'https://upload.wikimedia.org/wikipedia/commons/thumb/8/80/Sultan_of_Sulu_with_others_LCCN2014685226_%28cropped%29.jpg/500px-Sultan_of_Sulu_with_others_LCCN2014685226_%28cropped%29.jpg'
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
            <a href="rehiyon.php?region_id=<?php echo $regionId; ?>" class="inline-flex items-center text-gray-600 hover:text-[#0038A8] transition">
                <i class="fas fa-arrow-left mr-2"></i> Bumalik sa Rehiyon
            </a>
        </div>

        <!-- Header -->
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-book-open text-purple-600 text-3xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-purple-600">Aralin / Wiki</h1>
                    <p class="text-gray-600"><?php echo htmlspecialchars($region['name']); ?> - <?php echo htmlspecialchars($region['province']); ?></p>
                </div>
            </div>

            <div class="bg-purple-50 border-l-4 border-purple-500 p-3 rounded">
                <p class="text-sm text-purple-800"><i class="fas fa-info-circle mr-2"></i><strong>Panuto:</strong> Basahin ang mga impormasyon dito upang matutunan mo ang kasaysayan ng rehiyong ito. Makakatulong ito sa iyong laban sa mga kaaway.</p>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
            <div class="flex flex-wrap gap-2">
                <button onclick="showWikiTab('kasaysayan')" id="tab-kasaysayan" class="wiki-tab px-6 py-3 rounded-xl font-bold bg-purple-600 text-white transition">Kasaysayan</button>
                <button onclick="showWikiTab('kaaway')" id="tab-kaaway" class="wiki-tab px-6 py-3 rounded-xl font-bold bg-gray-200 text-gray-700 hover:bg-gray-300 transition">Mga Kaaway</button>
                <button onclick="showWikiTab('trivia')" id="tab-trivia" class="wiki-tab px-6 py-3 rounded-xl font-bold bg-gray-200 text-gray-700 hover:bg-gray-300 transition">Trivia</button>
            </div>
        </div>

        <!-- Kasaysayan Content -->
        <div id="wiki-kasaysayan" class="wiki-content bg-white rounded-2xl shadow-lg p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4"><i class="fas fa-landmark mr-2 text-blue-600"></i> Kasaysayan ng Rehiyon</h2>
            <div class="bg-blue-50 p-6 rounded-xl border-l-4 border-blue-500 mb-6">
                <p class="text-gray-700 leading-relaxed text-lg">
                    <?php echo htmlspecialchars($data['history']); ?>
                </p>
            </div>

            <?php
            // Region-specific additional historical information
            $wikiHistory = [
                1 => [
                    'title' => 'Mahalagang Kaganapan sa Maynila',
                    'events' => [
                        ['year' => '1571', 'event' => 'Itinatag ni Miguel López de Legazpi ang Maynila bilang kabesera ng kolonya ng Espanya'],
                        ['year' => '1896', 'event' => 'Nagsimula ang Rebolusyong Pilipino laban sa Espanya'],
                        ['year' => '1898', 'event' => 'Deklarasyon ng Kalayaan ng Pilipinas'],
                        ['year' => '1945', 'event' => 'Pinalaya ang Maynila mula sa pananakop ng Hapon']
                    ]
                ],
                2 => [
                    'title' => 'Mahalagang Kaganapan sa Cebu',
                    'events' => [
                        ['year' => '1521', 'event' => 'Dumaong si Ferdinand Magellan at natalo ni Lapulapu sa Mactan'],
                        ['year' => '1565', 'event' => 'Ibinalik ni Miguel López de Legazpi ang Kristiyanismo sa Cebu'],
                        ['year' => '1898', 'event' => 'Naging bahagi ng rebolusyon laban sa Espanya']
                    ]
                ],
                3 => [
                    'title' => 'Mahalagang Kaganapan sa Davao',
                    'events' => [
                        ['year' => '1942', 'event' => 'Sinakop ng Hapon ang Davao'],
                        ['year' => '1945', 'event' => 'Pinalaya mula sa pananakop ng Hapon'],
                        ['year' => '1967', 'event' => 'Naging lungsod ang Davao']
                    ]
                ],
                4 => [
                    'title' => 'Mahalagang Kaganapan sa Vigan',
                    'events' => [
                        ['year' => '1572', 'event' => 'Itinatag ni Juan de Salcedo ang Vigan'],
                        ['year' => '1872', 'event' => 'Pinatay si Padre Jose Burgos kasama ang GOMBURZA'],
                        ['year' => '1999', 'event' => 'Naging UNESCO World Heritage Site']
                    ]
                ],
                5 => [
                    'title' => 'Mahalagang Kaganapan sa Zamboanga',
                    'events' => [
                        ['year' => '1635', 'event' => 'Itinatag ang Fort Pilar upang ipagtanggol laban sa mga Moro'],
                        ['year' => '1718', 'event' => 'Muling itinayo ang Fort Pilar'],
                        ['year' => '1936', 'event' => 'Naging chartered city ang Zamboanga']
                    ]
                ]
            ];

            $historyData = $wikiHistory[$regionId] ?? null;
            if ($historyData):
            ?>
            <h3 class="font-bold text-gray-800 mb-4 text-xl"><?php echo htmlspecialchars($historyData['title']); ?></h3>
            <div class="space-y-4">
                <?php foreach ($historyData['events'] as $event): ?>
                <div class="bg-yellow-50 p-4 rounded-xl border-l-4 border-yellow-500">
                    <div class="font-bold text-yellow-800 text-lg mb-1"><?php echo $event['year']; ?></div>
                    <p class="text-gray-700"><?php echo $event['event']; ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Kaaway Content -->
        <div id="wiki-kaaway" class="wiki-content hidden bg-white rounded-2xl shadow-lg p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4"><i class="fas fa-skull mr-2 text-red-600"></i> Impormasyon tungkol sa Mga Kaaway</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($enemies as $index => $enemy): ?>
                <div class="bg-red-50 p-6 rounded-xl border-l-4 border-red-500">
                    <div class="flex items-center gap-4 mb-3">
                        <?php if (isset($data['enemy_images'][$index])): ?>
                        <img src="<?php echo htmlspecialchars($data['enemy_images'][$index]); ?>" alt="<?php echo htmlspecialchars($enemy['name']); ?>" class="w-20 h-20 object-cover rounded-lg">
                        <?php endif; ?>
                        <div>
                            <h3 class="font-bold text-red-800 text-lg"><?php echo htmlspecialchars($enemy['name']); ?></h3>
                            <span class="text-sm text-gray-600"><?php echo htmlspecialchars($enemy['era']); ?></span>
                        </div>
                    </div>
                    <?php if (isset($data['enemy_facts'][$index])): ?>
                    <p class="text-gray-700 mb-3"><?php echo htmlspecialchars($data['enemy_facts'][$index]); ?></p>
                    <?php endif; ?>
                    <div class="flex gap-4 text-sm text-gray-600">
                        <span><i class="fas fa-heart text-red-500 mr-1"></i> HP: <?php echo $enemy['hp']; ?></span>
                        <span><i class="fas fa-fist-raised text-orange-500 mr-1"></i> ATK: <?php echo $enemy['attack']; ?></span>
                        <span><i class="fas fa-shield-alt text-blue-500 mr-1"></i> DEF: <?php echo $enemy['defense']; ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Trivia Content -->
        <div id="wiki-trivia" class="wiki-content hidden bg-white rounded-2xl shadow-lg p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4"><i class="fas fa-lightbulb mr-2 text-green-600"></i> Trivia at Mga Katotohanan</h2>
            <?php
            $wikiTrivia = [
                1 => [
                    'Ang Intramuros ay tinawag na "Walled City" dahil sa mga pader na nakapalibot dito.',
                    'Ang Rizal Park o Luneta ay kung saan binaril si Dr. Jose Rizal noong 1896.',
                    'Ang Maynila ay may pinakamataas na density ng populasyon sa Pilipinas.',
                    'Ang Binondo ang pinakamatandang Chinatown sa mundo, itinatag noong 1594.',
                    'Ang Manila Bay ay kilala sa magandang sunset view.'
                ],
                2 => [
                    'Si Lapulapu ang unang Filipino na tumalo sa mga dayuhang mananakop.',
                    'Ang Santo Niño de Cebu ang pinakamatandang relihiyosong relikya sa Pilipinas.',
                    'Ang Cebu ang pinakamatandang lungsod sa Pilipinas, itinatag noong 1565.',
                    'Ang Magellan\'s Cross ang simbolo ng Kristiyanismo sa Pilipinas.',
                    'Ang Cebu ay kilala sa lechon at mga beach resorts.'
                ],
                3 => [
                    'Ang Mount Apo ang pinakamataas na bundok sa Pilipinas na may taas na 2,954 metro.',
                    'Ang Davao ang pinakamalaking lungsod sa Pilipinas base sa lawak ng lupain.',
                    'Ang Davao City ay kilala sa durian at Philippine Eagle.',
                    'Ang Kadayawan Festival ay pagdiriwang ng pasasalamat sa ani sa Davao.',
                    'Ang Davao ay may 11 na tribung Lumad na naninirahan dito.'
                ],
                4 => [
                    'Ang Vigan ay isa sa mga few na Spanish colonial towns na napangalagaan sa Pilipinas.',
                    'Ang Calle Crisologo ay kilala sa mga cobblestone streets at ancestral houses.',
                    'Si Padre Jose Burgos ay isang martir na ipinatay noong 1872.',
                    'Ang Vigan ay tahanan ng burnay pottery, isang tradisyonal na paggawa ng palayok.',
                    'Ang Vigan ay naging UNESCO World Heritage Site noong 1999.'
                ],
                5 => [
                    'Ang Zamboanga ay kilala sa wikang Chavacano, isang Spanish-based creole.',
                    'Ang Fort Pilar ay itinayo noong 1718 upang ipagtanggol laban sa mga Moro.',
                    'Ang Zamboanga ay tinawag na "City of Flowers" dahil sa maraming bulaklak.',
                    'Ang Vinta, isang tradisyonal na bangka, ay simbolo ng Zamboanga.',
                    'Ang Zamboanga ay may malakas na impluwensya ng Muslim, Spanish, at katutubong kultura.'
                ]
            ];

            $triviaData = $wikiTrivia[$regionId] ?? [];
            if (!empty($triviaData)):
            ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($triviaData as $trivia): ?>
                <div class="bg-green-50 p-4 rounded-xl border-l-4 border-green-500">
                    <p class="text-gray-700"><i class="fas fa-lightbulb text-green-600 mr-2"></i><?php echo $trivia; ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p class="text-gray-500">No trivia available for this region.</p>
            <?php endif; ?>
        </div>
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
        tab.classList.remove('bg-purple-600', 'text-white');
        tab.classList.add('bg-gray-200', 'text-gray-700');
    });

    // Show selected content
    document.getElementById('wiki-' + tabName).classList.remove('hidden');

    // Highlight selected tab
    const selectedTab = document.getElementById('tab-' + tabName);
    selectedTab.classList.remove('bg-gray-200', 'text-gray-700');
    selectedTab.classList.add('bg-purple-600', 'text-white');
}
</script>

<?php require_once 'includes/footer.php'; ?>
