<?php
require_once 'config/db.php';
// Optionally, require super admin login here

// System-wide stats
$total_admins = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
$total_tenants = $pdo->query("SELECT COUNT(*) FROM tenants")->fetchColumn();
$total_housing_types = $pdo->query("SELECT COUNT(*) FROM housing_types")->fetchColumn();
$total_active_contracts = $pdo->query("SELECT COUNT(*) FROM tenants WHERE end_date >= CURDATE() ")->fetchColumn();
$total_expired_contracts = $pdo->query("SELECT COUNT(*) FROM tenants WHERE end_date < CURDATE() ")->fetchColumn();
$total_rent = $pdo->query("SELECT SUM(total_rent) FROM tenants")->fetchColumn() ?: 0;

// Admin-specific stats
$admin_stats = $pdo->query("
    SELECT a.id, a.name,
        (SELECT COUNT(*) FROM tenants t WHERE t.admin_id = a.id) AS tenants_count,
        (SELECT COUNT(*) FROM housing_types h WHERE h.user_id = a.id) AS housing_types_count,
        (SELECT SUM(total_rent) FROM tenants t WHERE t.admin_id = a.id) AS total_rent
    FROM admins a
    ORDER BY a.name
")->fetchAll();

// Website stats (placeholders for demo)
$daily_active_users = rand(10, 50);
$new_tenants_this_month = $pdo->query("SELECT COUNT(*) FROM tenants WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())")->fetchColumn();
$system_uptime = 99.98; // Placeholder
$avg_revenue_per_admin = $total_admins > 0 ? $total_rent / $total_admins : 0;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم السوبر أدمن</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Tajawal', Arial, sans-serif;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-blue-50 to-purple-50 min-h-screen ">

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Hero Section: System-wide Stats -->
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-8 text-center">لوحة تحكم السوبر أدمن</h1>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
            <div class="bg-white rounded-2xl shadow-lg p-6 flex items-center gap-4 border-t-4 border-blue-500">
                <div class="bg-blue-100 text-blue-600 rounded-full p-3"><i class="fa-solid fa-users text-2xl"></i></div>
                <div>
                    <div class="text-2xl font-bold"><?php echo $total_admins; ?></div>
                    <div class="text-gray-700 font-semibold">إجمالي المدراء</div>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-lg p-6 flex items-center gap-4 border-t-4 border-purple-500">
                <div class="bg-purple-100 text-purple-600 rounded-full p-3"><i class="fa-solid fa-user-friends text-2xl"></i></div>
                <div>
                    <div class="text-2xl font-bold"><?php echo $total_tenants; ?></div>
                    <div class="text-gray-700 font-semibold">إجمالي المستأجرين</div>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-lg p-6 flex items-center gap-4 border-t-4 border-green-500">
                <div class="bg-green-100 text-green-600 rounded-full p-3"><i class="fa-solid fa-building text-2xl"></i></div>
                <div>
                    <div class="text-2xl font-bold"><?php echo $total_housing_types; ?></div>
                    <div class="text-gray-700 font-semibold">أنواع السكن</div>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-lg p-6 flex items-center gap-4 border-t-4 border-yellow-500">
                <div class="bg-yellow-100 text-yellow-600 rounded-full p-3"><i class="fa-solid fa-file-contract text-2xl"></i></div>
                <div>
                    <div class="text-2xl font-bold"><?php echo $total_active_contracts; ?></div>
                    <div class="text-gray-700 font-semibold">العقود النشطة</div>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-lg p-6 flex items-center gap-4 border-t-4 border-red-500">
                <div class="bg-red-100 text-red-600 rounded-full p-3"><i class="fa-solid fa-file-circle-xmark text-2xl"></i></div>
                <div>
                    <div class="text-2xl font-bold"><?php echo $total_expired_contracts; ?></div>
                    <div class="text-gray-700 font-semibold">العقود المنتهية</div>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-lg p-6 flex items-center gap-4 border-t-4 border-indigo-500">
                <div class="bg-indigo-100 text-indigo-600 rounded-full p-3"><i class="fa-solid fa-coins text-2xl"></i></div>
                <div>
                    <div class="text-2xl font-bold"><?php echo number_format($total_rent, 2); ?> <span class="text-base font-normal">د.م</span></div>
                    <div class="text-gray-700 font-semibold">إجمالي الإيرادات</div>
                </div>
            </div>
        </div>
        <!-- Quick Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-6 mb-10 justify-center">
            <a href="manage_admins.php" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl shadow-lg py-6 flex flex-col items-center justify-center text-xl font-semibold transition">
                <i class="fa-solid fa-users-cog text-3xl mb-2"></i>
                إدارة المدراء
            </a>
            <a href="review_data.php" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white rounded-2xl shadow-lg py-6 flex flex-col items-center justify-center text-xl font-semibold transition">
                <i class="fa-solid fa-database text-3xl mb-2"></i>
                مراجعة البيانات
            </a>
            <a href="add_admin.php" class="flex-1 bg-green-600 hover:bg-green-700 text-white rounded-2xl shadow-lg py-6 flex flex-col items-center justify-center text-xl font-semibold transition">
                <i class="fa-solid fa-user-plus text-3xl mb-2"></i>
                إضافة مدير جديد
            </a>
        </div>
        <!-- Admin-specific Stats Table -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-10 overflow-x-auto">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2"><i class="fa-solid fa-user-shield text-blue-500"></i>إحصائيات المدراء</h2>
            <table class="min-w-full divide-y divide-gray-200 text-center">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-xs font-bold text-gray-700 uppercase tracking-wider">اسم المدير</th>
                        <th class="px-6 py-3 text-xs font-bold text-gray-700 uppercase tracking-wider">عدد المستأجرين</th>
                        <th class="px-6 py-3 text-xs font-bold text-gray-700 uppercase tracking-wider">أنواع السكن</th>
                        <th class="px-6 py-3 text-xs font-bold text-gray-700 uppercase tracking-wider">إجمالي الإيرادات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($admin_stats as $admin): ?>
                        <tr class="hover:bg-blue-50">
                            <td class="px-6 py-4 whitespace-nowrap font-semibold text-blue-700"><?php echo htmlspecialchars($admin['name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $admin['tenants_count']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $admin['housing_types_count']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-green-700 font-bold"><?php echo number_format($admin['total_rent'] ?: 0, 2); ?> <span class="text-xs">د.م</span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <!-- Website Stats Section -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div class="bg-white rounded-xl shadow-md p-6 flex flex-col items-center hover:shadow-xl transition border-t-4 border-blue-400">
                <div class="bg-blue-100 text-blue-600 rounded-full p-3 mb-2"><i class="fa-solid fa-bolt text-2xl"></i></div>
                <div class="text-2xl font-bold"><?php echo $daily_active_users; ?></div>
                <div class="text-gray-700 font-semibold mt-1">المستخدمون النشطون اليوم</div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-6 flex flex-col items-center hover:shadow-xl transition border-t-4 border-purple-400">
                <div class="bg-purple-100 text-purple-600 rounded-full p-3 mb-2"><i class="fa-solid fa-user-plus text-2xl"></i></div>
                <div class="text-2xl font-bold"><?php echo $new_tenants_this_month; ?></div>
                <div class="text-gray-700 font-semibold mt-1">مستأجرون جدد هذا الشهر</div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-6 flex flex-col items-center hover:shadow-xl transition border-t-4 border-green-400">
                <div class="bg-green-100 text-green-600 rounded-full p-3 mb-2"><i class="fa-solid fa-server text-2xl"></i></div>
                <div class="text-2xl font-bold"><?php echo $system_uptime; ?>%</div>
                <div class="text-gray-700 font-semibold mt-1">مدة تشغيل النظام</div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-6 flex flex-col items-center hover:shadow-xl transition border-t-4 border-yellow-400">
                <div class="bg-yellow-100 text-yellow-600 rounded-full p-3 mb-2"><i class="fa-solid fa-chart-line text-2xl"></i></div>
                <div class="text-2xl font-bold"><?php echo number_format($avg_revenue_per_admin, 2); ?> <span class="text-xs">د.م</span></div>
                <div class="text-gray-700 font-semibold mt-1">متوسط الإيراد لكل مدير</div>
            </div>
        </div>
    </div>
</body>

</html>