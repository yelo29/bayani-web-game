<?php
require_once 'includes/header.php';
require_once 'includes/functions.php';

// Get categories
$categories = getCategories();

// Category-specific study materials based on seed.php (IDs 1-5)
$categoryWiki = [
    1 => [
        'title' => 'Mga Bayani',
        'description' => 'Aralin tungkol sa mga bayani ng Pilipinas',
        'topics' => [
            [
                'name' => 'Andres Bonifacio — Ama ng Rebolusyon',
                'content' => 'Si Andres Bonifacio ang nagtatag ng Katipunan noong Hulyo 7, 1892 sa Tondo, Maynila. Kilala siya bilang "Ama ng Rebolusyong Pilipino" at "Bayani ng Mahihirap" dahil nagmula siya sa mahirap na pamilya at nakipaglaban para sa masa. Ang Katipunan ay isang lihim na samahan na layunin ay palayain ang Pilipinas mula sa pananakop ng Espanya.'
            ],
            [
                'name' => 'Jose Rizal — Pambansang Bayani',
                'content' => 'Si Jose Rizal ang sumulat ng "Noli Me Tangere" na inilathala noong 1887 at inilantad ang mga pang-aabuso ng Espanya. Ang kanyang mga akda ay nagising sa konsyensya ng mga Pilipino at nagbigay inspirasyon sa rebolusyon. Siya ang kinikilalang Pambansang Bayani ng Pilipinas.'
            ],
            [
                'name' => 'Emilio Aguinaldo — Unang Pangulo',
                'content' => 'Si Emilio Aguinaldo ang naging unang Pangulo ng Unang Republika ng Pilipinas noong 1899. Kilala siya bilang "Bayani ng Cavite" at "Bayani ng Binakayan" dahil sa kanyang mga tagumpay laban sa mga pwersang Kastila sa probinsya ng Cavite. Siya ang nagpahayag ng kalayaan ng Pilipinas noong Hunyo 12, 1898 sa Kawit, Cavite.'
            ],
            [
                'name' => 'Apolinario Mabini — Utak ng Rebolusyon',
                'content' => 'Si Apolinario Mabini ay kilala bilang "Utak ng Rebolusyon" at "Dakilang Paralisado." Bagaman paralysado siya dahil sa polio, nagsilbi siyang punong tagapayo ni Aguinaldo at nagbigay ng matalinong gabay sa panahon ng rebolusyon. Ang kanyang dedikasyon sa kalayaan ay naging inspirasyon sa lahat.'
            ],
            [
                'name' => 'Gregorio del Pilar — Bayani ng Tirad Pass',
                'content' => 'Si Gregorio del Pilar ay namatay sa pagtatanggol ng Tirad Pass laban sa 500 na sundalong Amerikano nang may kasamang 60 lalaki lamang. Ang kanyang sakripisyo ay nagbigay-daan kay Aguinaldo na makatakas. Siya ay isa sa mga pinakabatang heneral sa kasaysayan ng Pilipinas.'
            ]
        ],
        'key_terms' => [
            'Katipunan — Lihim na samahan na itinatag ni Bonifacio noong 1892',
            'Noli Me Tangere — Nobel ni Rizal na nagbunyag ng mga pang-aabuso ng Espanya',
            'Unang Republika — Unang gobyerno ng Pilipinas na pinamunuan ni Aguinaldo',
            'Tirad Pass — Labanan kung saan namatay si Gregorio del Pilar',
            'Paralysado — Kondisyon ni Mabini ngunit patuloy siyang naglingkod sa bansa',
            'Pambansang Bayani — Titulo na ibinigay kay Jose Rizal',
            'Kawit, Cavite — Lugar kung saan ipinahayag ang kalayaan noong 1898'
        ]
    ],
    2 => [
        'title' => 'Kasaysayan',
        'description' => 'Aralin tungkol sa kasaysayan ng Pilipinas',
        'topics' => [
            [
                'name' => 'Panahon ng Kastila at Pagtatayo ng Kolonya',
                'content' => 'Noong 1521, dumating si Ferdinand Magellan sa Pilipinas. Noong 1565, itinatag ni Miguel López de Legazpi ang unang permanenteng kolonya ng Espanya sa Cebu. Ang Pilipinas ay nanatiling kolonya ng Espanya nang mahigit 300 taon, kung saan naimpluwensyahan ang kultura, relihiyon, at sistemang pampolitika.'
            ],
            [
                'name' => 'Ang Katipunan at Rebolusyong 1896',
                'content' => 'Noong Hulyo 7, 1892, itinatag ni Andres Bonifacio ang Katipunan sa Tondo, Maynila. Noong Agosto 23, 1896, nagsimula ang Himagsikan (Cry of Balintawak) na nagpasimula ng Rebolusyong Pilipino laban sa Espanya. Ito ang simula ng pakikipaglaban ng mga Pilipino para sa kalayaan.'
            ],
            [
                'name' => 'Kalayaan at Digmaang Pilipino-Amerikano',
                'content' => 'Noong Hunyo 12, 1898, ipinahayag ang kalayaan ng Pilipinas sa Kawit, Cavite. Ngunit noong Pebrero 4, 1899, nagsimula ang Digmaang Pilipino-Amerikano nang putukan ng mga sundalong Amerikano ang mga Pilipinong sundalo. Nagdulot ito ng matagal na pakikipaglaban para sa tunay na kalayaan.'
            ],
            [
                'name' => 'Commonwealth at Panahon ng Hapon',
                'content' => 'Noong Nobyembre 15, 1935, itinatag ang Commonwealth ng Pilipinas na may Manuel L. Quezon bilang pangulo. Noong 1942-1945, sinakop ng Hapon ang Pilipinas sa panahon ng Ikalawang Digmaang Pandaigdig. Noong Setyembre 21, 1972, idineklara ni Ferdinand Marcos ang Martial Law.'
            ],
            [
                'name' => 'EDSA at Modernong Kasaysayan',
                'content' => 'Noong Pebrero 22-25, 1986, nagtagumpay ang EDSA People Power Revolution — isang mapayapang rebolusyon na nagpaalis kay Marcos. Noong Hulyo 4, 1946, opisyal na ipinagkaloob ng Amerika ang ganap na kalayaan ng Pilipinas.'
            ]
        ],
        'key_terms' => [
            'Katipunan — Itinatag noong Hulyo 7, 1892 ni Andres Bonifacio',
            'Hunyo 12, 1898 — Araw ng Kalayaan ng Pilipinas',
            'Commonwealth — Itinatag noong 1935, si Quezon ang unang pangulo',
            'Martial Law — Idineklara ni Marcos noong Setyembre 21, 1972',
            'EDSA — Mapayapang rebolusyon noong Pebrero 22-25, 1986',
            'Hulyo 4, 1946 — Araw ng ganap na kalayaan mula sa Amerika',
            'Cry of Balintawak — Simula ng Rebolusyon noong Agosto 23, 1896'
        ]
    ],
    3 => [
        'title' => 'Kultura',
        'description' => 'Aralin tungkol sa kulturang Pilipino at tradisyon',
        'topics' => [
            [
                'name' => 'Mga Pambansang Simbolo',
                'content' => 'Ang Sampaguita (Jasminum sambac) ang pambansang bulaklak, idineklara noong 1934. Ang Philippine Eagle (Pithecophaga jefferyi) ang pambansang ibon. Ang Filipino (batay sa Tagalog) ang pambansang wika ayon sa Konstitusyon ng 1987. Ang Arnis (Eskrima) ang pambansang palakasan at martial art, idineklara noong 2009.'
            ],
            [
                'name' => 'Pagkain at Lutuin',
                'content' => 'Ang Adobo ay kadalasang itinuturing na di-opisyal na pambansang pagkain ng Pilipinas. Ito ay gawa sa baboy na niluto sa suka at toyo. Ang Lechon ay tanyag sa mga selebrasyon. Ang sinigang naman ay sinampalukang sabaw na maasim at masustansya.'
            ],
            [
                'name' => 'Tradisyon at Pagbati',
                'content' => 'Ang "Mabuhay" ang tradisyonal na pagbati na nangangahulugang "matagal mabuhay" o "welcome." Ang "Salamat" ang salitang Pilipino para sa "salamat/thank you." Ang Simbang Gabi ay isang siyam na araw na nobena bago ang Pasko, isa sa pinakamahalagang tradisyon ng mga Pilipino.'
            ],
            [
                'name' => 'Tahanan at Arkitektura',
                'content' => 'Ang Bahay Kubo (Nipa Hut) ang tradisyonal na katutubong bahay ng mga Pilipino. Ito ay gawa sa kawayan at nipa at itinataas sa mga poste. Sumasalamin ito sa simpleng pamumuhay at pagiging malapit sa kalikasan ng mga Pilipino.'
            ],
            [
                'name' => 'Pamilya at Pagpapahalaga',
                'content' => 'Ang "Kuya" ang tawag sa nakatatandang lalaking kapatid o pinsan, habang ang "Ate" naman ang tawag sa nakatatandang babaeng kapatid. Ang mga salitang "po" at "opo" ay ginagamit upang magpakita ng respeto sa mga nakakatanda, isang mahalagang gawi sa kulturang Pilipino.'
            ]
        ],
        'key_terms' => [
            'Sampaguita — Pambansang bulaklak, idineklara noong 1934',
            'Philippine Eagle — Pambansang ibon ng Pilipinas',
            'Arnis/Eskrima — Pambansang palakasan, idineklara noong 2009',
            'Adobo — Di-opisyal na pambansang pagkain',
            'Bahay Kubo — Tradisyonal na katutubong bahay',
            'Simbang Gabi — Siyam na araw na nobena bago ang Pasko',
            'Mabuhay — Tradisyonal na pagbati ng mga Pilipino'
        ]
    ],
    4 => [
        'title' => 'Heograpiya',
        'description' => 'Aralin tungkol sa heograpiya ng Pilipinas',
        'topics' => [
            [
                'name' => 'Arkipelago ng Pilipinas',
                'content' => 'Ang Pilipinas ay isang arkipelago na binubuo ng 7,641 na pulo. Ang kabisera ay Maynila, ang pinakamakulay at pinaka-urbanisadong lungsod. Ang Pilipinas ay napapalibutan ng Dagat Pasipiko sa silangan, Dagat Tsina sa kanluran, at Dagat Sulu sa timog.'
            ],
            [
                'name' => 'Mga Bundok at Bulkan',
                'content' => 'Ang Bundok Apo sa Mindanao ang pinakamataas na bundok sa Pilipinas na may 2,954 metro sa taas ng dagat. Ang Bundok Mayon sa Albay ay sikat dahil sa perpektong hugis ng kono nito. Ang Bundok Pinatubo ay kilala sa malaking pagsabog noong 1991.'
            ],
            [
                'name' => 'Mga Ilog at Lawa',
                'content' => 'Ang Ilog Cagayan ang pinakamahabang ilog sa Pilipinas na may halos 505 kilometro ang haba. Ang Laguna de Bay ang pinakamalaking lawa sa Pilipinas at isa sa mga pinakamalaki sa Timog-Silangang Asya. Ang Lawa ng Taal naman ay kilala sa pulo na may bulkan sa gitna.'
            ],
            [
                'name' => 'Mga Rehiyon at Lalawigan',
                'content' => 'Ang Pilipinas ay nahahati sa 17 administrative na rehiyon. Ang Gitnang Luzon ang kilala bilang "Rice Granary of the Philippines" dahil nagpo-produce ito ng karamihan ng bigas ng bansa. Ang Batanes ang pinakamaliit na lalawigan sa Pilipinas sa populasyon at lawak.'
            ]
        ],
        'key_terms' => [
            '7,641 — Bilang ng mga pulo sa Pilipinas',
            'Bundok Apo — Pinakamataas na bundok (2,954 metro)',
            'Ilog Cagayan — Pinakamahaba, humigit 505 km',
            'Laguna de Bay — Pinakamalaking lawa',
            'Bundok Mayon — Kilala sa perpektong hugis ng kono',
            '17 — Bilang ng mga rehiyon sa Pilipinas',
            'Gitnang Luzon — "Rice Granary of the Philippines"'
        ]
    ],
    5 => [
        'title' => 'Pamumuhay',
        'description' => 'Aralin tungkol sa pamumuhay at mga pagpapahalaga ng Pilipino',
        'topics' => [
            [
                'name' => 'Bayanihan at Pagkakaisa',
                'content' => 'Ang "Bayanihan" ay kumakatawan sa diwa ng pagkakaisa at kooperasyon ng komunidad. Ito ang kaisipan ng pagtutulungan — tulad ng mga kapitbahay na tumutulong nang sabay-sabay sa pagbabago ng tirahan. Ito ang nagpapakilala sa Pilipino bilang mapagtulungang tao.'
            ],
            [
                'name' => 'Utang na Loob at Pakikisama',
                'content' => 'Ang "Utang na loob" ay ang pagpapahalaga sa utang na gawa ng loob — ang obligasyon na suklian ang mga pabor na natanggap. Ang "Pakikisama" naman ay ang pagpapahalaga sa pagpapanatili ng magagandang relasyon at pakikipag-ayon sa kapwa, kahit kailangan pang isakripisyo ang sariling kagustuhan.'
            ],
            [
                'name' => 'Respeto sa Nakatatanda',
                'content' => 'Ang "Galang" (respeto) ay isa sa pinakamahalagang pagpapahalaga ng mga Pilipino, lalo na sa mga nakatatanda. Ang "po" at "opo" ay mga salitang nagpapakita ng paggalang. Ang "Mano po" ay isang galaw ng pagbibigay-galang kung saan kinukuha ang kamay ng nakakatanda at inilalagay sa noo.'
            ],
            [
                'name' => 'Pamilya at Pagmamahal',
                'content' => 'Ang pamilya ay sentro ng lipunang Pilipino. Karaniwan, ang pamilyang Pilipino ay extended family — kasama ang mga lolo, lola, tito, tita, at mga pinsan. Ang "Malasakit" ay ang pagpapahalaga ng pagmamalasakit at pagkakaisa ng pamilya.'
            ],
            [
                'name' => 'Hiya, Pasalubong, at Iba Pang Pagpapahalaga',
                'content' => 'Ang "Hiya" ay isang uri ng pagkamahiyain o pagpapahalaga sa karangalan na gumagabay sa panlipunang pag-uugali. Ang "Pasalubong" ay kaugalian ng pagdadala ng regalo o souvenir mula sa isang biyahe para sa mga pamilya at kaibigan. Ipinapakita nito ang pagmamahal at pag-iisip sa kapwa.'
            ]
        ],
        'key_terms' => [
            'Bayanihan — Diwa ng komunidad na pagkakaisa at kooperasyon',
            'Utang na loob — Obligasyong suklian ang mga pabor na natanggap',
            'Pakikisama — Pagpapanatili ng magagandang relasyon sa kapwa',
            'Galang — Respeto, lalo na sa mga nakatatanda',
            'Mano po — Galaw ng pagbibigay-galang sa mga nakakatanda',
            'Hiya — Pagkamahiyain na gumagabay sa panlipunang pag-uugali',
            'Pasalubong — Regalo o souvenir mula sa isang biyahe',
            'Malasakit — Pagmamalasakit at pagkakaisa sa kapwa'
        ]
    ]
];
?>

