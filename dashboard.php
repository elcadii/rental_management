<?php
require_once 'config/db.php';
requireLogin();

// جلب المستأجرين الخاصين بالمدير المسجل فقط
$stmt = $pdo->prepare("
    SELECT id, full_name, phone, email, cin, house_type, start_date, end_date, created_at 
    FROM tenants 
    WHERE admin_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$_SESSION['admin_id']]);
$tenants = $stmt->fetchAll();

// حساب الإحصائيات
$stats_stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_tenants,
        COUNT(CASE WHEN house_type = 'شقة' THEN 1 END) as apartments,
        COUNT(CASE WHEN house_type = 'فيلا' THEN 1 END) as villas,
        COUNT(CASE WHEN house_type = 'استوديو' THEN 1 END) as studios,
        COUNT(CASE WHEN end_date >= CURDATE() THEN 1 END) as active_tenants,
        COUNT(CASE WHEN end_date < CURDATE() THEN 1 END) as expired_tenants
    FROM tenants 
    WHERE admin_id = ?
");
$stats_stmt->execute([$_SESSION['admin_id']]);
$stats = $stats_stmt->fetch();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام إدارة الإيجارات</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Tajawal', Arial, sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .card-hover { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .card-hover:hover { transform: translateY(-5px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }
    </style>
