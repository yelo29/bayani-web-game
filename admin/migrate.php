<?php
/**
 * Bayani World - RPG Migration Script
 * Creates new RPG tables and inserts seed data
 * Token: BayaniQuiz2026
 */

session_start();

// Token protection
$requiredToken = 'BayaniQuiz2026';
if (!isset($_GET['token']) || $_GET['token'] !== $requiredToken) {
    die('Access denied. Invalid token.');
}

require_once '../includes/db.php';

try {
    $pdo = getDB();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h1>Bayani World RPG Migration</h1>";
    echo "<pre>";

    // Create regions table
    echo "Creating regions table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS regions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            province VARCHAR(100),
            island_group ENUM('luzon','visayas','mindanao') NOT NULL,
            min_level INT DEFAULT 1,
            description TEXT,
            background_color VARCHAR(20),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✓ regions table created\n\n";

    // Create enemies table
    echo "Creating enemies table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS enemies (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            region_id INT NOT NULL,
            category_id INT NOT NULL,
            hp INT DEFAULT 100,
            attack INT DEFAULT 20,
            defense INT DEFAULT 10,
            xp_reward INT DEFAULT 50,
            description TEXT,
            era VARCHAR(50),
            FOREIGN KEY (region_id) REFERENCES regions(id),
            FOREIGN KEY (category_id) REFERENCES categories(id)
        )
    ");
    echo "✓ enemies table created\n\n";

    // Create items table
    echo "Creating items table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            type ENUM('weapon','armor','scroll','potion') NOT NULL,
            description TEXT,
            power INT DEFAULT 10,
            rarity ENUM('common','rare','legendary') DEFAULT 'common',
            region_id INT NULL,
            FOREIGN KEY (region_id) REFERENCES regions(id)
        )
    ");
    echo "✓ items table created\n\n";

    // Create inventory table
    echo "Creating inventory table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS inventory (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            item_id INT NOT NULL,
            equipped TINYINT DEFAULT 0,
            earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (item_id) REFERENCES items(id)
        )
    ");
    echo "✓ inventory table created\n\n";

    // Create story_progress table
    echo "Creating story_progress table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS story_progress (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            chapter INT DEFAULT 1,
            scene INT DEFAULT 1,
            completed TINYINT DEFAULT 0,
            choices TEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    echo "✓ story_progress table created\n\n";

    // Create region_progress table
    echo "Creating region_progress table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS region_progress (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            region_id INT NOT NULL,
            unlocked TINYINT DEFAULT 0,
            battles_won INT DEFAULT 0,
            completed TINYINT DEFAULT 0,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (region_id) REFERENCES regions(id)
        )
    ");
    echo "✓ region_progress table created\n\n";

    // Create battle_log table
    echo "Creating battle_log table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS battle_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            enemy_id INT NOT NULL,
            won TINYINT DEFAULT 0,
            damage_dealt INT DEFAULT 0,
            damage_taken INT DEFAULT 0,
            xp_earned INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (enemy_id) REFERENCES enemies(id)
        )
    ");
    echo "✓ battle_log table created\n\n";

    // Add player_hp columns to users table
    echo "Adding player_hp columns to users table...\n";
    $pdo->exec("
        ALTER TABLE users 
        ADD COLUMN IF NOT EXISTS player_hp INT DEFAULT 100,
        ADD COLUMN IF NOT EXISTS player_max_hp INT DEFAULT 100
    ");
    echo "✓ player_hp columns added to users table\n\n";

    // Add UNIQUE KEY to regions.name
    echo "Adding UNIQUE KEY to regions.name...\n";
    $pdo->exec("
        ALTER TABLE regions 
        ADD UNIQUE KEY unique_name (name)
    ");
    echo "✓ UNIQUE KEY added to regions.name\n\n";

    // Add streak columns to users table
    echo "Adding streak columns to users table...\n";
    $pdo->exec("
        ALTER TABLE users 
        ADD COLUMN IF NOT EXISTS current_streak INT DEFAULT 0,
        ADD COLUMN IF NOT EXISTS best_streak INT DEFAULT 0
    ");
    echo "✓ streak columns added to users table\n\n";

    // Add coins column to users table
    echo "Adding coins column to users table...\n";
    $pdo->exec("
        ALTER TABLE users 
        ADD COLUMN IF NOT EXISTS coins INT DEFAULT 0
    ");
    echo "✓ coins column added to users table\n\n";

    // Insert seed data for regions
    echo "Inserting seed data for regions...\n";
    $regions = [
        ['Maynila', 'Metro Manila', 'luzon', 1, 'The capital city and heart of the Philippine revolution.', '#0038A8'],
        ['Cebu', 'Cebu', 'visayas', 3, 'The Queen City of the South, where Lapu-Lapu fought Magellan.', '#CE1126'],
        ['Davao', 'Davao del Sur', 'mindanao', 5, 'The largest city in the Philippines, home to Mount Apo.', '#16a34a'],
        ['Vigan', 'Ilocos Sur', 'luzon', 4, 'A UNESCO World Heritage site with Spanish colonial architecture.', '#FCD116'],
        ['Zamboanga', 'Zamboanga del Sur', 'mindanao', 6, 'The City of Flowers, known for its rich cultural heritage.', '#dc2626']
    ];

    foreach ($regions as $region) {
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO regions (name, province, island_group, min_level, description, background_color)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute($region);
        echo "✓ Inserted region: {$region[0]}\n";
    }
    echo "\n";

    // Get region IDs for enemy seeding
    $regionIds = [];
    $stmt = $pdo->query("SELECT id, name FROM regions ORDER BY id");
    while ($row = $stmt->fetch()) {
        $regionIds[$row['name']] = $row['id'];
    }

    // Get category IDs for enemy seeding
    $categoryIds = [];
    $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY id");
    while ($row = $stmt->fetch()) {
        $categoryIds[$row['name']] = $row['id'];
    }

    // Insert seed data for enemies (3 per region)
    echo "Inserting seed data for enemies...\n";
    $enemies = [
        // Maynila (History category)
        ['Conquistador', $regionIds['Maynila'], $categoryIds['History'] ?? 1, 120, 25, 15, 60, 'Spanish colonial soldier who oppressed the natives.', 'Spanish Colonial'],
        ['Corrupt Alcalde', $regionIds['Maynila'], $categoryIds['History'] ?? 1, 100, 20, 10, 50, 'A corrupt Spanish official who abused his power.', 'Spanish Colonial'],
        ['Friar', $regionIds['Maynila'], $categoryIds['History'] ?? 1, 90, 18, 8, 45, 'A Spanish friar who exploited the Filipino people.', 'Spanish Colonial'],
        
        // Cebu (History category)
        ['Magellan', $regionIds['Cebu'], $categoryIds['History'] ?? 1, 150, 30, 20, 80, 'The Portuguese explorer who was defeated by Lapu-Lapu.', 'Pre-Colonial'],
        ['Pirate', $regionIds['Cebu'], $categoryIds['History'] ?? 1, 80, 15, 5, 40, 'A pirate who raided coastal villages.', 'Pre-Colonial'],
        ['Rajah\'s Rival', $regionIds['Cebu'], $categoryIds['History'] ?? 1, 110, 22, 12, 55, 'A rival chieftain seeking power.', 'Pre-Colonial'],
        
        // Davao (History category)
        ['Japanese Invader', $regionIds['Davao'], $categoryIds['History'] ?? 1, 130, 28, 18, 70, 'A Japanese soldier during World War II.', 'World War II'],
        ['Traitor', $regionIds['Davao'], $categoryIds['History'] ?? 1, 95, 20, 10, 50, 'A Filipino who collaborated with the enemy.', 'World War II'],
        ['Warlord', $regionIds['Davao'], $categoryIds['History'] ?? 1, 140, 32, 22, 75, 'A local warlord who terrorized the region.', 'World War II'],
        
        // Vigan (Culture category)
        ['Tax Collector', $regionIds['Vigan'], $categoryIds['Culture'] ?? 2, 85, 16, 8, 45, 'A Spanish tax collector who oppressed the poor.', 'Spanish Colonial'],
        ['Slave Trader', $regionIds['Vigan'], $categoryIds['Culture'] ?? 2, 100, 20, 12, 55, 'A trader who sold Filipinos into slavery.', 'Spanish Colonial'],
        ['Cruel Overseer', $regionIds['Vigan'], $categoryIds['Culture'] ?? 2, 90, 18, 10, 48, 'An overseer who abused workers in the hacienda.', 'Spanish Colonial'],
        
        // Zamboanga (History category)
        ['Moro Raider', $regionIds['Zamboanga'], $categoryIds['History'] ?? 1, 115, 24, 14, 60, 'A Moro warrior who raided Spanish settlements.', 'Spanish Colonial'],
        ['Fort Defender', $regionIds['Zamboanga'], $categoryIds['History'] ?? 1, 125, 26, 16, 65, 'A defender of the Spanish fort in Zamboanga.', 'Spanish Colonial'],
        ['Rebel Leader', $regionIds['Zamboanga'], $categoryIds['History'] ?? 1, 135, 30, 18, 72, 'A rebel leader who fought against Spanish rule.', 'Spanish Colonial']
    ];

    foreach ($enemies as $enemy) {
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO enemies (name, region_id, category_id, hp, attack, defense, xp_reward, description, era)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute($enemy);
        echo "✓ Inserted enemy: {$enemy[0]}\n";
    }
    echo "\n";

    // Update enemy stats for better balance
    echo "Updating enemy stats for better balance...\n";
    $pdo->exec("UPDATE enemies SET hp=200, attack=18, defense=8 WHERE name='Conquistador'");
    $pdo->exec("UPDATE enemies SET hp=150, attack=15, defense=5 WHERE name='Corrupt Alcalde'");
    $pdo->exec("UPDATE enemies SET hp=120, attack=12, defense=4 WHERE name='Friar'");
    $pdo->exec("UPDATE enemies SET hp=280, attack=25, defense=12 WHERE name='Magellan'");
    $pdo->exec("UPDATE enemies SET hp=220, attack=28, defense=8 WHERE name='Pirate'");
    $pdo->exec("UPDATE enemies SET hp=250, attack=22, defense=10 WHERE name='Rajah\\'s Rival'");
    $pdo->exec("UPDATE enemies SET hp=350, attack=35, defense=15 WHERE name='Japanese Invader'");
    $pdo->exec("UPDATE enemies SET hp=300, attack=30, defense=12 WHERE name='Traitor'");
    $pdo->exec("UPDATE enemies SET hp=380, attack=38, defense=18 WHERE name='Warlord'");
    $pdo->exec("UPDATE enemies SET hp=260, attack=22, defense=10 WHERE name='Tax Collector'");
    $pdo->exec("UPDATE enemies SET hp=300, attack=28, defense=14 WHERE name='Slave Trader'");
    $pdo->exec("UPDATE enemies SET hp=320, attack=32, defense=16 WHERE name='Cruel Overseer'");
    $pdo->exec("UPDATE enemies SET hp=400, attack=40, defense=20 WHERE name='Moro Raider'");
    $pdo->exec("UPDATE enemies SET hp=450, attack=35, defense=25 WHERE name='Fort Defender'");
    $pdo->exec("UPDATE enemies SET hp=500, attack=42, defense=22 WHERE name='Rebel Leader'");
    echo "✓ Enemy stats updated\n\n";

    // Update Lucky Charm description and type
    echo "Updating Lucky Charm item...\n";
    $pdo->exec("UPDATE items SET description='Boosts XP earned per battle by 50%', type='scroll' WHERE name='Lucky Charm'");
    echo "✓ Lucky Charm updated\n\n";

    // Add battle_warning_dismissed column
    echo "Adding battle_warning_dismissed column...\n";
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS battle_warning_dismissed TINYINT DEFAULT 0");
    echo "✓ battle_warning_dismissed column added\n\n";

    // Insert seed data for items
    echo "Inserting seed data for items...\n";
    $items = [
        // Weapons
        ['Bolo', 'weapon', 'A traditional Filipino machete used for farming and combat.', 15, 'common', null],
        ['Kampilan', 'weapon', 'A large sword used by Filipino warriors and chiefs.', 25, 'rare', null],
        ['Sibat', 'weapon', 'A spear used by ancient Filipino warriors.', 20, 'common', null],
        
        // Armor
        ['Kalasag', 'armor', 'A traditional Filipino shield made of wood and rattan.', 18, 'common', null],
        ['Bahag', 'armor', 'A traditional loincloth worn by Filipino warriors.', 10, 'common', null],
        ['Salakot', 'armor', 'A traditional wide-brimmed hat made from rattan or bamboo.', 12, 'common', null],
        
        // Scrolls
        ['Rizal Scroll', 'scroll', 'Contains wisdom from Dr. Jose Rizal\'s writings.', 30, 'legendary', null],
        ['Bonifacio Scroll', 'scroll', 'Contains the revolutionary spirit of Andres Bonifacio.', 28, 'legendary', null],
        ['Luna Scroll', 'scroll', 'Contains military strategies of General Antonio Luna.', 26, 'rare', null],
        ['Mabini Scroll', 'scroll', 'Contains political wisdom of Apolinario Mabini.', 24, 'rare', null]
    ];

    foreach ($items as $item) {
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO items (name, type, description, power, rarity, region_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute($item);
        echo "✓ Inserted item: {$item[0]}\n";
    }
    echo "\n";

    echo "</pre>";
    echo "<h2>✓ Migration Complete!</h2>";
    echo "<p>All RPG tables have been created and seed data inserted.</p>";
    echo "<p><a href='../index.php'>Return to Home</a></p>";

} catch (PDOException $e) {
    echo "<h2>✗ Migration Failed</h2>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
