<?php
require_once 'config/db.php';
requireLogin();

// Fetch all housing types for this admin
$stmt = $pdo->prepare('SELECT * FROM housing_types WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$_SESSION['admin_id']]);
$admin_housing_types = $stmt->fetchAll();

// Build a map of housing type names by ID for this admin
$housing_type_map = [];
foreach ($admin_housing_types as $type) {
    $housing_type_map[$type['id']] = $type['name'];
    $housing_type_map[$type['name']] = $type['name']; // for backward compatibility if house_type is a name
}

// Prepare filters for full tenant list
$filter_housing_type = isset($_GET['full_filter_housing_type']) ? $_GET['full_filter_housing_type'] : '';
$search_query = isset($_GET['full_search']) ? trim($_GET['full_search']) : '';
$full_tenants_query = "SELECT id, full_name, phone, email, cin, house_type, marital_status , total_rent , start_date, end_date, created_at, price_per_day FROM tenants WHERE admin_id = ?";
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
<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>القائمة الكاملة للمستأجرين</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Tajawal', Arial, sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-2 sm:px-4 md:px-6 lg:px-8 py-8">
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
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الحالة الاجتماعية</th>
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
                                    <?php echo htmlspecialchars($tenant['marital_status']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-money-bill-wave ml-1"></i><?php echo htmlspecialchars($tenant['total_rent']); ?>
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
</body>
</html> 