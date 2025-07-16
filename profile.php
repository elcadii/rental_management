<?php
require_once 'config/db.php';
requireLogin();

$admin_id = $_SESSION['admin_id'];

// Fetch admin info (including created_at)
$stmt = $pdo->prepare("SELECT name, email, phone, created_at FROM admins WHERE id = ?");
$stmt->execute([$admin_id]);
$user = $stmt->fetch();
if (!$user) die('المستخدم غير موجود');
$name = $user['name'];
$email = $user['email'];
$phone = $user['phone'];
$created_at = $user['created_at'];

// Fetch statistics
// 1. Number of tenants
$stmt = $pdo->prepare("SELECT COUNT(*) FROM tenants WHERE admin_id = ?");
$stmt->execute([$admin_id]);
$total_tenants = $stmt->fetchColumn();
// 2. Number of housing types
$stmt = $pdo->prepare("SELECT COUNT(*) FROM housing_types WHERE user_id = ?");
$stmt->execute([$admin_id]);
$total_housing_types = $stmt->fetchColumn();
// 3. Active contracts (tenants with end_date >= today)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM tenants WHERE admin_id = ? AND end_date >= CURDATE()");
$stmt->execute([$admin_id]);
$active_contracts = $stmt->fetchColumn();
// 4. Expired contracts (tenants with end_date < today)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM tenants WHERE admin_id = ? AND end_date < CURDATE()");
$stmt->execute([$admin_id]);
$expired_contracts = $stmt->fetchColumn();
// 5. Total rent
$stmt = $pdo->prepare("SELECT SUM(total_rent) FROM tenants WHERE admin_id = ?");
$stmt->execute([$admin_id]);
$total_rent = $stmt->fetchColumn() ?: 0;
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الملف الشخصي - نظام إدارة الإيجارات</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { font-family: 'Tajawal', Arial, sans-serif; }</style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-purple-50 min-h-screen   ">
    <?php
    include 'header.php';
    include 'sidebar.php';
    ?>
    <div class="max-w-[85%] mx-auto my-10 px-4">
        <!-- Profile Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-8 mb-8 flex flex-col items-center text-center">
            <div class="w-24 h-24 rounded-full bg-gradient-to-br from-blue-400 to-purple-400 flex items-center justify-center mb-4 shadow-lg">
                <i class="fa-solid fa-user text-white text-4xl"></i>
            </div>
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($name); ?></h2>
            <div class="flex flex-col gap-1 text-gray-700 font-semibold mb-4">
                <div><i class="fa-solid fa-envelope text-blue-500 ml-2"></i> <?php echo $email ? htmlspecialchars($email) : '<span class=\'text-gray-400\'>غير محدد</span>'; ?></div>
                <div><i class="fa-solid fa-phone text-purple-500 ml-2"></i> <?php echo $phone ? htmlspecialchars($phone) : '<span class=\'text-gray-400\'>غير محدد</span>'; ?></div>
                <div><i class="fa-solid fa-calendar text-green-500 ml-2"></i> <span class="text-gray-500 font-normal">عضو منذ:</span> <?php echo date('Y/m/d', strtotime($created_at)); ?></div>
            </div>
            <div class="flex flex-col sm:flex-row gap-4 w-full justify-center mt-2">
                <a href="edit_profile.php" class="flex-1 py-2 px-4 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow transition text-center"><i class="fa-solid fa-pen-to-square ml-2"></i>تعديل المعلومات</a>
                <a href="change_password.php" class="flex-1 py-2 px-4 rounded-2xl bg-purple-600 hover:bg-purple-700 text-white font-semibold shadow transition text-center"><i class="fa-solid fa-key ml-2"></i>تغيير كلمة المرور</a>
            </div>
        </div>
        <!-- Statistics Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-2xl shadow-md p-6 flex flex-col items-center hover:shadow-xl transition">
                <div class="bg-blue-100 text-blue-600 rounded-full p-3 mb-2"><i class="fa-solid fa-users text-2xl"></i></div>
                <div class="text-2xl font-bold"><?php echo $total_tenants; ?></div>
                <div class="text-gray-700 font-semibold mt-1">عدد المستأجرين</div>
            </div>
            <div class="bg-white rounded-2xl shadow-md p-6 flex flex-col items-center hover:shadow-xl transition">
                <div class="bg-purple-100 text-purple-600 rounded-full p-3 mb-2"><i class="fa-solid fa-building text-2xl"></i></div>
                <div class="text-2xl font-bold"><?php echo $total_housing_types; ?></div>
                <div class="text-gray-700 font-semibold mt-1">أنواع السكن</div>
            </div>
            <div class="bg-white rounded-2xl shadow-md p-6 flex flex-col items-center hover:shadow-xl transition">
                <div class="bg-green-100 text-green-600 rounded-full p-3 mb-2"><i class="fa-solid fa-file-contract text-2xl"></i></div>
                <div class="text-2xl font-bold"><?php echo $active_contracts; ?></div>
                <div class="text-gray-700 font-semibold mt-1">عقود نشطة</div>
            </div>
            <div class="bg-white rounded-2xl shadow-md p-6 flex flex-col items-center hover:shadow-xl transition">
                <div class="bg-red-100 text-red-600 rounded-full p-3 mb-2"><i class="fa-solid fa-file-circle-xmark text-2xl"></i></div>
                <div class="text-2xl font-bold"><?php echo $expired_contracts; ?></div>
                <div class="text-gray-700 font-semibold mt-1">عقود منتهية</div>
            </div>
        </div>
        <!-- Total Rent Card -->
        <div class="bg-gradient-to-r from-purple-500 to-blue-500 rounded-2xl shadow-lg p-8 flex flex-col items-center text-white mb-8">
            <div class="flex items-center gap-3 mb-2">
                <i class="fa-solid fa-coins text-3xl"></i>
                <span class="text-3xl font-bold"><?php echo number_format($total_rent, 2); ?></span>
            </div>
            <div class="text-lg font-semibold">إجمالي الإيجار المحصل</div>
        </div>
    </div>
</body>
</html> 