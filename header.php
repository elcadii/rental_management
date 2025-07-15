<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : '';
?>
<header class="bg-white shadow-lg border-b-2 border-blue-500 sticky top-0 z-50 w-full">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-end items-center h-16">
        <!-- <div class="flex items-center gap-3">
            <i class="fas fa-home text-blue-600 text-2xl"></i>
            <span class="text-xl font-bold text-gray-900">نظام الإيجارات</span>
        </div> -->
        <div class="flex items-center gap-4">
            <span class="text-gray-700 font-medium hidden sm:inline"><i class="fas fa-user-circle text-blue-500 ml-1"></i><?php echo htmlspecialchars($admin_name); ?></span>
            <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200 flex items-center">
                <i class="fas fa-sign-out-alt ml-1"></i>
                تسجيل الخروج
            </a>
        </div>
    </div>
</header> 