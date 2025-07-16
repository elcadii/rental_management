<?php
require_once 'config/db.php';
$success = '';
$errors = [];
// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare('DELETE FROM admins WHERE id = ?');
    if ($stmt->execute([$delete_id])) {
        $success = 'تم حذف المدير بنجاح';
    } else {
        $errors['general'] = 'حدث خطأ أثناء حذف المدير';
    }
}
$admins = $pdo->query('SELECT * FROM admins ORDER BY created_at DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المدراء</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { font-family: 'Tajawal', Arial, sans-serif; }</style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-purple-50 min-h-screen ">
    <div class="max-w-4xl mx-auto my-10 px-4">
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900"><i class="fa-solid fa-users-cog text-blue-500 ml-2"></i>إدارة المدراء</h2>
                <a href="add_admin.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl font-semibold flex items-center gap-2"><i class="fa-solid fa-user-plus"></i>إضافة مدير</a>
            </div>
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 w-full">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            <?php if (isset($errors['general'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 w-full">
                    <?php echo $errors['general']; ?>
                </div>
            <?php endif; ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-center">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-xs font-bold text-gray-700 uppercase tracking-wider">الاسم</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-700 uppercase tracking-wider">البريد الإلكتروني</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-700 uppercase tracking-wider">رقم الهاتف</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-700 uppercase tracking-wider">تاريخ الإنشاء</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-700 uppercase tracking-wider">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($admins as $admin): ?>
                            <tr class="hover:bg-blue-50">
                                <td class="px-6 py-4 whitespace-nowrap font-semibold text-blue-700"><?php echo htmlspecialchars($admin['name']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($admin['email']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($admin['phone'] ?? 'غير محدد'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo date('Y/m/d', strtotime($admin['created_at'])); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap flex gap-2 justify-center">
                                    <a href="edit_admin.php?edit=<?php echo $admin['id']; ?>" class="text-blue-600 hover:text-blue-800"><i class="fa-solid fa-pen-to-square"></i></a>
                                    <a href="manage_admins.php?delete=<?php echo $admin['id']; ?>" class="text-red-600 hover:text-red-800" onclick="return confirm('هل أنت متأكد من حذف هذا المدير؟');"><i class="fa-solid fa-trash"></i></a>
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