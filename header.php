<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<nav class="bg-white shadow-lg border-b-2 border-blue-500 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <div class="flex items-center">
                <i class="fas fa-home text-blue-600 text-2xl ml-3"></i>
                <a href="index.php" class="text-xl font-bold text-gray-900">نظام الإيجارات</a>
            </div>
            <div class="hidden md:flex items-center space-x-6 space-x-reverse">
                <a href="index.php" class="flex items-center text-blue-600 hover:text-blue-800 font-medium">
                    <i class="fas fa-home ml-2"></i>
                    الرئيسية
                </a>
                <a href="add_tenant.php" class="flex items-center text-gray-700 hover:text-blue-600 font-medium">
                    <i class="fas fa-plus ml-2"></i>
                    إضافة مستأجر
                </a>
                <a href="housing_types.php" class="flex items-center text-gray-700 hover:text-blue-600 font-medium">
                    <i class="fas fa-building ml-2"></i>
                    إدارة أنواع السكن
                </a>
                <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200 flex items-center">
                    <i class="fas fa-sign-out-alt ml-1"></i>
                    تسجيل الخروج
                </a>
            </div>
            <!-- Hamburger -->
            <div class="md:hidden flex items-center">
                <button id="mobile-menu-button" class="text-gray-700 focus:outline-none focus:text-blue-600">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
        </div>
    </div>
    <!-- Mobile Menu -->
    <div id="mobile-menu" class="md:hidden hidden bg-white border-t border-blue-100">
        <div class="px-4 py-2 flex flex-col space-y-2">
            <a href="index.php" class="flex items-center text-blue-600 hover:text-blue-800 font-medium">
                <i class="fas fa-home ml-2"></i>
                الرئيسية
            </a>
            <a href="add_tenant.php" class="flex items-center text-gray-700 hover:text-blue-600 font-medium">
                <i class="fas fa-plus ml-2"></i>
                إضافة مستأجر
            </a>
            <a href="housing_types.php" class="flex items-center text-gray-700 hover:text-blue-600 font-medium">
                <i class="fas fa-building ml-2"></i>
                إدارة أنواع السكن
            </a>
            <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200 flex items-center">
                <i class="fas fa-sign-out-alt ml-1"></i>
                تسجيل الخروج
            </a>
        </div>
    </div>
    <script>
        const btn = document.getElementById('mobile-menu-button');
        const menu = document.getElementById('mobile-menu');
        btn.addEventListener('click', () => {
            menu.classList.toggle('hidden');
        });
        // Optional: Hide menu on navigation
        menu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => menu.classList.add('hidden'));
        });
    </script>
</nav> 