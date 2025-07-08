<?php
require_once 'config/db.php';
requireLogin();

// Check if session admin_id exists in admins table
$stmt = $pdo->prepare('SELECT COUNT(*) FROM admins WHERE id = ?');
$stmt->execute([$_SESSION['admin_id']]);
if ($stmt->fetchColumn() == 0) {
    session_destroy();
    header('Location: login.php');
    exit();
}

$housing_type_error = '';
$housing_type_success = '';

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_housing_type'])) {
    $delete_id = (int)$_POST['delete_housing_type'];
    // Only delete if this type belongs to the logged-in admin
    $stmt = $pdo->prepare('DELETE FROM housing_types WHERE id = ? AND user_id = ?');
    if ($stmt->execute([$delete_id, $_SESSION['admin_id']])) {
        if ($stmt->rowCount() > 0) {
            $housing_type_success = 'تم حذف نوع السكن بنجاح.';
        } else {
            $housing_type_error = 'لا يمكنك حذف هذا النوع.';
        }
    } else {
        $housing_type_error = 'حدث خطأ أثناء الحذف.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_housing_type'])) {
    $type_name = sanitize($_POST['housing_type_name'] ?? '');
    if (empty($type_name)) {
        $housing_type_error = 'اسم نوع السكن مطلوب';
    } else {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM housing_types WHERE name = ? AND user_id = ?');
        $stmt->execute([$type_name, $_SESSION['admin_id']]);
        if ($stmt->fetchColumn() > 0) {
            $housing_type_error = 'هذا النوع مضاف بالفعل.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO housing_types (name, user_id) VALUES (?, ?)');
            $stmt->execute([$type_name, $_SESSION['admin_id']]);
            $housing_type_success = 'تمت إضافة نوع السكن بنجاح!';
        }
    }
}
$stmt = $pdo->prepare('SELECT * FROM housing_types WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$_SESSION['admin_id']]);
$admin_housing_types = $stmt->fetchAll();
?>
<?php include 'header.php'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة أنواع السكن</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Tajawal', Arial, sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    
    <div class="max-w-2xl mx-auto mt-12 mb-12 bg-white rounded-2xl shadow-2xl p-4 sm:p-8 border border-blue-100">
        <div class="flex flex-col sm:flex-row items-center mb-6 border-b pb-4 gap-2">
            <div class="bg-blue-100 p-3 rounded-full ml-0 sm:ml-4 mb-2 sm:mb-0">
                <i class="fas fa-building text-blue-600 text-2xl"></i>
            </div>
            <h2 class="text-2xl font-extrabold text-gray-900">إدارة أنواع السكن</h2>
        </div>
        <?php if ($housing_type_success): ?>
            <div class="text-green-700 bg-green-50 border border-green-200 rounded-lg px-4 py-2 mb-4 text-sm flex items-center gap-2">
                <i class="fas fa-check-circle"></i> <?php echo $housing_type_success; ?>
            </div>
        <?php endif; ?>
        <?php if ($housing_type_error): ?>
            <div class="text-red-600 bg-red-50 border border-red-200 rounded-lg px-4 py-2 mb-4 text-sm flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i> <?php echo $housing_type_error; ?>
            </div>
        <?php endif; ?>
        <form method="post" class="flex flex-col sm:flex-row gap-4 items-center mb-6">
            <input type="text" name="housing_type_name" placeholder="أدخل اسم نوع السكن الجديد" class="flex-1 px-4 py-3 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-blue-500 transition text-lg" required>
            <button type="submit" name="add_housing_type" class="px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold text-lg transition flex items-center gap-2">
                <i class="fas fa-plus"></i> إضافة
            </button>
        </form>
        <div>
            <h3 class="text-lg font-bold text-gray-800 mb-3 flex items-center gap-2">
                <i class="fas fa-list-ul text-blue-400"></i> أنواع السكن المضافة:
            </h3>
            <?php if (empty($admin_housing_types)): ?>
                <div class="text-gray-400 italic">لم تقم بإضافة أي نوع بعد.</div>
            <?php else: ?>
                <ul class="space-y-2">
                    <?php foreach ($admin_housing_types as $type): ?>
                        <li class="flex items-center justify-between bg-blue-50 border border-blue-100 rounded-lg px-4 py-2">
                            <span class="text-blue-900 font-medium flex items-center gap-2">
                                <i class="fas fa-home text-blue-300"></i> <?php echo htmlspecialchars($type['name']); ?>
                            </span>
                            <form method="post" class="m-0 p-0">
                                <input type="hidden" name="delete_housing_type" value="<?php echo $type['id']; ?>">
                                <button type="submit" class="text-red-500 hover:text-red-700 transition flex items-center gap-1" onclick="return confirm('هل أنت متأكد من حذف هذا النوع؟');">
                                    <i class="fas fa-trash-alt"></i> حذف
                                </button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 