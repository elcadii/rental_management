<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : '';
require_once __DIR__ . '/includes/i18n.php';
?>
<header class="bg-white shadow-lg border-b-2 border-blue-500 sticky top-0 z-50 w-full">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-end items-center h-16">
        <!-- <div class="flex items-center gap-3">
            <i class="fas fa-home text-blue-600 text-2xl"></i>
            <span class="text-xl font-bold text-gray-900">نظام الإيجارات</span>
        </div> -->
        <div class="flex items-center gap-4">
            
            <a href="profile.php" style="text-decoration:none">
                <span class="text-gray-700 font-medium hidden sm:inline"><i class="fas fa-user-circle text-blue-500 ml-1"></i><?php echo htmlspecialchars($admin_name); ?></span>
            </a>
            <form method="get" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="flex items-center mb-0">
                <select name="lang" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <?php foreach (supportedLanguages() as $code => $meta): ?>
                        <option value="<?php echo $code; ?>" <?php echo currentLang() === $code ? 'selected' : ''; ?>><?php echo $meta['flag'] . ' ' . $meta['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
            <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200 flex items-center">
                <i class="fas fa-sign-out-alt ml-1"></i>
                <?php echo __('header.logout'); ?>
            </a>
        </div>
    </div>
</header> 