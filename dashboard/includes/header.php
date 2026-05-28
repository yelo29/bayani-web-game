<?php
error_reporting(0);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/db.php';

// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Dashboard'; ?> - Bayani World Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-900 text-gray-100">
    <div class="flex min-h-screen">
        <!-- Mobile Menu Button -->
        <button id="mobileMenuBtn" class="lg:hidden fixed top-4 left-4 z-50 bg-[#0038A8] text-white p-3 rounded-lg shadow-lg">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Sidebar -->
        <aside id="sidebar" class="fixed lg:static inset-0 z-40 w-64 bg-gray-800 border-r border-gray-700 flex flex-col transform -translate-x-full lg:translate-x-0 transition-transform duration-300">
            <!-- Logo -->
            <div class="p-4 lg:p-6 border-b border-gray-700 flex items-center justify-between">
                <h1 class="text-lg lg:text-2xl font-bold text-[#0038A8] truncate">
                    <i class="fas fa-shield-halved mr-2"></i>Bayani World
                </h1>
                <button id="closeSidebarBtn" class="lg:hidden text-gray-400 hover:text-white ml-2 flex-shrink-0">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <p class="text-gray-400 text-xs lg:text-sm px-4 lg:px-6 pb-4 truncate">Admin Dashboard</p>

            <!-- Navigation -->
            <nav class="flex-1 p-4 overflow-y-auto">
                <ul class="space-y-2">
                    <li>
                        <a href="/dashboard/index.php"
                           class="flex items-center px-3 lg:px-4 py-3 rounded-lg transition <?php echo $current_page === 'index.php' ? 'bg-[#0038A8] text-white' : 'text-gray-300 hover:bg-gray-700'; ?>">
                            <i class="fas fa-tachometer-alt mr-2 lg:mr-3 text-sm lg:text-base"></i><span class="text-sm lg:text-base">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="/dashboard/users.php"
                           class="flex items-center px-3 lg:px-4 py-3 rounded-lg transition <?php echo $current_page === 'users.php' ? 'bg-[#0038A8] text-white' : 'text-gray-300 hover:bg-gray-700'; ?>">
                            <i class="fas fa-users mr-2 lg:mr-3 text-sm lg:text-base"></i><span class="text-sm lg:text-base">Users</span>
                        </a>
                    </li>
                    <li>
                        <a href="/dashboard/questions.php"
                           class="flex items-center px-3 lg:px-4 py-3 rounded-lg transition <?php echo $current_page === 'questions.php' ? 'bg-[#0038A8] text-white' : 'text-gray-300 hover:bg-gray-700'; ?>">
                            <i class="fas fa-question-circle mr-2 lg:mr-3 text-sm lg:text-base"></i><span class="text-sm lg:text-base">Questions</span>
                        </a>
                    </li>
                    <li>
                        <a href="/dashboard/analytics.php"
                           class="flex items-center px-3 lg:px-4 py-3 rounded-lg transition <?php echo $current_page === 'analytics.php' ? 'bg-[#0038A8] text-white' : 'text-gray-300 hover:bg-gray-700'; ?>">
                            <i class="fas fa-chart-line mr-2 lg:mr-3 text-sm lg:text-base"></i><span class="text-sm lg:text-base">Analytics</span>
                        </a>
                    </li>
                    <li>
                        <a href="/dashboard/items.php"
                           class="flex items-center px-3 lg:px-4 py-3 rounded-lg transition <?php echo $current_page === 'items.php' ? 'bg-[#0038A8] text-white' : 'text-gray-300 hover:bg-gray-700'; ?>">
                            <i class="fas fa-box mr-2 lg:mr-3 text-sm lg:text-base"></i><span class="text-sm lg:text-base">Items</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Admin Info -->
            <div class="p-4 border-t border-gray-700">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-[#0038A8] rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-user-shield text-white"></i>
                    </div>
                    <div class="ml-3 min-w-0">
                        <p class="text-sm font-bold text-white truncate"><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></p>
                        <p class="text-xs text-gray-400 truncate">Administrator</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Sidebar Overlay -->
        <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-30 hidden lg:hidden"></div>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0">
            <!-- Top Bar -->
            <header class="bg-gray-800 border-b border-gray-700 px-4 lg:px-6 py-3 lg:py-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-base lg:text-lg lg:text-xl font-bold text-white truncate pr-4"><?php echo $page_title ?? 'Dashboard'; ?></h2>
                    <a href="/dashboard/logout.php" class="text-gray-300 hover:text-red-400 transition text-xs lg:text-sm lg:text-base flex-shrink-0">
                        <i class="fas fa-sign-out-alt mr-1 lg:mr-2"></i><span class="hidden sm:inline">Logout</span>
                    </a>
                </div>
            </header>

            <!-- Content Area -->
            <div class="flex-1 p-4 lg:p-6 overflow-auto">

<script>
// Mobile menu toggle
const mobileMenuBtn = document.getElementById('mobileMenuBtn');
const sidebar = document.getElementById('sidebar');
const closeSidebarBtn = document.getElementById('closeSidebarBtn');
const sidebarOverlay = document.getElementById('sidebarOverlay');

function openSidebar() {
    sidebar.classList.remove('-translate-x-full');
    sidebarOverlay.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeSidebar() {
    sidebar.classList.add('-translate-x-full');
    sidebarOverlay.classList.add('hidden');
    document.body.style.overflow = 'auto';
}

if (mobileMenuBtn) {
    mobileMenuBtn.addEventListener('click', openSidebar);
}

if (closeSidebarBtn) {
    closeSidebarBtn.addEventListener('click', closeSidebar);
}

if (sidebarOverlay) {
    sidebarOverlay.addEventListener('click', closeSidebar);
}

// Close sidebar when clicking a navigation link on mobile
const sidebarLinks = sidebar.querySelectorAll('a');
sidebarLinks.forEach(link => {
    link.addEventListener('click', () => {
        if (window.innerWidth < 1024) {
            closeSidebar();
        }
    });
});
</script>
