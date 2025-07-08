<?php
require_once 'config/db.php';
requireLogin();

// Check if session admin_id exists in admins table
$stmt = $pdo->prepare('SELECT COUNT(*) FROM admins WHERE id = ?');
$stmt->execute([$_SESSION['admin_id']]);
if ($stmt->fetchColumn() == 0) {
    // Destroy session and redirect to login
    session_destroy();
    header('Location: login.php');
    exit();
}

// Handle add housing type form
$housing_type_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_housing_type'])) {
    $type_name = sanitize($_POST['housing_type_name'] ?? '');
    if (empty($type_name)) {
        $housing_type_error = 'اسم نوع السكن مطلوب';
    } else {
        // Check for duplicate for this admin
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM housing_types WHERE name = ? AND user_id = ?');
        $stmt->execute([$type_name, $_SESSION['admin_id']]);
        if ($stmt->fetchColumn() > 0) {
            $housing_type_error = 'هذا النوع مضاف بالفعل.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO housing_types (name, user_id) VALUES (?, ?)');
            $stmt->execute([$type_name, $_SESSION['admin_id']]);
        }
    }
}
// Fetch all housing types for this admin
$stmt = $pdo->prepare('SELECT * FROM housing_types WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$_SESSION['admin_id']]);
$admin_housing_types = $stmt->fetchAll();

// Fetch tenants belonging only to the logged-in admin
$stmt = $pdo->prepare("
    SELECT id, full_name, phone, email, cin, house_type, start_date, end_date, created_at, price_per_day 
    FROM tenants 
    WHERE admin_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$_SESSION['admin_id']]);
$tenants = $stmt->fetchAll();

// Calculate statistics
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

// Build a map of housing type names by ID for this admin
$housing_type_map = [];
foreach ($admin_housing_types as $type) {
    $housing_type_map[$type['id']] = $type['name'];
    $housing_type_map[$type['name']] = $type['name']; // for backward compatibility if house_type is a name
}

// Prepare filters for full tenant list
$filter_housing_type = isset($_GET['full_filter_housing_type']) ? $_GET['full_filter_housing_type'] : '';
$search_query = isset($_GET['full_search']) ? trim($_GET['full_search']) : '';
$full_tenants_query = "SELECT id, full_name, phone, email, cin, house_type, start_date, end_date, created_at, price_per_day FROM tenants WHERE admin_id = ?";
$full_params = [$_SESSION['admin_id']];
if ($filter_housing_type !== '') {
    $full_tenants_query .= " AND house_type = ?";
    $full_params[] = $filter_housing_type;
}
if ($search_query !== '') {
    $full_tenants_query .= " AND (full_name LIKE ? OR phone LIKE ? OR email LIKE ? OR cin LIKE ?)";
    $search_term = "%$search_query%";
    $full_params = array_merge($full_params, [$search_term, $search_term, $search_term, $search_term]);
}
$full_tenants_query .= " ORDER BY created_at DESC";
$full_stmt = $pdo->prepare($full_tenants_query);
$full_stmt->execute($full_params);
$full_tenants = $full_stmt->fetchAll();
?>

<?php include 'header.php'; ?>
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
                                    <p class="text-sm text-gray-500"><?php 
                                        $ht = $tenant['house_type'];
                                        echo htmlspecialchars(isset($housing_type_map[$ht]) ? $housing_type_map[$ht] : $ht);
                                    ?></p>
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
    <div id="full-list" class="max-w-7xl mx-auto px-2 sm:px-4 md:px-6 lg:px-8 pb-16">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-2 sm:px-6 py-4 bg-gray-50 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <h3 class="text-lg font-bold text-gray-900">القائمة الكاملة للمستأجرين</h3>
            </div>
            <!-- Stylish filter/search bar -->
            <div class="px-2 sm:px-6 py-4 bg-blue-50 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <form method="get" class="flex flex-col md:flex-row gap-2 md:gap-4 items-center w-full">
                    <div class="flex flex-col md:flex-row gap-2 md:gap-4 w-full md:w-auto">
                        <select name="full_filter_housing_type" class="px-4 py-2 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-blue-500 bg-white text-gray-700 w-full md:w-auto">
                            <option value="">كل أنواع السكن</option>
                            <?php foreach ($admin_housing_types as $type): ?>
                                <option value="<?php echo htmlspecialchars($type['name']); ?>" <?php echo $filter_housing_type === $type['name'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($type['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" name="full_search" placeholder="بحث بالاسم أو الهاتف أو البريد أو الهوية" value="<?php echo htmlspecialchars($search_query); ?>" class="px-4 py-2 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-blue-500 bg-white text-gray-700 w-full md:w-64" />
                    </div>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold transition flex items-center gap-2">
                        <i class="fas fa-search"></i> بحث
                    </button>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الاسم</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الهاتف</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">البريد الإلكتروني</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">رقم الهوية</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">نوع السكن</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">إجمالي الإيجار</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">تاريخ البداية</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">تاريخ النهاية</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الحالة</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($full_tenants as $tenant): ?>
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
                                        <?php 
                                            $ht = $tenant['house_type'];
                                            echo htmlspecialchars(isset($housing_type_map[$ht]) ? $housing_type_map[$ht] : $ht);
                                        ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php 
                                        $start = new DateTime($tenant['start_date']);
                                        $end = new DateTime($tenant['end_date']);
                                        $days = $start->diff($end)->days + 1;
                                        $days = $days > 0 ? $days : 0;
                                        $total_rent = $tenant['price_per_day'] * $days;
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-money-bill-wave ml-1"></i> <?php echo number_format($total_rent, 2); ?> 
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
