<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$page_title = 'Database Migration';
$db = getDB();

$messages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_migration'])) {
    $sql = file_get_contents(__DIR__ . '/migration.sql');
    
    // Split SQL into individual statements
    $statements = explode(';', $sql);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement)) continue;
        
        try {
            $db->exec($statement);
            $messages[] = ['type' => 'success', 'text' => 'Executed: ' . substr($statement, 0, 60) . '...'];
        } catch (PDOException $e) {
            // Ignore duplicate column/table errors
            if (strpos($e->getMessage(), 'Duplicate column') !== false ||
                strpos($e->getMessage(), 'already exists') !== false ||
                strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $messages[] = ['type' => 'info', 'text' => 'Skipped (already exists): ' . substr($statement, 0, 60) . '...'];
            } else {
                $messages[] = ['type' => 'error', 'text' => 'Error: ' . $e->getMessage()];
            }
        }
    }
    
    $messages[] = ['type' => 'success', 'text' => 'Migration completed!'];
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="space-y-6">
    <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
        <h3 class="text-base lg:text-lg font-bold text-white mb-4">
            <i class="fas fa-database mr-2 text-[#0038A8]"></i>Database Migration
        </h3>
        
        <p class="text-gray-300 mb-4">
            This migration will add the necessary tables and columns for the enhanced admin dashboard features including:
        </p>
        <ul class="list-disc list-inside text-gray-400 mb-6 space-y-1">
            <li>Items table: drop_rate and level_requirement columns</li>
            <li>Users table: is_banned and ban_reason columns</li>
            <li>New game_settings table for global game settings</li>
            <li>New level_settings table for level progression</li>
            <li>New class_bonuses table for hero class bonuses</li>
            <li>New battle_log table for battle statistics</li>
        </ul>
        
        <?php if (!empty($messages)): ?>
            <div class="space-y-2 mb-6">
                <?php foreach ($messages as $msg): ?>
                    <div class="p-3 rounded-lg <?php echo $msg['type'] === 'success' ? 'bg-green-900/30 border border-green-600 text-green-400' : ($msg['type'] === 'error' ? 'bg-red-900/30 border border-red-600 text-red-400' : 'bg-blue-900/30 border border-blue-600 text-blue-400'); ?>">
                        <?php echo htmlspecialchars($msg['text']); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <button type="submit" name="run_migration" value="1" class="bg-[#0038A8] hover:bg-[#0047b3] text-white px-6 py-3 rounded-lg transition text-sm font-bold">
                <i class="fas fa-play mr-2"></i>Run Migration
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
