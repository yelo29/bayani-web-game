<?php
// Database Seeder - Password Protected
// Access via: seed.php?token=B4y4n1Qu1z_S3cr3t_T0k3n_2026_PH_H1st0ry!@#$%^&*

session_start();

// Check for secret token
$secret_token = 'B4y4n1Qu1z_S3cr3t_T0k3n_2026_PH_H1st0ry!@#$%^&*';
if (!isset($_GET['token']) || $_GET['token'] !== $secret_token) {
    die('Access denied. Invalid token.');
}

require_once '../includes/db.php';

$pdo = getDB();

// Drop existing tables (for clean re-seeding)
$pdo->exec("DROP TABLE IF EXISTS scores");
$pdo->exec("DROP TABLE IF EXISTS questions");
$pdo->exec("DROP TABLE IF EXISTS categories");
$pdo->exec("DROP TABLE IF EXISTS users");

// Create tables
echo "Creating tables...\n";

// Categories table
$pdo->exec("
    CREATE TABLE categories (
      id INT AUTO_INCREMENT PRIMARY KEY,
      name VARCHAR(100) NOT NULL,
      description TEXT,
      icon VARCHAR(50),
      color VARCHAR(20),
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

// Questions table
$pdo->exec("
    CREATE TABLE questions (
      id INT AUTO_INCREMENT PRIMARY KEY,
      category_id INT NOT NULL,
      question TEXT NOT NULL,
      option_a VARCHAR(255) NOT NULL,
      option_b VARCHAR(255) NOT NULL,
      option_c VARCHAR(255) NOT NULL,
      option_d VARCHAR(255) NOT NULL,
      correct_option ENUM('a','b','c','d') NOT NULL,
      fun_fact TEXT,
      difficulty ENUM('easy','medium','hard') DEFAULT 'medium',
      FOREIGN KEY (category_id) REFERENCES categories(id)
    )
");

// Scores table
$pdo->exec("
    CREATE TABLE scores (
      id INT AUTO_INCREMENT PRIMARY KEY,
      player_name VARCHAR(80) NOT NULL,
      category_id INT,
      score INT NOT NULL,
      total_questions INT NOT NULL,
      time_taken INT,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (category_id) REFERENCES categories(id)
    )
");

// Users table
$pdo->exec("
    CREATE TABLE users (
      id INT AUTO_INCREMENT PRIMARY KEY,
      username VARCHAR(80) UNIQUE NOT NULL,
      email VARCHAR(150) UNIQUE NOT NULL,
      password_hash VARCHAR(255) NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

echo "Tables created successfully!\n";

// Insert categories
echo "Inserting categories...\n";

$categories = [
    [
        'name' => 'Mga Bayani',
        'description' => 'Learn about the heroes who shaped Philippine history',
        'icon' => 'fa-star',
        'color' => 'yellow'
    ],
    [
        'name' => 'Rebolusyon',
        'description' => 'Discover the revolutionary movements that freed the Philippines',
        'icon' => 'fa-fire',
        'color' => 'red'
    ],
    [
        'name' => 'Sinaunang Pilipinas',
        'description' => 'Explore pre-colonial Philippine civilization and culture',
        'icon' => 'fa-landmark',
        'color' => 'green'
    ],
    [
        'name' => 'Panahon ng Kastila',
        'description' => 'Understand the Spanish colonial period and its impact',
        'icon' => 'fa-church',
        'color' => 'blue'
    ],
    [
        'name' => 'Ika-20 Siglo',
        'description' => 'Modern Philippine history from Commonwealth to present',
        'icon' => 'fa-flag',
        'color' => 'purple'
    ]
];

foreach ($categories as $category) {
    $stmt = $pdo->prepare("
        INSERT INTO categories (name, description, icon, color)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $category['name'],
        $category['description'],
        $category['icon'],
        $category['color']
    ]);
}

echo "Categories inserted successfully!\n";

// Insert questions for Category 1: Mga Bayani (Heroes)
echo "Inserting questions for Mga Bayani...\n";

$heroes_questions = [
    [
        'question' => 'Sino ang Pambansang Bayani ng Pilipinas na kilala bilang "Ang Dakilang Martir"?',
        'option_a' => 'Andres Bonifacio',
        'option_b' => 'Jose Rizal',
        'option_c' => 'Emilio Aguinaldo',
        'option_d' => 'Apolinario Mabini',
        'correct_option' => 'b',
        'fun_fact' => 'Jose Rizal wrote two novels: Noli Me Tangere and El Filibusterismo, which exposed the abuses of Spanish colonial rule.',
        'difficulty' => 'easy'
    ],
    [
        'question' => 'Sino ang tinaguriang "Supremo" ng Katipunan?',
        'option_a' => 'Emilio Jacinto',
        'option_b' => 'Andres Bonifacio',
        'option_c' => 'Gregorio del Pilar',
        'option_d' => 'Antonio Luna',
        'correct_option' => 'b',
        'fun_fact' => 'Andres Bonifacio founded the Katipunan in 1892, a secret society that aimed to liberate the Philippines from Spanish rule.',
        'difficulty' => 'easy'
    ],
    [
        'question' => 'Sino ang unang babaeng General sa Pilipinas na naglaban sa mga Kastila?',
        'option_a' => 'Melchora Aquino',
        'option_b' => 'Gabriela Silang',
        'option_c' => 'Teresa Magbanua',
        'option_d' => 'Agueda Kahabagan',
        'correct_option' => 'b',
        'fun_fact' => 'Gabriela Silang led Ilocano rebels after her husband Diego Silang was assassinated in 1763. She continued the fight for four months.',
        'difficulty' => 'medium'
    ],
    [
        'question' => 'Sino ang "Utak ng Rebolusyon" na naglingkod bilang unang Punong Ministro ng Republika?',
        'option_a' => 'Emilio Aguinaldo',
        'option_b' => 'Apolinario Mabini',
        'option_c' => 'Andres Bonifacio',
        'option_d' => 'Emilio Jacinto',
        'correct_option' => 'b',
        'fun_fact' => 'Despite being paralyzed from the waist down due to polio, Apolinario Mabini wrote the Malolos Constitution and served as Aguinaldo\'s chief advisor.',
        'difficulty' => 'medium'
    ],
    [
        'question' => 'Sino ang "Bayani sa Bataan" na nagtanggol sa Bataan hanggang sa huling sandali?',
        'option_a' => 'Gregorio del Pilar',
        'option_b' => 'Antonio Luna',
        'option_c' => 'Vicente Lim',
        'option_d' => 'Jose Abad Santos',
        'correct_option' => 'c',
        'fun_fact' => 'General Vicente Lim was the first Filipino graduate of West Point and refused to surrender during WWII, choosing to die with honor.',
        'difficulty' => 'hard'
    ],
    [
        'question' => 'Sino ang "Ina ng Rebolusyon" na nagbigay ng tulong sa mga Katipunero?',
        'option_a' => 'Melchora Aquino',
        'option_b' => 'Gabriela Silang',
        'option_c' => 'Marina Dizon',
        'option_d' => 'Josefa Rizal',
        'correct_option' => 'a',
        'fun_fact' => 'Melchora Aquino, also known as Tandang Sora, provided food and shelter to Katipuneros. She was exiled to Guam for her support of the revolution.',
        'difficulty' => 'easy'
    ],
    [
        'question' => 'Sino ang "Brains of the Revolution" na nagsulat ng Kartilya ng Katipunan?',
        'option_a' => 'Andres Bonifacio',
        'option_b' => 'Emilio Jacinto',
        'option_c' => 'Apolinario Mabini',
        'option_d' => 'Gregorio del Pilar',
        'correct_option' => 'b',
        'fun_fact' => 'Emilio Jacinto was only 18 when he wrote the Kartilya, which served as the moral code of the Katipunan.',
        'difficulty' => 'medium'
    ],
    [
        'question' => 'Sino ang "Hero of Tirad Pass" na namatay sa edad na 24?',
        'option_a' => 'Antonio Luna',
        'option_b' => 'Gregorio del Pilar',
        'option_c' => 'Miguel Malvar',
        'option_d' => 'Manuel Quezon',
        'correct_option' => 'b',
        'fun_fact' => 'Gregorio del Pilar and 60 men held off 500 American soldiers at Tirad Pass, allowing President Aguinaldo to escape.',
        'difficulty' => 'medium'
    ],
    [
        'question' => 'Sino ang unang babaeng doktor sa Pilipinas?',
        'option_a' => 'Fe del Mundo',
        'option_b' => 'Carmen Planas',
        'option_c' => 'Maria Orosa',
        'option_d' => 'Geronima Pecson',
        'correct_option' => 'a',
        'fun_fact' => 'Fe del Mundo was the first woman admitted to Harvard Medical School and founded the first pediatric hospital in the Philippines.',
        'difficulty' => 'hard'
    ],
    [
        'question' => 'Sino ang "Grand Old Man of Philippine Politics" na naglingkod bilang Pangulo ng 1944-1946?',
        'option_a' => 'Manuel Quezon',
        'option_b' => 'Sergio Osmeña',
        'option_c' => 'Manuel Roxas',
        'option_d' => 'Elpidio Quirino',
        'correct_option' => 'b',
        'fun_fact' => 'Sergio Osmeña was the first Vice President of the Philippines and assumed the presidency after Quezon\'s death during WWII.',
        'difficulty' => 'medium'
    ]
];

foreach ($heroes_questions as $q) {
    $stmt = $pdo->prepare("
        INSERT INTO questions (category_id, question, option_a, option_b, option_c, option_d, correct_option, fun_fact, difficulty)
        VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $q['question'],
        $q['option_a'],
        $q['option_b'],
        $q['option_c'],
        $q['option_d'],
        $q['correct_option'],
        $q['fun_fact'],
        $q['difficulty']
    ]);
}

echo "Questions for Mga Bayani inserted successfully!\n";

// Insert questions for Category 2: Rebolusyon (Revolution)
echo "Inserting questions for Rebolusyon...\n";

$revolution_questions = [
    [
        'question' => 'Saan nangyari ang unang sigaw ng rebolusyon laban sa mga Kastila?',
        'option_a' => 'Balintawak',
        'option_b' => 'Pugad Lawin',
        'option_c' => 'Cavite',
        'option_d' => 'Biak-na-Bato',
        'correct_option' => 'b',
        'fun_fact' => 'The Cry of Pugad Lawin on August 23, 1896, marked the start of the Philippine Revolution when Katipuneros tore their cedulas.',
        'difficulty' => 'easy'
    ],
    [
        'question' => 'Anong taon itinatag ang Katipunan?',
        'option_a' => '1892',
        'option_b' => '1896',
        'option_c' => '1898',
        'option_d' => '1901',
        'correct_option' => 'a',
        'fun_fact' => 'The Katipunan was founded on July 7, 1892, in a house on Azcarraga Street (now Claro M. Recto Avenue) in Manila.',
        'difficulty' => 'easy'
    ],
    [
        'question' => 'Sino ang naging unang Pangulo ng Unang Republika ng Pilipinas?',
        'option_a' => 'Andres Bonifacio',
        'option_b' => 'Emilio Aguinaldo',
        'option_c' => 'Manuel Quezon',
        'option_d' => 'Sergio Osmeña',
        'correct_option' => 'b',
        'fun_fact' => 'Emilio Aguinaldo proclaimed Philippine independence on June 12, 1898, in Kawit, Cavite, establishing the First Philippine Republic.',
        'difficulty' => 'easy'
    ],
    [
        'question' => 'Anong kasunduan ang nagtapos sa Rebolusyong Pilipino laban sa Espanya?',
        'option_a' => 'Treaty of Paris',
        'option_b' => 'Treaty of Biak-na-Bato',
        'option_c' => 'Pact of Zanjon',
        'option_d' => 'Treaty of Versailles',
        'correct_option' => 'a',
        'fun_fact' => 'The Treaty of Paris (1898) ended the Spanish-American War, with Spain ceding the Philippines to the US for $20 million.',
        'difficulty' => 'medium'
    ],
    [
        'question' => 'Sino ang nagtatag ng La Liga Filipina, isang organisasyon na naglalayong pagkaisahin ang Pilipinas?',
        'option_a' => 'Andres Bonifacio',
        'option_b' => 'Jose Rizal',
        'option_c' => 'Marcelo del Pilar',
        'option_d' => 'Graciano Lopez Jaena',
        'correct_option' => 'b',
        'fun_fact' => 'La Liga Filipina was founded by Rizal in 1892. Though it was short-lived due to his exile, it inspired the formation of the Katipunan.',
        'difficulty' => 'medium'
    ],
    [
        'question' => 'Anong pangalan ng pahayagan na itinatag ni Marcelo del Pilar sa Espanya?',
        'option_a' => 'La Solidaridad',
        'option_b' => 'Diario de Manila',
        'option_c' => 'La Independencia',
        'option_d' => 'El Heraldo',
        'correct_option' => 'a',
        'fun_fact' => 'La Solidaridad was the official newspaper of the Propaganda Movement in Spain, advocating for Philippine reforms.',
        'difficulty' => 'medium'
    ],
    [
        'question' => 'Sino ang "Hero of the Battle of Tirad Pass"?',
        'option_a' => 'Antonio Luna',
        'option_b' => 'Gregorio del Pilar',
        'option_c' => 'Emilio Aguinaldo',
        'option_d' => 'Miguel Malvar',
        'correct_option' => 'b',
        'fun_fact' => 'The Battle of Tirad Pass on December 2, 1899, was dubbed by the Americans as "the Philippines\' Thermopylae" for its heroic last stand.',
        'difficulty' => 'medium'
    ],
    [
        'question' => 'Anong kasunduan ang nilagdaan sa Biak-na-Bato noong 1897?',
        'option_a' => 'Treaty of Paris',
        'option_b' => 'Pact of Biak-na-Bato',
        'option_c' => 'Truce of Manila',
        'option_d' => 'Cavite Agreement',
        'correct_option' => 'b',
        'fun_fact' => 'The Pact of Biak-na-Bato established a truce between revolutionaries and Spain, with Aguinaldo going into exile in Hong Kong.',
        'difficulty' => 'hard'
    ],
    [
        'question' => 'Sino ang heneral na tinaguriang "General of the Revolution" at nagpatuloy sa pakikibaka matapos bumitaw si Aguinaldo?',
        'option_a' => 'Antonio Luna',
        'option_b' => 'Miguel Malvar',
        'option_c' => 'Gregorio del Pilar',
        'option_d' => 'Vicente Lukban',
        'correct_option' => 'b',
        'fun_fact' => 'Miguel Malvar continued the resistance against American forces after Aguinaldo\'s capture and was the last general to surrender in 1902.',
        'difficulty' => 'hard'
    ],
    [
        'question' => 'Anong petsa ipinagdidiwang ang Araw ng Kalayaan ng Pilipinas?',
        'option_a' => 'Hulyo 4',
        'option_b' => 'Hunyo 12',
        'option_c' => 'Agosto 23',
        'option_d' => 'Enero 23',
        'correct_option' => 'b',
        'fun_fact' => 'June 12, 1898, was when Aguinaldo proclaimed independence from Spain. The US granted independence on July 4, 1946, but June 12 became the official holiday in 1964.',
        'difficulty' => 'easy'
    ]
];

foreach ($revolution_questions as $q) {
    $stmt = $pdo->prepare("
        INSERT INTO questions (category_id, question, option_a, option_b, option_c, option_d, correct_option, fun_fact, difficulty)
        VALUES (2, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $q['question'],
        $q['option_a'],
        $q['option_b'],
        $q['option_c'],
        $q['option_d'],
        $q['correct_option'],
        $q['fun_fact'],
        $q['difficulty']
    ]);
}

echo "Questions for Rebolusyon inserted successfully!\n";

// Insert questions for Category 3: Sinaunang Pilipinas (Pre-colonial)
echo "Inserting questions for Sinaunang Pilipinas...\n";

$precolonial_questions = [
    [
        'question' => 'Ano ang tawag sa pinakamataas na pinuno sa sinaunang barangay?',
        'option_a' => 'Datu',
        'option_b' => 'Rajah',
        'option_c' => 'Sultan',
        'option_d' => 'Lakan',
        'correct_option' => 'a',
        'fun_fact' => 'Datus were the chieftains who ruled barangays. In some regions, they were called Rajah (Muslim areas) or Lakan (Central Luzon).',
        'difficulty' => 'easy'
    ],
    [
        'question' => 'Ano ang pangunahing diyos ng mga sinaunang Pilipino?',
        'option_a' => 'Bathala',
        'option_b' => 'Anito',
        'option_c' => 'Diwata',
        'option_d' => 'Lambana',
        'correct_option' => 'a',
        'fun_fact' => 'Bathala was the supreme god in Tagalog mythology. Other deities included Apolaki (god of war) and Tala (goddess of stars).',
        'difficulty' => 'easy'
    ],
    [
        'question' => 'Anong sistema ng pagsulat ang ginamit ng mga sinaunang Pilipino?',
        'option_a' => 'Baybayin',
        'option_b' => 'Alibata',
        'option_c' => 'Kudlit',
        'option_d' => 'Sulat Tagalog',
        'correct_option' => 'a',
        'fun_fact' => 'Baybayin was the pre-colonial writing system. "Alibata" is a misnomer coined in the early 20th century.',
        'difficulty' => 'easy'
    ],
    [
        'question' => 'Ano ang tawag sa gawaing pangkalakalan ng mga Pilipino sa mga Tsino bago dumating ang mga Kastila?',
        'option_a' => 'Galleon Trade',
        'option_b' => 'Barter Trade',
        'option_c' => 'Manila-Acapulco Trade',
        'option_d' => 'Silk Road',
        'correct_option' => 'b',
        'fun_fact' => 'Pre-colonial Filipinos traded with Chinese, Japanese, Indians, and Arabs through barter, exchanging gold, pearls, and spices for silk, porcelain, and other goods.',
        'difficulty' => 'medium'
    ],
    [
        'question' => 'Sino ang unang dayuhang nakarating sa Pilipinas na naitala sa kasaysayan?',
        'option_a' => 'Ferdinand Magellan',
        'option_b' => 'Lapu-Lapu',
        'option_c' => 'Miguel Lopez de Legazpi',
        'option_d' => 'Ruy Lopez de Villalobos',
        'correct_option' => 'a',
        'fun_fact' => 'Ferdinand Magellan arrived in 1521 and named the islands "Islas de San Lazaro." He was killed by Lapu-Lapu in the Battle of Mactan.',
        'difficulty' => 'easy'
    ],
    [
        'question' => 'Ano ang tawag sa antas ng lipunan na binubuo ng mga maharlika o libreng tao?',
        'option_a' => 'Alipin',
        'option_b' => 'Maharlika',
        'option_c' => 'Timawa',
        'option_d' => 'Datu',
        'correct_option' => 'c',
        'fun_fact' => 'Timawa were the free commoners in pre-colonial society. Maharlika were warriors, while Alipin were servants or slaves.',
        'difficulty' => 'medium'
    ],
    [
        'question' => 'Anong pulo ang tinitirhan ni Lapu-Lapu noong panahon ni Magellan?',
        'option_a' => 'Cebu',
        'option_b' => 'Mactan',
        'option_c' => 'Bohol',
        'option_d' => 'Leyte',
        'correct_option' => 'b',
        'fun_fact' => 'Lapu-Lapu was the chieftain of Mactan Island. His victory over Magellan in 1521 made him the first Filipino to resist foreign colonization.',
        'difficulty' => 'easy'
    ],
    [
        'question' => 'Ano ang pangalan ng unang tao na natagpuan sa Tabon Cave, Palawan?',
        'option_a' => 'Tabon Man',
        'option_b' => 'Callao Man',
        'option_c' => 'Aurora Man',
        'option_d' => 'Peñablanca Man',
        'correct_option' => 'a',
        'fun_fact' => 'Tabon Man, dating back 22,000-24,000 years, is among the oldest human remains found in the Philippines, indicating early human settlement.',
        'difficulty' => 'hard'
    ],
    [
        'question' => 'Ano ang tawag sa ritwal ng pagpapakasal sa sinaunang Pilipino?',
        'option_a' => 'Kasalan',
        'option_b' => 'Pamumuluan',
        'option_c' => 'Pahiyas',
        'option_d' => 'Kagaw',
        'correct_option' => 'b',
        'fun_fact' => 'Pamumuluan was the pre-colonial wedding ceremony where the couple exchanged vows in the presence of the community and ancestors.',
        'difficulty' => 'hard'
    ],
    [
        'question' => 'Anong relihiyon ang naniniwala sa maraming diyos?',
        'option_a' => 'Monotheism',
        'option_b' => 'Polytheism',
        'option_c' => 'Animism',
        'option_d' => 'Atheism',
        'correct_option' => 'c',
        'fun_fact' => 'Pre-colonial Filipinos practiced animism, believing that spirits inhabited natural objects like trees, rocks, and rivers.',
        'difficulty' => 'medium'
    ]
];

foreach ($precolonial_questions as $q) {
    $stmt = $pdo->prepare("
        INSERT INTO questions (category_id, question, option_a, option_b, option_c, option_d, correct_option, fun_fact, difficulty)
        VALUES (3, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $q['question'],
        $q['option_a'],
        $q['option_b'],
        $q['option_c'],
        $q['option_d'],
        $q['correct_option'],
        $q['fun_fact'],
        $q['difficulty']
    ]);
}

echo "Questions for Sinaunang Pilipinas inserted successfully!\n";

// Insert questions for Category 4: Panahon ng Kastila (Spanish Era)
echo "Inserting questions for Panahon ng Kastila...\n";

$spanish_questions = [
    [
        'question' => 'Ilang taon nagtagal ang panahon ng Kastila sa Pilipinas?',
        'option_a' => '300 taon',
        'option_b' => '333 taon',
        'option_c' => '350 taon',
        'option_d' => '400 taon',
        'correct_option' => 'b',
        'fun_fact' => 'Spanish colonial rule lasted from 1565 (Miguel Lopez de Legazpi) to 1898, totaling 333 years.',
        'difficulty' => 'easy'
    ],
    [
        'question' => 'Sino ang unang Gobernador-Heneral ng Pilipinas?',
        'option_a' => 'Miguel Lopez de Legazpi',
        'option_b' => 'Ferdinand Magellan',
        'option_c' => 'Juan de Salcedo',
        'option_d' => 'Martin de Goiti',
        'correct_option' => 'a',
        'fun_fact' => 'Legazpi established the first Spanish settlement in Cebu in 1565 and later moved to Manila, making it the colonial capital.',
        'difficulty' => 'easy'
    ],
    [
        'question' => 'Ano ang tawag sa sistema ng pagbubuwis na ipinatupad ng mga Kastila?',
        'option_a' => 'Polo y Servicio',
        'option_b' => 'Cedula',
        'option_c' => 'Tributo',
        'option_d' => 'Bandala',
        'correct_option' => 'c',
        'fun_fact' => 'Tributo was a tax paid by natives to Spain. Polo y Servicio was forced labor, while Bandala was the forced sale of crops to the government.',
        'difficulty' => 'medium'
    ],
    [
        'question' => 'Anong pangalan ng barkong ginamit sa kalakalan sa pagitan ng Manila at Acapulco?',
        'option_a' => 'Galleon',
        'option_b' => 'Carrack',
        'option_c' => 'Caravel',
        'option_d' => 'Frigate',
        'correct_option' => 'a',
        'fun_fact' => 'The Manila-Acapulco Galleon Trade (1565-1815) connected Asia to the Americas, bringing silver from Mexico and goods like silk, porcelain, and spices from China.',
        'difficulty' => 'medium'
    ],
    [
        'question' => 'Sino ang nagtatag ng Unang Republika ng Pilipinas sa Malolos, Bulacan?',
        'option_a' => 'Emilio Aguinaldo',
        'option_b' => 'Andres Bonifacio',
        'option_c' => 'Apolinario Mabini',
        'option_d' => 'Manuel Quezon',
        'correct_option' => 'a',
        'fun_fact' => 'The Malolos Congress convened in 1898 to draft the first Philippine Constitution, establishing Asia\'s first constitutional republic.',
        'difficulty' => 'easy'
    ],
    [
        'question' => 'Ano ang tawag sa patakaran ng mga Kastila na nagpapatayo ng mga bayan sa paligid ng simbahan?',
        'option_a' => 'Reduccion',
        'option_b' => 'Encomienda',
        'option_c' => 'Hacienda',
        'option_d' => 'Parish',
        'correct_option' => 'a',
        'fun_fact' => 'Reduccion consolidated scattered communities into centralized towns centered around a church, making it easier for Spain to govern and convert natives.',
        'difficulty' => 'hard'
    ],
    [
        'question' => 'Sino ang "Hero of the Clergy" na nagtatag ng Kapatirang Pang-Akademikang Filipino?',
        'option_a' => 'Jose Burgos',
        'option_b' => 'Mariano Gomez',
        'option_c' => 'Jacinto Zamora',
        'option_d' => 'Pedro Pelaez',
        'correct_option' => 'd',
        'fun_fact' => 'Father Pedro Pelaez advocated for secularization and Filipino priests. His work inspired the GOMBURZA priests who were executed in 1872.',
        'difficulty' => 'hard'
    ],
    [
        'question' => 'Anong pangalan ng tatlong paring ineksekusyon noong 1872 na nagsimula ng rebolusyon?',
        'option_a' => 'GOMBURZA',
        'option_b' => 'RIZAL',
        'option_c' => 'BONIFACIO',
        'option_d' => 'MABINI',
        'correct_option' => 'a',
        'fun_fact' => 'GOMBURZA (Gomez, Burgos, Zamora) were executed by garrote in 1872 for alleged involvement in the Cavite Mutiny, sparking nationalist sentiment.',
        'difficulty' => 'medium'
    ],
    [
        'question' => 'Ano ang tawag sa sistema ng pagmamay-ari ng lupa na ipinatupad ng mga Kastila?',
        'option_a' => 'Encomienda',
        'option_b' => 'Hacienda',
        'option_c' => 'Land Grant',
        'option_d' => 'Friar Estate',
        'correct_option' => 'a',
        'fun_fact' => 'The Encomienda System granted Spanish colonists the right to collect taxes from natives in exchange for protection and Christianization.',
        'difficulty' => 'medium'
    ],
    [
        'question' => 'Sino ang nagtatag ng Universidad de Santo Tomas, ang pinakamatandang unibersidad sa Pilipinas?',
        'option_a' => 'Miguel de Benavides',
        'option_b' => 'Diego de Herrera',
        'option_c' => 'Fernando de Castro',
        'option_d' => 'Juan de Salcedo',
        'correct_option' => 'a',
        'fun_fact' => 'UST was founded in 1611 by Miguel de Benavides, making it Asia\'s oldest existing university and older than Harvard by 25 years.',
        'difficulty' => 'hard'
    ]
];

foreach ($spanish_questions as $q) {
    $stmt = $pdo->prepare("
        INSERT INTO questions (category_id, question, option_a, option_b, option_c, option_d, correct_option, fun_fact, difficulty)
        VALUES (4, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $q['question'],
        $q['option_a'],
        $q['option_b'],
        $q['option_c'],
        $q['option_d'],
        $q['correct_option'],
        $q['fun_fact'],
        $q['difficulty']
    ]);
}

echo "Questions for Panahon ng Kastila inserted successfully!\n";

// Insert questions for Category 5: Ika-20 Siglo (20th Century)
echo "Inserting questions for Ika-20 Siglo...\n";

$modern_questions = [
    [
        'question' => 'Ilang taon nanalo ang Pilipinas sa Commonwealth?',
        'option_a' => '10 taon',
        'option_b' => '12 taon',
        'option_c' => '15 taon',
        'option_d' => '20 taon',
        'correct_option' => 'a',
        'fun_fact' => 'The Commonwealth period lasted from 1935 to 1946, preparing the Philippines for full independence after American colonial rule.',
        'difficulty' => 'easy'
    ],
    [
        'question' => 'Sino ang unang Pangulo ng Commonwealth ng Pilipinas?',
        'option_a' => 'Manuel Quezon',
        'option_b' => 'Sergio Osmeña',
        'option_c' => 'Manuel Roxas',
        'option_d' => 'Jose Laurel',
        'correct_option' => 'a',
        'fun_fact' => 'Manuel Quezon served as President of the Commonwealth from 1935 until his death in 1944. He was known as the "Father of the National Language."',
        'difficulty' => 'easy'
    ],
    [
        'question' => 'Anong taon sumalakay ang Hapon sa Pilipinas?',
        'option_a' => '1941',
        'option_b' => '1942',
        'option_c' => '1943',
        'option_d' => '1944',
        'correct_option' => 'a',
        'fun_fact' => 'Japan invaded the Philippines on December 8, 1941, just hours after attacking Pearl Harbor, leading to three years of occupation.',
        'difficulty' => 'easy'
    ],
    [
        'question' => 'Sino ang Pangulo ng Pilipinas noong panahon ng Hapon?',
        'option_a' => 'Manuel Quezon',
        'option_b' => 'Jose P. Laurel',
        'option_c' => 'Sergio Osmeña',
        'option_d' => 'Manuel Roxas',
        'correct_option' => 'b',
        'fun_fact' => 'Jose P. Laurel served as President of the Second Republic under Japanese occupation from 1943 to 1945.',
        'difficulty' => 'medium'
    ],
    [
        'question' => 'Anong pangalan ng kilusang nagtanggol sa Bataan noong WWII?',
        'option_a' => 'Bataan Death March',
        'option_b' => 'Battle of Bataan',
        'option_c' => 'Defense of Bataan',
        'option_d' => 'Bataan Resistance',
        'correct_option' => 'b',
        'fun_fact' => 'The Battle of Bataan (1942) lasted three months before American and Filipino forces surrendered, followed by the infamous Bataan Death March.',
        'difficulty' => 'medium'
    ],
    [
        'question' => 'Sino ang unang babaeng Pangulo ng Pilipinas?',
        'option_a' => 'Imelda Marcos',
        'option_b' => 'Corazon Aquino',
        'option_c' => 'Gloria Arroyo',
        'option_d' => 'Miriam Santiago',
        'correct_option' => 'b',
        'fun_fact' => 'Corazon Aquino became President after the 1986 EDSA People Power Revolution, restoring democracy after 20 years of martial law.',
        'difficulty' => 'easy'
    ],
    [
        'question' => 'Anong taon ipinatupad ang Batas Militar sa Pilipinas?',
        'option_a' => '1970',
        'option_b' => '1972',
        'option_c' => '1975',
        'option_d' => '1980',
        'correct_option' => 'b',
        'fun_fact' => 'President Ferdinand Marcos declared Martial Law (Proclamation 1081) on September 21, 1972, which lasted until 1981.',
        'difficulty' => 'easy'
    ],
    [
        'question' => 'Ano ang tawag sa kilusang nagpatalsik kay Marcos noong 1986?',
        'option_a' => 'People Power Revolution',
        'option_b' => 'EDSA Revolution',
        'option_c' => 'Yellow Revolution',
        'option_d' => 'Lahat ng nabanggit',
        'correct_option' => 'd',
        'fun_fact' => 'The EDSA People Power Revolution (February 22-25, 1986) was a nonviolent uprising that ousted Marcos and installed Corazon Aquino as president.',
        'difficulty' => 'easy'
    ],
    [
        'question' => 'Sino ang "Father of Philippine Independence" na naglagda ng Kasunduan sa Paris?',
        'option_a' => 'Emilio Aguinaldo',
        'option_b' => 'Manuel Quezon',
        'option_c' => 'Sergio Osmeña',
        'option_d' => 'Carlos Romulo',
        'correct_option' => 'c',
        'fun_fact' => 'Sergio Osmeña was part of the Philippine mission that signed the Treaty of Manila in 1946, granting full independence from the US.',
        'difficulty' => 'hard'
    ],
    [
        'question' => 'Anong pangalan ng base militar ng US na nasa Pilipinas hanggang 1992?',
        'option_a' => 'Clark Air Base',
        'option_b' => 'Subic Naval Base',
        'option_c' => 'Lahat ng nabanggit',
        'option_d' => 'Wala sa nabanggit',
        'correct_option' => 'c',
        'fun_fact' => 'The US maintained Clark Air Base and Subic Naval Base until 1991, when the Philippine Senate rejected the bases treaty, leading to their withdrawal in 1992.',
        'difficulty' => 'medium'
    ]
];

foreach ($modern_questions as $q) {
    $stmt = $pdo->prepare("
        INSERT INTO questions (category_id, question, option_a, option_b, option_c, option_d, correct_option, fun_fact, difficulty)
        VALUES (5, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $q['question'],
        $q['option_a'],
        $q['option_b'],
        $q['option_c'],
        $q['option_d'],
        $q['correct_option'],
        $q['fun_fact'],
        $q['difficulty']
    ]);
}

echo "Questions for Ika-20 Siglo inserted successfully!\n";
echo "Database seeded successfully!\n";
echo "Total questions inserted: 50\n";
echo "<br><a href='../index.php'>Go to Bayani Quiz</a>";
?>