</head>
<body class="bg-gray-50">
    <!-- شريط التنقل العلوي -->
    <nav class="bg-white shadow-lg border-b-2 border-blue-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-8 space-x-reverse">
                    <div class="flex items-center">
                        <i class="fas fa-home text-blue-600 text-2xl ml-3"></i>
                        <h1 class="text-xl font-bold text-gray-900">نظام الإيجارات</h1>
                    </div>
                    <div class="hidden md:flex items-center space-x-6 space-x-reverse">
                        <a href="dashboard.php" class="flex items-center text-blue-600 hover:text-blue-800 font-medium">
                            <i class="fas fa-home ml-2"></i>
                            الرئيسية
                        </a>
                        <a href="#tenants-list" class="flex items-center text-gray-700 hover:text-blue-600 font-medium">
                            <i class="fas fa-users ml-2"></i>
                            قائمة المستأجرين
                        </a>
                        <a href="add_tenant.php" class="flex items-center text-gray-700 hover:text-blue-600 font-medium">
                            <i class="fas fa-plus ml-2"></i>
                            إضافة مستأجر
                        </a>
                    </div>
                </div>
                <div class="flex items-center space-x-4 space-x-reverse">
                    <span class="text-gray-700 font-medium">مرحباً، <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                    <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200">
                        <i class="fas fa-sign-out-alt ml-1"></i>
                        تسجيل الخروج
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- القسم الرئيسي -->
    <div class="gradient-bg py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl font-bold text-white mb-4">نظام إدارة الإيجارات</h1>
            <p class="text-xl text-blue-100 max-w-3xl mx-auto">
                نظام شامل لإدارة حجوزات الإيجار للمنازل المتعددة مع إمكانية تتبع المستأجرين وإدارة العقود
            </p>
        </div>
    </div>

    <!-- الأقسام الرئيسية -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- قسم الإحصائيات -->
            <div class="bg-white rounded-xl shadow-lg p-6 card-hover">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-900">الإحصائيات</h3>
                    <i class="fas fa-chart-bar text-purple-500 text-2xl"></i>
                </div>
                <p class="text-gray-600 mb-6">تقارير وإحصائيات حول الإيجارات والمستأجرين</p>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
                        <span class="text-gray-700">إجمالي المستأجرين</span>
                        <span class="font-bold text-blue-600"><?php echo $stats['total_tenants']; ?></span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                        <span class="text-gray-700">العقود النشطة</span>
                        <span class="font-bold text-green-600"><?php echo $stats['active_tenants']; ?></span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg">
                        <span class="text-gray-700">العقود المنتهية</span>
                        <span class="font-bold text-red-600"><?php echo $stats['expired_tenants']; ?></span>
                    </div>
                </div>
                
                <button class="w-full mt-4 bg-purple-100 text-purple-700 py-2 rounded-lg hover:bg-purple-200 transition duration-200">
                    عرض التفاصيل
                </button>
            </div>

            <!-- قسم قائمة المستأجرين -->
            <div class="bg-white rounded-xl shadow-lg p-6 card-hover" id="tenants-list">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-900">قائمة المستأجرين</h3>
                    <i class="fas fa-users text-blue-500 text-2xl"></i>
                </div>
                <p class="text-gray-600 mb-6">عرض وإدارة جميع المستأجرين الحاليين والسابقين</p>
                
                <?php if (empty($tenants)): ?>
                    <div class="text-center py-8">
                        <i class="fas fa-users text-gray-300 text-4xl mb-4"></i>
                        <p class="text-gray-500">لا توجد مستأجرين مسجلين بعد</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-3 max-h-64 overflow-y-auto">
                        <?php foreach (array_slice($tenants, 0, 5) as $tenant): ?>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-900"><?php echo htmlspecialchars($tenant['full_name']); ?></p>
                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($tenant['house_type']); ?></p>
                                </div>
                                <div class="flex space-x-2 space-x-reverse">
                                    <a href="edit_tenant.php?id=<?php echo $tenant['id']; ?>" 
                                       class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete_tenant.php?id=<?php echo $tenant['id']; ?>" 
                                       class="text-red-600 hover:text-red-800"
                                       onclick="return confirm('هل أنت متأكد من حذف هذا المستأجر؟')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <a href="#full-list" class="w-full mt-4 bg-blue-100 text-blue-700 py-2 rounded-lg hover:bg-blue-200 transition duration-200 block text-center">
                    عرض القائمة الكاملة
                </a>
            </div>

            <!-- قسم إضافة مستأجر جديد -->
            <div class="bg-white rounded-xl shadow-lg p-6 card-hover">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-900">إضافة مستأجر جديد</h3>
                    <i class="fas fa-plus text-green-500 text-2xl"></i>
                </div>
                <p class="text-gray-600 mb-6">تسجيل مستأجر جديد مع جميع البيانات المطلوبة</p>
                
                <div class="text-center py-8">
                    <i class="fas fa-user-plus text-green-300 text-4xl mb-4"></i>
                    <p class="text-gray-600 mb-4">ابدأ بإضافة مستأجر جديد للنظام</p>
                </div>
                
                <a href="add_tenant.php" class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition duration-200 block text-center font-medium">
                    <i class="fas fa-plus ml-2"></i>
                    إضافة مستأجر
                </a>
            </div>
        </div>
    </div>

    <!-- المميزات الرئيسية -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <h2 class="text-3xl font-bold text-gray-900 text-center mb-12">المميزات الرئيسية</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-white rounded-xl shadow-lg p-8 card-hover">
                <div class="flex items-center mb-4">
                    <div class="bg-green-100 p-3 rounded-full ml-4">
                        <i class="fas fa-users text-green-600 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">تتبع المستأجرين</h3>
                </div>
                <p class="text-gray-600">حفظ وتتبع جميع بيانات المستأجرين بسهولة</p>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg p-8 card-hover">
                <div class="flex items-center mb-4">
                    <div class="bg-blue-100 p-3 rounded-full ml-4">
                        <i class="fas fa-building text-blue-600 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">إدارة متعددة المنازل</h3>
                </div>
                <p class="text-gray-600">إدارة عدة أنواع من المنازل والشقق في مكان واحد</p>
            </div>
        </div>
    </div>

    <!-- القائمة الكاملة للمستأجرين -->
    <?php if (!empty($tenants)): ?>
    <div id="full-list" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h3 class="text-lg font-bold text-gray-900">القائمة الكاملة للمستأجرين</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الاسم</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الهاتف</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">البريد الإلكتروني</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">رقم الهوية</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">نوع السكن</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">تاريخ البداية</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">تاريخ النهاية</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الحالة</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($tenants as $tenant): ?>
                            <?php 
                            $isActive = strtotime($tenant['end_date']) >= time();
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900"><?php echo htmlspecialchars($tenant['full_name']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($tenant['phone']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($tenant['email'] ?? 'غير محدد'); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($tenant['cin']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?php echo htmlspecialchars($tenant['house_type']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('Y/m/d', strtotime($tenant['start_date'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('Y/m/d', strtotime($tenant['end_date'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($isActive): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            نشط
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            منتهي
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2 space-x-reverse">
                                        <a href="edit_tenant.php?id=<?php echo $tenant['id']; ?>" 
                                           class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete_tenant.php?id=<?php echo $tenant['id']; ?>" 
                                           class="text-red-600 hover:text-red-900"
                                           onclick="return confirm('هل أنت متأكد من حذف هذا المستأجر؟')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
        // تأثيرات التمرير السلس
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
