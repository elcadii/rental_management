<?php
require_once 'config/db.php';
// System summary
$total_admins = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
$total_tenants = $pdo->query("SELECT COUNT(*) FROM tenants")->fetchColumn();
$total_housing_types = $pdo->query("SELECT COUNT(*) FROM housing_types")->fetchColumn();
$total_contracts = $pdo->query("SELECT COUNT(*) FROM tenants")->fetchColumn();
$total_rent = $pdo->query("SELECT SUM(total_rent) FROM tenants")->fetchColumn() ?: 0;
// Recent tenants
$recent_tenants = $pdo->query("SELECT t.*, a.name AS admin_name FROM tenants t JOIN admins a ON t.admin_id = a.id ORDER BY t.created_at DESC LIMIT 10")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مراجعة البيانات</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { font-family: 'Tajawal', Arial, sans-serif; }</style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-purple-50 min-h-screen ">
    <div class="max-w-6xl mx-auto my-10 px-4">
        <h2 class="text-2xl font-bold text-gray-900 mb-8 flex items-center gap-2"><i class="fa-solid fa-database text-purple-500"></i>مراجعة البيانات</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mb-10">
            <div class="bg-white rounded-2xl shadow-lg p-6 flex flex-col items-center border-t-4 border-blue-500">
                <div class="bg-blue-100 text-blue-600 rounded-full p-3 mb-2"><i class="fa-solid fa-users text-2xl"></i></div>
                <div class="text-2xl font-bold"><?php echo $total_admins; ?></div>
                <div class="text-gray-700 font-semibold mt-1">المدراء</div>
            </div>
            <div class="bg-white rounded-2xl shadow-lg p-6 flex flex-col items-center border-t-4 border-purple-500">
                <div class="bg-purple-100 text-purple-600 rounded-full p-3 mb-2"><i class="fa-solid fa-user-friends text-2xl"></i></div>
                <div class="text-2xl font-bold"><?php echo $total_tenants; ?></div>
                <div class="text-gray-700 font-semibold mt-1">المستأجرون</div>
            </div>
            <div class="bg-white rounded-2xl shadow-lg p-6 flex flex-col items-center border-t-4 border-green-500">
                <div class="bg-green-100 text-green-600 rounded-full p-3 mb-2"><i class="fa-solid fa-building text-2xl"></i></div>
                <div class="text-2xl font-bold"><?php echo $total_housing_types; ?></div>
                <div class="text-gray-700 font-semibold mt-1">أنواع السكن</div>
            </div>
            <div class="bg-white rounded-2xl shadow-lg p-6 flex flex-col items-center border-t-4 border-yellow-500">
                <div class="bg-yellow-100 text-yellow-600 rounded-full p-3 mb-2"><i class="fa-solid fa-file-contract text-2xl"></i></div>
                <div class="text-2xl font-bold"><?php echo $total_contracts; ?></div>
                <div class="text-gray-700 font-semibold mt-1">العقود</div>
            </div>
            <div class="bg-white rounded-2xl shadow-lg p-6 flex flex-col items-center border-t-4 border-indigo-500">
                <div class="bg-indigo-100 text-indigo-600 rounded-full p-3 mb-2"><i class="fa-solid fa-coins text-2xl"></i></div>
                <div class="text-2xl font-bold"><?php echo number_format($total_rent, 2); ?> <span class="text-xs">د.م</span></div>
                <div class="text-gray-700 font-semibold mt-1">إجمالي الإيرادات</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-lg p-6 overflow-x-auto">
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2"><i class="fa-solid fa-users text-blue-500"></i>أحدث المستأجرين</h3>
            <table class="min-w-full divide-y divide-gray-200 text-center">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-xs font-bold text-gray-700 uppercase tracking-wider">الاسم</th>
                        <th class="px-6 py-3 text-xs font-bold text-gray-700 uppercase tracking-wider">رقم الهاتف</th>
                        <th class="px-6 py-3 text-xs font-bold text-gray-700 uppercase tracking-wider">البريد الإلكتروني</th>
                        <th class="px-6 py-3 text-xs font-bold text-gray-700 uppercase tracking-wider">المدير</th>
                        <th class="px-6 py-3 text-xs font-bold text-gray-700 uppercase tracking-wider">تاريخ البداية</th>
                        <th class="px-6 py-3 text-xs font-bold text-gray-700 uppercase tracking-wider">تاريخ النهاية</th>
                        <th class="px-6 py-3 text-xs font-bold text-gray-700 uppercase tracking-wider">إجمالي الإيجار</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($recent_tenants as $tenant): ?>
                        <tr class="hover:bg-blue-50">
                            <td class="px-6 py-4 whitespace-nowrap font-semibold text-blue-700"><?php echo htmlspecialchars($tenant['full_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($tenant['phone']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($tenant['email'] ?? 'غير محدد'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($tenant['admin_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo date('Y/m/d', strtotime($tenant['start_date'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo date('Y/m/d', strtotime($tenant['end_date'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-green-700 font-bold"><?php echo number_format($tenant['total_rent'] ?: 0, 2); ?> <span class="text-xs">د.م</span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html> 