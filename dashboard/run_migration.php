<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/db.php';

$db = getDB();

$sql = file_get_contents(__DIR__ . '/migration.sql');

// Split SQL into individual statements (handling IF NOT EXISTS)
$statements = explode(';', $sql);

foreach ($statements as $statement) {
    $statement = trim($statement);
    if (empty($statement)) continue;
    
    try {
        $db->exec($statement);
        echo "Executed: " . substr($statement, 0, 50) . "...\n";
    } catch (PDOException $e) {
        // Ignore duplicate column/table errors
        if (strpos($e->getMessage(), 'Duplicate column') !== false || 
            strpos($e->getMessage(), 'already exists') !== false) {
            echo "Skipped (already exists): " . substr($statement, 0, 50) . "...\n";
        } else {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
}

echo "\nMigration completed!\n";
