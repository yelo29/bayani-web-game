<?php
error_reporting(0);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$page_title = 'Questions';
$db = getDB();

// Handle POST - Add new question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $category_id = (int)$_POST['category_id'];
    $question = trim($_POST['question']);
    $option_a = trim($_POST['option_a']);
    $option_b = trim($_POST['option_b']);
    $option_c = trim($_POST['option_c']);
    $option_d = trim($_POST['option_d']);
    $correct_option = $_POST['correct_option'];
    $difficulty = $_POST['difficulty'];
    $fun_fact = trim($_POST['fun_fact']);
    
    if ($question && $option_a && $option_b && $option_c && $option_d && $correct_option) {
        $stmt = $db->prepare("INSERT INTO questions (category_id, question, option_a, option_b, option_c, option_d, correct_option, difficulty, fun_fact) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$category_id, $question, $option_a, $option_b, $option_c, $option_d, $correct_option, $difficulty, $fun_fact]);
        header('Location: /dashboard/questions.php?success=1');
        exit;
    }
}

// Handle GET - Delete question
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM questions WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    header('Location: /dashboard/questions.php?deleted=1');
    exit;
}

// Handle POST - Bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action']) && isset($_POST['question_ids'])) {
    $action = $_POST['bulk_action'];
    $question_ids = $_POST['question_ids'];
    
    if (!empty($question_ids)) {
        $placeholders = str_repeat('?,', count($question_ids) - 1) . '?';
        
        if ($action === 'delete') {
            $stmt = $db->prepare("DELETE FROM questions WHERE id IN ($placeholders)");
            $stmt->execute($question_ids);
            header('Location: /dashboard/questions.php?bulk_deleted=1');
            exit;
        } elseif ($action === 'change_category' && isset($_POST['new_category_id'])) {
            $stmt = $db->prepare("UPDATE questions SET category_id = ? WHERE id IN ($placeholders)");
            $params = array_merge([(int)$_POST['new_category_id']], $question_ids);
            $stmt->execute($params);
            header('Location: /dashboard/questions.php?bulk_updated=1');
            exit;
        } elseif ($action === 'change_difficulty' && isset($_POST['new_difficulty'])) {
            $stmt = $db->prepare("UPDATE questions SET difficulty = ? WHERE id IN ($placeholders)");
            $params = array_merge([$_POST['new_difficulty']], $question_ids);
            $stmt->execute($params);
            header('Location: /dashboard/questions.php?bulk_updated=1');
            exit;
        }
    }
}

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$filter_difficulty = isset($_GET['difficulty']) ? $_GET['difficulty'] : '';

// Build query
$query = "SELECT q.id, q.question, q.option_a, q.option_b, q.option_c, q.option_d, q.correct_option, q.difficulty, c.name as category_name 
          FROM questions q 
          JOIN categories c ON q.category_id = c.id 
          WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND q.question LIKE ?";
    $params[] = "%$search%";
}

if ($filter_category) {
    $query .= " AND q.category_id = ?";
    $params[] = $filter_category;
}

if ($filter_difficulty) {
    $query .= " AND q.difficulty = ?";
    $params[] = $filter_difficulty;
}

// Get total count
$count_query = str_replace('SELECT q.id, q.question, q.option_a, q.option_b, q.option_c, q.option_d, q.correct_option, q.difficulty, c.name as category_name', 'SELECT COUNT(*)', $query);
$total_stmt = $db->prepare($count_query);
$total_stmt->execute($params);
$total = $total_stmt->fetchColumn();
$total_pages = ceil($total / $per_page);

// Get questions with category names
$query .= " ORDER BY q.id DESC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;