<main class="min-h-screen bg-gray-50 py-8 px-4">
    <div class="max-w-6xl mx-auto">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="piliin.php" class="inline-flex items-center text-gray-600 hover:text-[#0038A8] transition">
                <i class="fas fa-arrow-left mr-2"></i> Bumalik sa Piliin
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
                    <p class="text-gray-600">Mga Kategorya ng Quiz</p>
                </div>
            </div>

            <div class="bg-purple-50 border-l-4 border-purple-500 p-3 rounded">
                <p class="text-sm text-purple-800"><i class="fas fa-info-circle mr-2"></i><strong>Panuto:</strong> Basahin ang mga impormasyon dito upang matutunan mo ang bawat kategorya. Makakatulong ito sa iyong pagsagot sa mga quiz.</p>
            </div>
        </div>

        <!-- Category Wiki Content -->
        <div class="space-y-8">
            <?php foreach ($categories as $category): ?>
                <?php
                $wikiData = $categoryWiki[$category['id']] ?? null;
                $colorClasses = [
                    'yellow' => 'from-yellow-400 to-yellow-600',
                    'red'    => 'from-red-400 to-red-600',
                    'green'  => 'from-green-400 to-green-600',
                    'blue'   => 'from-blue-400 to-blue-600',
                    'purple' => 'from-purple-400 to-purple-600'
                ];
                $gradient = $colorClasses[$category['color']] ?? 'from-gray-400 to-gray-600';
                ?>
                <?php if ($wikiData): ?>
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <!-- Category Header -->
                    <div class="p-6 bg-gradient-to-r <?php echo $gradient; ?> text-white">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                                <i class="fas <?php echo $category['icon']; ?> text-2xl"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($wikiData['title']); ?></h2>
                                <p class="text-white/80 text-sm"><?php echo htmlspecialchars($wikiData['description']); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Topics -->
                    <div class="p-6">
                        <h3 class="font-bold text-gray-800 mb-4 text-lg"><i class="fas fa-book mr-2 text-blue-600"></i> Mga Paksa</h3>
                        <div class="space-y-4 mb-6">
                            <?php foreach ($wikiData['topics'] as $topic): ?>
                            <div class="bg-blue-50 p-4 rounded-xl border-l-4 border-blue-500">
                                <h4 class="font-bold text-blue-800 mb-2"><?php echo htmlspecialchars($topic['name']); ?></h4>
                                <p class="text-gray-700 text-sm"><?php echo $topic['content']; ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Key Terms -->
                        <h3 class="font-bold text-gray-800 mb-4 text-lg"><i class="fas fa-key mr-2 text-green-600"></i> Mga Mahalagang Salita</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <?php foreach ($wikiData['key_terms'] as $term): ?>
                            <div class="bg-green-50 p-3 rounded-lg border-l-4 border-green-500">
                                <p class="text-gray-700 text-sm"><i class="fas fa-lightbulb text-green-600 mr-2"></i><?php echo htmlspecialchars($term); ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>