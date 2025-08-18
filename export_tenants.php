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
$query = "SELECT id, full_name, phone, email, cin, address, house_type, marital_status, total_rent, start_date, end_date, created_at, price_per_day, marriage_contract FROM tenants WHERE admin_id = ?";
$params = [$_SESSION['admin_id']];
if ($filter_housing_type !== '') {
    $query .= " AND house_type = ?";
    $params[] = $filter_housing_type;
}
if ($search_query !== '') {
    $query .= " AND (full_name LIKE ? OR phone LIKE ? OR email LIKE ? OR cin LIKE ? OR address LIKE ?)";
    $search_term = "%$search_query%";
    $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term, $search_term]);
}
$query .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$tenants = $stmt->fetchAll();

// Build base URL for file links
$baseUrl = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http')
	. '://' . $_SERVER['HTTP_HOST']
	. rtrim(dirname($_SERVER['REQUEST_URI']), '/\\') . '/';

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=tenants_export_' . date('Ymd_His') . '.csv');

$output = fopen('php://output', 'w');
// Output column headers (in Arabic, matching the table)
fputcsv($output, [
    'الاسم',
    'الهاتف',
    'البريد الإلكتروني',
    'رقم الهوية',
    'العنوان',
    'نوع السكن',
    'الحالة الاجتماعية',
    'عقد الزواج',
    'سعر اليوم',
    'إجمالي الإيجار',
    'تاريخ البداية',
    'تاريخ النهاية',
    'الحالة'
]);

foreach ($tenants as $tenant) {
    $isActive = strtotime($tenant['end_date']) >= time();
    
    // Convert values
    $price_per_day = convertCurrency($tenant['price_per_day'] ?? 0);
    $total_rent = convertCurrency($tenant['total_rent'] ?? 0);
    
    $contractValue = '';
    if ($tenant['marital_status'] === 'Married' && !empty($tenant['marriage_contract'])) {
        $contractValue = $baseUrl . ltrim($tenant['marriage_contract'], '/');
    } elseif ($tenant['marital_status'] === 'Married') {
        $contractValue = 'غير متوفر';
    } else {
        $contractValue = '-';
    }
    
    fputcsv($output, [
        $tenant['full_name'],
        $tenant['phone'],
        $tenant['email'] ?: 'غير محدد',
        $tenant['cin'],
        $tenant['address'] ?: 'غير محدد',
        isset($housing_type_map[$tenant['house_type']]) ? $housing_type_map[$tenant['house_type']] : $tenant['house_type'],
        $tenant['marital_status'],
        $contractValue,
        formatCurrency($price_per_day),
        formatCurrency($total_rent),
        date('Y/m/d', strtotime($tenant['start_date'])),
        date('Y/m/d', strtotime($tenant['end_date'])),
        $isActive ? 'نشط' : 'منتهي'
    ]);
}
fclose($output);
exit; 