$stmt = $db->prepare($query);
$stmt->execute($params);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for dropdown
$categories = $db->query("SELECT id, name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/includes/header.php';
?>

<div class="space-y-6">
    <!-- Add Question Form -->
    <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
        <h3 class="text-base lg:text-lg font-bold text-white mb-4"><i class="fas fa-plus-circle mr-2"></i>Add New Question</h3>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 lg:gap-4">
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Category</label>
                    <select name="category_id" required class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-3 lg:px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Difficulty</label>
                    <select name="difficulty" required class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-3 lg:px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                        <option value="easy">Easy</option>
                        <option value="medium">Medium</option>
                        <option value="hard">Hard</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Question</label>
                    <textarea name="question" required rows="2" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-3 lg:px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm" placeholder="Enter the question..."></textarea>
                </div>
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Option A</label>
                    <input type="text" name="option_a" required class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-3 lg:px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm" placeholder="Option A">
                </div>
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Option B</label>
                    <input type="text" name="option_b" required class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-3 lg:px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm" placeholder="Option B">
                </div>
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Option C</label>
                    <input type="text" name="option_c" required class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-3 lg:px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm" placeholder="Option C">
                </div>
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Option D</label>
                    <input type="text" name="option_d" required class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-3 lg:px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm" placeholder="Option D">
                </div>
                <div>
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Correct Answer</label>
                    <select name="correct_option" required class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-3 lg:px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                        <option value="a">A</option>
                        <option value="b">B</option>
                        <option value="c">C</option>
                        <option value="d">D</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-gray-300 text-xs lg:text-sm font-bold mb-2">Fun Fact (Optional)</label>
                    <textarea name="fun_fact" rows="2" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-3 lg:px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm" placeholder="Fun fact about the answer..."></textarea>
                </div>
            </div>
            <button type="submit" class="mt-4 bg-[#0038A8] hover:bg-[#0047b3] text-white px-4 lg:px-6 py-2 rounded-lg transition text-sm">
                <i class="fas fa-save mr-1 lg:mr-2"></i><span class="hidden sm:inline">Save Question</span>
            </button>
        </form>
    </div>

    <!-- Filters -->
    <div class="bg-gray-800 rounded-xl p-4 lg:p-6 border border-gray-700">
        <form method="GET" class="flex flex-col lg:flex-row gap-3">
            <input
                type="text"
                name="search"
                value="<?php echo htmlspecialchars($search); ?>"
                placeholder="Search questions..."
                class="flex-1 bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm"
            >
            <select name="category" class="bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                <option value="0">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $filter_category == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="difficulty" class="bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                <option value="">All Difficulties</option>
                <option value="easy" <?php echo $filter_difficulty === 'easy' ? 'selected' : ''; ?>>Easy</option>
                <option value="medium" <?php echo $filter_difficulty === 'medium' ? 'selected' : ''; ?>>Medium</option>
                <option value="hard" <?php echo $filter_difficulty === 'hard' ? 'selected' : ''; ?>>Hard</option>
            </select>
            <div class="flex gap-2">
                <button type="submit" class="bg-[#0038A8] hover:bg-[#0047b3] text-white px-4 lg:px-6 py-2 rounded-lg transition text-sm flex-shrink-0">
                    <i class="fas fa-filter mr-1 lg:mr-2"></i><span class="hidden sm:inline">Filter</span>
                </button>
                <?php if ($search || $filter_category || $filter_difficulty): ?>
                    <a href="/dashboard/questions.php" class="bg-gray-600 hover:bg-gray-500 text-white px-4 lg:px-6 py-2 rounded-lg transition text-sm flex-shrink-0">
                        <i class="fas fa-times mr-1 lg:mr-2"></i><span class="hidden sm:inline">Clear</span>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Questions Table -->
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <form method="POST" id="bulkActionForm">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider w-12">
                                <input type="checkbox" id="selectAll" class="rounded bg-gray-600 border-gray-500 text-[#0038A8] focus:ring-[#0038A8]">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Question</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Difficulty</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Correct</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <?php foreach ($questions as $q): ?>
                            <tr class="hover:bg-gray-700/50">
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <input type="checkbox" name="question_ids[]" value="<?php echo $q['id']; ?>" class="question-checkbox rounded bg-gray-600 border-gray-500 text-[#0038A8] focus:ring-[#0038A8]">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo $q['id']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo htmlspecialchars($q['category_name']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-300"><?php echo htmlspecialchars(substr($q['question'], 0, 60)) . (strlen($q['question']) > 60 ? '...' : ''); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $diff_colors = ['easy' => 'bg-green-500', 'medium' => 'bg-yellow-500', 'hard' => 'bg-red-500'];
                                    $color = $diff_colors[$q['difficulty']] ?? 'bg-gray-500';
                                    ?>
                                    <span class="px-3 py-1 rounded-full text-xs font-medium text-white <?php echo $color; ?>">
                                        <?php echo ucfirst($q['difficulty']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-white"><?php echo strtoupper($q['correct_option']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="?delete=<?php echo $q['id']; ?>" onclick="return confirm('Are you sure you want to delete this question?');" class="text-red-400 hover:text-red-300 transition">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Bulk Actions Bar -->
            <div class="bg-gray-700 px-4 lg:px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-gray-600">
                <div class="flex items-center gap-3 flex-wrap">
                    <select name="bulk_action" class="bg-gray-600 text-white border border-gray-500 rounded-lg px-3 py-2 focus:outline-none focus:border-[#0038A8] text-sm">
                        <option value="">Bulk Actions</option>
                        <option value="delete">Delete Selected</option>
                        <option value="change_category">Change Category</option>
                        <option value="change_difficulty">Change Difficulty</option>
                    </select>
                    <select name="new_category_id" class="bg-gray-600 text-white border border-gray-500 rounded-lg px-3 py-2 focus:outline-none focus:border-[#0038A8] text-sm hidden" id="bulkCategorySelect">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="new_difficulty" class="bg-gray-600 text-white border border-gray-500 rounded-lg px-3 py-2 focus:outline-none focus:border-[#0038A8] text-sm hidden" id="bulkDifficultySelect">
                        <option value="">Select Difficulty</option>
                        <option value="easy">Easy</option>
                        <option value="medium">Medium</option>
                        <option value="hard">Hard</option>
                    </select>
                    <button type="submit" class="bg-[#0038A8] hover:bg-[#0047b3] text-white px-4 py-2 rounded-lg transition text-sm">
                        Apply
                    </button>
                </div>
            </div>
        </form>

        <!-- Pagination -->
        <div class="bg-gray-700 px-4 lg:px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-xs sm:text-sm text-gray-300 text-center sm:text-left">
                Showing <?php echo ($offset + 1); ?> to <?php echo min($offset + $per_page, $total); ?> of <?php echo $total; ?> questions
            </p>
            <div class="flex gap-2">
                <?php 
                $query_params = [];
                if ($search) $query_params[] = 'search=' . urlencode($search);
                if ($filter_category) $query_params[] = 'category=' . $filter_category;
                if ($filter_difficulty) $query_params[] = 'difficulty=' . urlencode($filter_difficulty);
                $query_string = implode('&', $query_params);
                ?>
                <?php if ($page > 1): ?>
                    <a href="?<?php echo $query_string ? $query_string . '&' : ''; ?>page=<?php echo $page - 1; ?>" class="px-3 lg:px-4 py-2 bg-gray-600 hover:bg-gray-500 rounded-lg text-white transition text-sm">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>
                <span class="px-3 lg:px-4 py-2 bg-[#0038A8] rounded-lg text-white text-sm"><?php echo $page; ?></span>
                <?php if ($page < $total_pages): ?>
                    <a href="?<?php echo $query_string ? $query_string . '&' : ''; ?>page=<?php echo $page + 1; ?>" class="px-3 lg:px-4 py-2 bg-gray-600 hover:bg-gray-500 rounded-lg text-white transition text-sm">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Select all checkbox
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.question-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
});

// Bulk action dropdown change
document.querySelector('select[name="bulk_action"]').addEventListener('change', function() {
    const categorySelect = document.getElementById('bulkCategorySelect');
    const difficultySelect = document.getElementById('bulkDifficultySelect');
    
    categorySelect.classList.add('hidden');
    difficultySelect.classList.add('hidden');
    
    if (this.value === 'change_category') {
        categorySelect.classList.remove('hidden');
    } else if (this.value === 'change_difficulty') {
        difficultySelect.classList.remove('hidden');
    }
});

// Form submission validation
document.getElementById('bulkActionForm').addEventListener('submit', function(e) {
    const action = this.querySelector('select[name="bulk_action"]').value;
    const selectedQuestions = this.querySelectorAll('.question-checkbox:checked');
    
    if (!action) {
        e.preventDefault();
        alert('Please select a bulk action');
        return;
    }
    
    if (selectedQuestions.length === 0) {
        e.preventDefault();
        alert('Please select at least one question');
        return;
    }
    
    if (action === 'delete') {
        if (!confirm(`Are you sure you want to delete ${selectedQuestions.length} question(s)?`)) {
            e.preventDefault();
        }
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
