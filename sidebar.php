<?php
$current = basename($_SERVER['PHP_SELF']);
$nav = [
    [
        'file' => 'dashboard.php',
        'label' => 'الرئيسية',
        'icon' => 'fa-home',
    ],
    [
        'file' => 'add_tenant.php',
        'label' => 'إضافة مستأجر',
        'icon' => 'fa-user-plus',
    ],
    [
        'file' => 'full_tenants_list.php',
        'label' => 'قائمة المستأجرين',
        'icon' => 'fa-users',
    ],
    [
        'file' => 'housing_types.php',
        'label' => 'أنواع السكن',
        'icon' => 'fa-building',
    ],
     [
        'file' => 'profile.php' ,
        'label' => 'حسابي',
        'icon' => 'fa-user',
    ],
];
?>
<aside class="fixed top-0 right-0 h-full w-64 bg-white border-l border-blue-100 shadow-lg flex flex-col z-40">
    <div class="flex items-center justify-center h-16 border-b border-blue-100">
        <a href="index.php" class="text-2xl font-extrabold text-blue-700 flex items-center gap-2">
            <i class="fas fa-home"></i> نظام الإيجارات
        </a>
    </div>
    <nav class="flex-1 overflow-y-auto py-4">
        <ul class="space-y-2 px-4">
            <?php foreach ($nav as $item): ?>
                <li>
                    <a href="<?php echo $item['file']; ?>"
                       class="flex items-center gap-3 px-4 py-3 rounded-lg transition font-medium text-base <?php echo $current === $item['file'] ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700'; ?>">
                        <i class="fas <?php echo $item['icon']; ?> text-lg"></i>
                        <span><?php echo $item['label']; ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
    <div class="mt-auto px-4 pb-6">
        <a href="logout.php"
           class="w-full flex items-center gap-3 px-4 py-3 rounded-lg bg-red-500 text-white font-medium text-base hover:bg-red-600 transition justify-center">
            <i class="fas fa-sign-out-alt"></i>
            <span>تسجيل الخروج</span>
        </a>
    </div>
</aside>
<!-- Responsive toggle button for mobile -->
<div class="md:hidden fixed top-4 right-4 z-50">
    <button id="sidebar-toggle" class="bg-blue-600 text-white p-2 rounded-lg shadow-lg focus:outline-none">
        <i class="fas fa-bars"></i>
    </button>
</div>
<script>
    // Sidebar toggle for mobile
    const sidebar = document.querySelector('aside');
    const toggleBtn = document.getElementById('sidebar-toggle');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('hidden');
        });
    }
    // Hide sidebar by default on mobile
    function handleResize() {
        if (window.innerWidth < 768) {
            sidebar.classList.add('hidden');
        } else {
            sidebar.classList.remove('hidden');
        }
    }
    window.addEventListener('resize', handleResize);
    window.addEventListener('DOMContentLoaded', handleResize);
</script>
<style>
    body { padding-right: 16rem; padding-left: 0; }
    @media (max-width: 767px) {
        body { padding-right: 0; }
    }
</style> 