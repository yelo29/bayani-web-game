<?php
error_reporting(0);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$page_title = 'Game Content';
$db = getDB();

// Handle POST - Save JSON data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_json') {
    $game = $_POST['game'];
    $file = $_POST['file'];
    $content = $_POST['content'];
    
    $valid_games = ['handa', 'wikain', 'buhay', 'agham'];
    $valid_files = [
        'handa' => ['challenges.json', 'quiz.json'],
        'wikain' => ['words.json', 'parts_of_speech.json', 'sentences.json'],
        'buhay' => ['provinces.json', 'animals.json', 'events.json'],
        'agham' => ['cell.json', 'ecosystem.json', 'elemento.json']
    ];
    
    if (in_array($game, $valid_games) && in_array($file, $valid_files[$game])) {
        $filepath = __DIR__ . "/../games/$game/data/$file";
        
        // Validate JSON
        json_decode($content);
        if (json_last_error() === JSON_ERROR_NONE) {
            file_put_contents($filepath, $content);
            header('Location: /dashboard/games.php?success=1');
            exit;
        } else {
            $error = 'Invalid JSON format: ' . json_last_error_msg();
        }
    }
}

// Get game data
$games_data = [];

// Handa data
$games_data['handa'] = [
    'name' => 'Handa Ka Na (Math/Disaster Preparedness)',
    'files' => [
        'challenges.json' => json_decode(file_get_contents(__DIR__ . '/../games/handa/data/challenges.json'), true),
        'quiz.json' => json_decode(file_get_contents(__DIR__ . '/../games/handa/data/quiz.json'), true)
    ]
];

// Wikain data
$games_data['wikain'] = [
    'name' => 'Wikain (Filipino Language)',
    'files' => [
        'words.json' => json_decode(file_get_contents(__DIR__ . '/../games/wikain/data/words.json'), true),
        'parts_of_speech.json' => json_decode(file_get_contents(__DIR__ . '/../games/wikain/data/parts_of_speech.json'), true),
        'sentences.json' => json_decode(file_get_contents(__DIR__ . '/../games/wikain/data/sentences.json'), true)
    ]
];

// Buhay data
$games_data['buhay'] = [
    'name' => 'Buhay Pilipinas (Social Studies)',
    'files' => [
        'provinces.json' => json_decode(file_get_contents(__DIR__ . '/../games/buhay/data/provinces.json'), true),
        'animals.json' => json_decode(file_get_contents(__DIR__ . '/../games/buhay/data/animals.json'), true),
        'events.json' => json_decode(file_get_contents(__DIR__ . '/../games/buhay/data/events.json'), true)
    ]
];

// Agham data
$games_data['agham'] = [
    'name' => 'Agham (Science)',
    'files' => [
        'cell.json' => json_decode(file_get_contents(__DIR__ . '/../games/agham/data/cell.json'), true),
        'ecosystem.json' => json_decode(file_get_contents(__DIR__ . '/../games/agham/data/ecosystem.json'), true),
        'elemento.json' => json_decode(file_get_contents(__DIR__ . '/../games/agham/data/elemento.json'), true)
    ]
];

require_once __DIR__ . '/includes/header.php';
?>

<div class="space-y-6">
    <?php if (isset($error)): ?>
        <div class="bg-red-900/30 border border-red-600 rounded-lg p-4 mb-6">
            <p class="text-red-400 text-center">
                <i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error); ?>
            </p>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
        <div class="bg-green-900/30 border border-green-600 rounded-lg p-4 mb-6">
            <p class="text-green-400 text-center">
                <i class="fas fa-check-circle mr-2"></i>Data saved successfully
            </p>
        </div>
    <?php endif; ?>

    <?php foreach ($games_data as $game_key => $game): ?>
        <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
            <h3 class="text-base lg:text-lg font-bold text-white mb-4">
                <i class="fas fa-gamepad mr-2 text-[#0038A8]"></i><?php echo $game['name']; ?>
            </h3>
            
            <div class="space-y-4">
                <?php foreach ($game['files'] as $file_key => $file_data): ?>
                    <div class="bg-gray-700/50 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-white font-bold"><?php echo $file_key; ?></h4>
                            <span class="text-gray-400 text-sm"><?php echo count($file_data); ?> items</span>
                        </div>
                        
                        <button onclick="toggleEditor('<?php echo $game_key; ?>', '<?php echo $file_key; ?>')" class="bg-[#0038A8] hover:bg-[#0047b3] text-white px-4 py-2 rounded-lg transition text-sm mb-3">
                            <i class="fas fa-edit mr-2"></i>Edit Data
                        </button>
                        
                        <div id="editor-<?php echo $game_key; ?>-<?php echo $file_key; ?>" class="hidden">
                            <form method="POST">
                                <input type="hidden" name="action" value="save_json">
                                <input type="hidden" name="game" value="<?php echo $game_key; ?>">
                                <input type="hidden" name="file" value="<?php echo $file_key; ?>">
                                
                                <textarea 
                                    name="content" 
                                    class="w-full h-96 bg-gray-900 text-green-400 font-mono text-xs border border-gray-600 rounded-lg p-4 focus:outline-none focus:border-[#0038A8]"
                                    spellcheck="false"
                                ><?php echo htmlspecialchars(json_encode($file_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></textarea>
                                
                                <div class="flex gap-2 mt-3">
                                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition text-sm">
                                        <i class="fas fa-save mr-2"></i>Save Changes
                                    </button>
                                    <button type="button" onclick="toggleEditor('<?php echo $game_key; ?>', '<?php echo $file_key; ?>')" class="bg-gray-600 hover:bg-gray-500 text-white px-4 py-2 rounded-lg transition text-sm">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Preview -->
                        <div class="mt-4">
                            <h5 class="text-gray-400 text-sm font-bold mb-2">Preview (first 5 items):</h5>
                            <div class="bg-gray-900 rounded-lg p-3 overflow-x-auto">
                                <pre class="text-xs text-gray-300"><?php echo htmlspecialchars(json_encode(array_slice($file_data, 0, 5), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
function toggleEditor(game, file) {
    const editor = document.getElementById('editor-' + game + '-' + file);
    editor.classList.toggle('hidden');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
