<?php
// Database Seeder - Password Protected
// Access via: seed.php?token=BayaniQuiz2026

session_start();

// Check for secret token
$secret_token = 'BayaniQuiz2026';
if (!isset($_GET['token']) || $_GET['token'] !== $secret_token) {
    die('Access denied. Invalid token.');
}

require_once '../includes/db.php';

$pdo = getDB();

// Drop existing tables (for clean re-seeding)
$pdo->exec("DROP TABLE IF EXISTS achievements");
$pdo->exec("DROP TABLE IF EXISTS scores");
$pdo->exec("DROP TABLE IF EXISTS users");
$pdo->exec("DROP TABLE IF EXISTS questions");
$pdo->exec("DROP TABLE IF EXISTS categories");

// Create categories table
$pdo->exec("
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    color VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Create questions table
$pdo->exec("
CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    question TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    option_d VARCHAR(255) NOT NULL,
    correct_option ENUM('A','B','C','D') NOT NULL,
    fun_fact TEXT,
    difficulty ENUM('easy','medium','hard') DEFAULT 'medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
)");

// Create users table with hero system
$pdo->exec("
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    hero_class ENUM('mandirigma','lakambini','mangkukulam') NULL,
    avatar_color VARCHAR(20) DEFAULT '#0038A8',
    xp INT DEFAULT 0,
    level INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

// Create scores table with user_id
$pdo->exec("
CREATE TABLE scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    player_name VARCHAR(100),
    category_id INT NOT NULL,
    score INT NOT NULL,
    total_questions INT NOT NULL,
    time_taken INT NOT NULL,
    xp_earned INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
)");

// Create achievements table
$pdo->exec("
CREATE TABLE achievements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    achievement_name VARCHAR(100) NOT NULL,
    achievement_description TEXT,
    earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)");

// Insert categories
$categories = [
    ['name' => 'Mga Bayani', 'description' => 'Learn about Philippine national heroes', 'icon' => 'fa-user-shield', 'color' => 'yellow'],
    ['name' => 'Kasaysayan', 'description' => 'Philippine history through the ages', 'icon' => 'fa-landmark', 'color' => 'red'],
    ['name' => 'Kultura', 'description' => 'Filipino culture and traditions', 'icon' => 'fa-music', 'color' => 'green'],
    ['name' => 'Heograpiya', 'description' => 'Philippine geography and landmarks', 'icon' => 'fa-map-marked-alt', 'color' => 'blue'],
    ['name' => 'Pamumuhay', 'description' => 'Daily life and Filipino values', 'icon' => 'fa-heart', 'color' => 'purple']
];

foreach ($categories as $cat) {
    $stmt = $pdo->prepare("INSERT INTO categories (name, description, icon, color) VALUES (?, ?, ?, ?)");
    $stmt->execute([$cat['name'], $cat['description'], $cat['icon'], $cat['color']]);
}

// Insert sample questions (10 per category)
$questions = [
    // Mga Bayani
    [1, 'Who is known as the "Hero of Tirad Pass"?', 'Andres Bonifacio', 'Emilio Aguinaldo', 'Gregorio del Pilar', 'Antonio Luna', 'C', 'Gregorio del Pilar died defending Tirad Pass with only 60 men against 500 American soldiers.', 'medium'],
    [1, 'Who founded the Katipunan?', 'Jose Rizal', 'Andres Bonifacio', 'Emilio Aguinaldo', 'Apolinario Mabini', 'B', 'The Katipunan was founded by Andres Bonifacio on July 7, 1892.', 'easy'],
    [1, 'Who wrote "Noli Me Tangere"?', 'Andres Bonifacio', 'Jose Rizal', 'Emilio Jacinto', 'Marcelo H. del Pilar', 'B', 'Noli Me Tangere was published in 1887 and exposed Spanish abuses.', 'easy'],
    [1, 'Who is the "Brains of the Revolution"?', 'Andres Bonifacio', 'Emilio Aguinaldo', 'Apolinario Mabini', 'Antonio Luna', 'C', 'Apolinario Mabini, despite being paralyzed, served as Aguinaldo\'s chief advisor.', 'medium'],
    [1, 'Who is the "Hero of Cavite"?', 'Gregorio del Pilar', 'Emilio Aguinaldo', 'Antonio Luna', 'Andres Bonifacio', 'B', 'Emilio Aguinaldo led many victories in Cavite province.', 'medium'],
    [1, 'Who is the "First President of the Philippines"?', 'Andres Bonifacio', 'Emilio Aguinaldo', 'Manuel L. Quezon', 'Sergio Osmeña', 'B', 'Emilio Aguinaldo became the first president of the First Philippine Republic in 1899.', 'easy'],
    [1, 'Who is the "Grand Old Man of Philippine Politics"?', 'Manuel L. Quezon', 'Sergio Osmeña', 'Manuel Roxas', 'Carlos P. Romulo', 'B', 'Sergio Osmeña served as president from 1944-1946 after Quezon.', 'hard'],
    [1, 'Who is the "Hero of Binakayan"?', 'Andres Bonifacio', 'Emilio Aguinaldo', 'Antonio Luna', 'Gregorio del Pilar', 'B', 'The Battle of Binakayan was a major victory against Spanish forces in 1896.', 'medium'],
    [1, 'Who is known as the "Hero of the Poor"?', 'Jose Rizal', 'Andres Bonifacio', 'Emilio Aguinaldo', 'Apolinario Mabini', 'B', 'Andres Bonifacio came from a poor family and fought for the masses.', 'easy'],
    [1, 'Who is the "Sublime Paralytic"?', 'Jose Rizal', 'Apolinario Mabini', 'Andres Bonifacio', 'Emilio Aguinaldo', 'B', 'Apolinario Mabini was paralyzed by polio but continued to serve the revolution.', 'hard'],
    
    // Kasaysayan
    [2, 'When did the Philippines declare independence from Spain?', 'June 12, 1898', 'July 4, 1946', 'August 30, 1896', 'January 1, 1900', 'A', 'Philippine independence was declared in Kawit, Cavite on June 12, 1898.', 'easy'],
    [2, 'What year did the Philippine-American War begin?', '1898', '1899', '1900', '1901', 'B', 'The war started on February 4, 1899 when American soldiers fired on Filipino troops.', 'medium'],
    [2, 'How long did the Japanese occupation last?', '2 years', '3 years', '4 years', '5 years', 'B', 'Japan occupied the Philippines from 1942 to 1945.', 'medium'],
    [2, 'What was the first Spanish settlement in the Philippines?', 'Manila', 'Cebu', 'Vigan', 'Davao', 'B', 'Miguel López de Legazpi established the first settlement in Cebu in 1565.', 'medium'],
    [2, 'When was the Katipunan founded?', 'July 7, 1892', 'August 23, 1896', 'December 30, 1896', 'June 12, 1898', 'A', 'Andres Bonifacio founded the Katipunan in Tondo, Manila.', 'easy'],
    [2, 'What was the Cry of Balintawak?', 'Start of revolution', 'Declaration of independence', 'End of Spanish rule', 'Start of American rule', 'A', 'The Cry of Balintawak marked the start of the Philippine Revolution in 1896.', 'medium'],
    [2, 'When did the Philippines become a US Commonwealth?', '1935', '1946', '1901', '1898', 'A', 'The Commonwealth was established on November 15, 1935 with Manuel L. Quezon as president.', 'hard'],
    [2, 'What year did Ferdinand Marcos declare Martial Law?', '1972', '1973', '1971', '1974', 'A', 'Martial Law was declared on September 21, 1972 through Proclamation 1081.', 'medium'],
    [2, 'When was the EDSA People Power Revolution?', 'February 1986', 'August 1983', 'January 1981', 'December 1989', 'A', 'The peaceful revolution lasted from February 22-25, 1986.', 'easy'],
    [2, 'When did the Philippines gain full independence from the US?', 'July 4, 1946', 'June 12, 1898', 'January 1, 1900', 'August 30, 1896', 'A', 'The US granted full independence on July 4, 1946.', 'easy'],
    
    // Kultura
    [3, 'What is the national flower of the Philippines?', 'Rose', 'Sampaguita', 'Orchid', 'Sunflower', 'B', 'Sampaguita (Jasminum sambac) was declared the national flower in 1934.', 'easy'],
    [3, 'What is the national language?', 'English', 'Spanish', 'Filipino', 'Chinese', 'C', 'Filipino (based on Tagalog) is the national language as per the 1987 Constitution.', 'easy'],
    [3, 'What is the traditional Filipino greeting?', 'Hello', 'Kamusta', 'Mabuhay', 'Salamat', 'C', 'Mabuhay is a traditional greeting meaning "long live" or "welcome".', 'easy'],
    [3, 'What is the national sport?', 'Basketball', 'Arnis', 'Boxing', 'Football', 'B', 'Arnis (Eskrima) was declared the national sport and martial art in 2009.', 'medium'],
    [3, 'What is the famous Filipino dish made from pork?', 'Adobo', 'Sinigang', 'Kare-Kare', 'Lechon', 'A', 'Adobo is often considered the unofficial national dish of the Philippines.', 'easy'],
    [3, 'What is the Filipino term for "thank you"?', 'Paalam', 'Salamat', 'Kamusta', 'Opo', 'B', 'Salamat is the Filipino word for thank you.', 'easy'],
    [3, 'What is the traditional Filipino house called?', 'Bahay Kubo', 'Mansion', 'Apartment', 'Condo', 'A', 'Bahay Kubo (Nipa Hut) is the traditional indigenous house.', 'easy'],
    [3, 'What is the famous Filipino Christmas tradition?', 'Trick or Treat', 'Simbang Gabi', 'Easter Egg Hunt', 'Halloween', 'B', 'Simbang Gabi (Midnight Mass) is a nine-day novena leading to Christmas.', 'medium'],
    [3, 'What is the Filipino term for "older brother"?', 'Ate', 'Kuya', 'Tito', 'Tita', 'B', 'Kuya is used to address an older brother or male cousin.', 'easy'],
    [3, 'What is the national bird?', 'Eagle', 'Maya', 'Philippine Eagle', 'Parrot', 'C', 'The Philippine Eagle (Pithecophaga jefferyi) is the national bird.', 'medium'],
    
    // Heograpiya
    [4, 'What is the capital city of the Philippines?', 'Cebu', 'Davao', 'Manila', 'Cagayan de Oro', 'C', 'Manila is the capital and most populous city of the Philippines.', 'easy'],
    [4, 'How many islands make up the Philippines?', '1,000', '5,000', '7,641', '10,000', 'C', 'The Philippines is an archipelago of 7,641 islands.', 'medium'],
    [4, 'What is the longest river in the Philippines?', 'Pasig River', 'Cagayan River', 'Pampanga River', 'Agusan River', 'B', 'The Cagayan River is approximately 505 kilometers long.', 'hard'],
    [4, 'What is the highest mountain in the Philippines?', 'Mount Apo', 'Mount Pulag', 'Mount Pinatubo', 'Mount Mayon', 'A', 'Mount Apo in Mindanao stands at 2,954 meters above sea level.', 'medium'],
    [4, 'Which region is known as the "Rice Granary of the Philippines"?', 'NCR', 'Central Luzon', 'Bicol', 'Visayas', 'B', 'Central Luzon produces most of the country\'s rice supply.', 'medium'],
    [4, 'What is the largest lake in the Philippines?', 'Laguna de Bay', 'Lake Taal', 'Lake Lanao', 'Lake Buhi', 'A', 'Laguna de Bay is the largest lake in Southeast Asia.', 'medium'],
    [4, 'Which volcano is known for its perfect cone shape?', 'Mount Pinatubo', 'Mount Mayon', 'Mount Taal', 'Mount Apo', 'B', 'Mount Mayon in Albay is famous for its symmetric cone shape.', 'easy'],
    [4, 'What ocean borders the Philippines on the east?', 'Atlantic Ocean', 'Indian Ocean', 'Pacific Ocean', 'Arctic Ocean', 'C', 'The Philippine Sea (part of the Pacific Ocean) borders the east.', 'easy'],
    [4, 'How many regions are there in the Philippines?', '12', '15', '17', '20', 'C', 'The Philippines has 17 administrative regions.', 'medium'],
    [4, 'What is the smallest province in the Philippines?', 'Batanes', 'Marinduque', 'Romblon', 'Guimaras', 'A', 'Batanes is the smallest province both in area and population.', 'hard'],
    
    // Pamumuhay
    [5, 'What is the Filipino value of "bayanihan"?', 'Individualism', 'Community unity', 'Selfishness', 'Isolation', 'B', 'Bayanihan refers to the spirit of communal unity and cooperation.', 'easy'],
    [5, 'What is "utang na loob"?', 'Debt of gratitude', 'Financial debt', 'Bad debt', 'No debt', 'A', 'Utang na loob is a sense of obligation to repay favors received.', 'medium'],
    [5, 'What is "pakikisama"?', 'Getting along with others', 'Fighting others', 'Ignoring others', 'Competing with others', 'A', 'Pakikisama is the value of maintaining harmonious relationships.', 'medium'],
    [5, 'What is the Filipino term for "respect"?', 'Galang', 'Bastos', 'Lait', 'Asar', 'A', 'Galang is the Filipino word for respect, especially towards elders.', 'easy'],
    [5, 'What is "po" and "opo" used for?', 'Insulting', 'Showing respect', 'Greeting', 'Farewell', 'B', 'Po and opo are particles used to show respect to elders.', 'easy'],
    [5, 'What is the traditional Filipino family structure?', 'Nuclear family only', 'Extended family', 'Single parent', 'No family', 'B', 'Filipino families typically include extended family members living together.', 'easy'],
    [5, 'What is "mano po"?', 'Hand gesture of respect', 'Fighting', 'Waving', 'Clapping', 'A', 'Mano po is the gesture of taking an elder\'s hand and placing it on one\'s forehead.', 'easy'],
    [5, 'What is the Filipino concept of "hiya"?', 'Shame/sense of propriety', 'Pride', 'Anger', 'Joy', 'A', 'Hiya is a sense of shame or propriety that guides social behavior.', 'medium'],
    [5, 'What is "pasalubong"?', 'Souvenir/gift', 'Food', 'Money', 'Clothes', 'A', 'Pasalubong is a gift brought back from a trip for family and friends.', 'easy'],
    [5, 'What is the Filipino value of "malasakit"?', 'Compassion/empathy', 'Indifference', 'Cruelty', 'Apathy', 'A', 'Malasakit is the feeling of compassion and concern for others.', 'medium']
];

foreach ($questions as $q) {
    $stmt = $pdo->prepare("INSERT INTO questions (category_id, question, option_a, option_b, option_c, option_d, correct_option, fun_fact, difficulty) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute($q);
}

echo "Database seeded successfully!<br>";
echo "Categories: " . count($categories) . "<br>";
echo "Questions: " . count($questions) . "<br>";
echo "Tables created: categories, questions, users, scores, achievements";
