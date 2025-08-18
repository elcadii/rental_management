<?php
require_once 'config/db.php';
require_once 'includes/currency_manager.php';
requireLogin();

// Fetch all housing types for this admin (for mapping)
$stmt = $pdo->prepare('SELECT * FROM housing_types WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$_SESSION['admin_id']]);
$admin_housing_types = $stmt->fetchAll();
$housing_type_map = [];
foreach ($admin_housing_types as $type) {
    $housing_type_map[$type['id']] = $type['name'];
    $housing_type_map[$type['name']] = $type['name']; // for backward compatibility
}

// Prepare filters (same as full_tenants_list.php)
$filter_housing_type = isset($_GET['full_filter_housing_type']) ? $_GET['full_filter_housing_type'] : '';
$search_query = isset($_GET['full_search']) ? trim($_GET['full_search']) : '';
$full_tenants_query = "SELECT id, full_name, phone, email, cin, address, house_type, marital_status, total_rent, start_date, end_date, created_at, price_per_day, marriage_contract FROM tenants WHERE admin_id = ?";
$full_params = [$_SESSION['admin_id']];
if ($filter_housing_type !== '') {
    $full_tenants_query .= " AND house_type = ?";
    $full_params[] = $filter_housing_type;
}
if ($search_query !== '') {
    $search_term = "%$search_query%";
    $full_params = array_merge($full_params, [$search_term, $search_term, $search_term, $search_term, $search_term]);
}
$full_tenants_query .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($full_tenants_query);
$stmt->execute($full_params);
$full_tenants = $stmt->fetchAll();

// Build base URL for file links
$baseUrl = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http')
	. '://' . $_SERVER['HTTP_HOST']
	. rtrim(dirname($_SERVER['REQUEST_URI']), '/\\') . '/';

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename=tenants_export_' . date('Ymd_His') . '.xls');
header('Pragma: no-cache');
header('Expires: 0');

// Output UTF-8 BOM for Excel compatibility with Arabic
echo "\xEF\xBB\xBF";

echo "<table border='1'>";
echo "<tr>"
    . "<th>الاسم</th>"
    . "<th>الهاتف</th>"
    . "<th>البريد الإلكتروني</th>"
    . "<th>رقم الهوية</th>"
    . "<th>العنوان</th>"
    . "<th>نوع السكن</th>"
    . "<th>الحالة الاجتماعية</th>"
    . "<th>عقد الزواج</th>"
    . "<th>سعر اليوم</th>"
    . "<th>إجمالي الإيجار</th>"
    . "<th>تاريخ البداية</th>"
    . "<th>تاريخ النهاية</th>"
    . "</tr>";
foreach ($full_tenants as $tenant) {
    $isActive = strtotime($tenant['end_date']) >= time();
    
    // Convert price per day
    $price_per_day = convertCurrency($tenant['price_per_day'] ?? 0);
    
    // Prepare contract cell
    $contractCell = '-';
    if ($tenant['marital_status'] === 'Married' && !empty($tenant['marriage_contract'])) {
        $contractUrl = $baseUrl . ltrim($tenant['marriage_contract'], '/');
        $contractCell = "<a href=\"" . htmlspecialchars($contractUrl) . "\" target=\"_blank\">عرض العقد</a>";
    } else if ($tenant['marital_status'] === 'Married') {
        $contractCell = 'غير متوفر';
    }
    
    echo "<tr>"
        . "<td>" . htmlspecialchars($tenant['full_name']) . "</td>"
        . "<td>" . htmlspecialchars($tenant['phone']) . "</td>"
        . "<td>" . htmlspecialchars($tenant['email'] ?: 'غير محدد') . "</td>"
        . "<td>" . htmlspecialchars($tenant['cin']) . "</td>"
        . "<td>" . htmlspecialchars($tenant['address'] ?: 'غير محدد') . "</td>"
        . "<td>" . htmlspecialchars(isset($housing_type_map[$tenant['house_type']]) ? $housing_type_map[$tenant['house_type']] : $tenant['house_type']) . "</td>"
        . "<td>" . htmlspecialchars($tenant['marital_status']) . "</td>"
        . "<td>" . $contractCell . "</td>"
        . "<td>" . formatCurrency($price_per_day) . "</td>"
        . "<td>" . date('Y/m/d', strtotime($tenant['start_date'])) . "</td>"
        . "<td>" . date('Y/m/d', strtotime($tenant['end_date'])) . "</td>"
        . "</tr>";
}
echo "</table>";
exit; 