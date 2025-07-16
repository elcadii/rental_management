<?php
require_once 'config/db.php';
$errors = [];
$success = '';
$admin_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
if (!$admin_id) die('معرف المدير غير صحيح');
// Fetch admin
$stmt = $pdo->prepare('SELECT * FROM admins WHERE id = ?');
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();
if (!$admin) die('المدير غير موجود');
$name = $admin['name'];
$email = $admin['email'];
$phone = $admin['phone'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email_or_phone = sanitize($_POST['email_or_phone'] ?? '');
    $new_email = null;
    $new_phone = null;
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    if (empty($name)) {
        $errors['name'] = 'الاسم مطلوب';
    }
    if (empty($email_or_phone)) {
        $errors['email_or_phone'] = 'البريد الإلكتروني أو رقم الهاتف مطلوب';
    } elseif (filter_var($email_or_phone, FILTER_VALIDATE_EMAIL)) {
        $new_email = $email_or_phone;
    } elseif (preg_match('/^\+?\d{8,15}$/', $email_or_phone)) {
        $new_phone = $email_or_phone;
    } else {
        $errors['email_or_phone'] = 'يرجى إدخال بريد إلكتروني أو رقم هاتف صحيح';
    }
    // Check uniqueness if changed
    if (empty($errors)) {
        if ($new_email && $new_email !== $email) {
            $stmt = $pdo->prepare('SELECT id FROM admins WHERE email = ? AND id != ?');
            $stmt->execute([$new_email, $admin_id]);
            if ($stmt->fetch()) {
                $errors['email_or_phone'] = 'البريد الإلكتروني مستخدم بالفعل';
            }
        }
        if ($new_phone && $new_phone !== $phone) {
            $stmt = $pdo->prepare('SELECT id FROM admins WHERE phone = ? AND id != ?');
            $stmt->execute([$new_phone, $admin_id]);
            if ($stmt->fetch()) {
                $errors['email_or_phone'] = 'رقم الهاتف مستخدم بالفعل';
            }
        }
    }
    // Password update (optional)
    $update_password = false;
    if (!empty($password) || !empty($confirm_password)) {
        if (strlen($password) < 6) {
            $errors['password'] = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
        } elseif ($password !== $confirm_password) {
            $errors['confirm_password'] = 'كلمتا المرور غير متطابقتين';
        } else {
            $update_password = true;
        }
    }
    if (empty($errors)) {
        if ($update_password) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE admins SET name = ?, email = ?, phone = ?, password = ? WHERE id = ?');
            $result = $stmt->execute([$name, $new_email ?? null, $new_phone ?? null, $hashed, $admin_id]);
        } else {
            $stmt = $pdo->prepare('UPDATE admins SET name = ?, email = ?, phone = ? WHERE id = ?');
            $result = $stmt->execute([$name, $new_email ?? null, $new_phone ?? null, $admin_id]);
        }
        if ($result) {
            $success = 'تم تحديث بيانات المدير بنجاح! سيتم تحويلك لإدارة المدراء...';
            echo '<meta http-equiv="refresh" content="2;url=manage_admins.php">';
        } else {
            $errors['general'] = 'حدث خطأ أثناء تحديث البيانات';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل بيانات المدير</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { font-family: 'Tajawal', Arial, sans-serif; }</style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-purple-50 min-h-screen ">
    <div class="max-w-lg mx-auto my-10 px-4">
        <div class="bg-white rounded-2xl shadow-2xl p-8 flex flex-col items-center text-center">
            <h2 class="text-2xl font-bold text-gray-900 mb-6"><i class="fa-solid fa-user-edit text-blue-500 ml-2"></i>تعديل بيانات المدير</h2>
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
            <form method="POST" class="w-full space-y-4 text-right">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">الاسم الكامل</label>
                    <input type="text" id="name" name="name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="أدخل اسم المدير" value="<?php echo htmlspecialchars($name ?? ''); ?>">
                    <div class="text-red-500 text-sm mt-1"><?php echo $errors['name'] ?? ''; ?></div>
                </div>
                <div>
                    <label for="email_or_phone" class="block text-sm font-medium text-gray-700 mb-1">البريد الإلكتروني أو رقم الهاتف</label>
                    <input type="text" id="email_or_phone" name="email_or_phone" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="أدخل بريد المدير أو رقم هاتفه" value="<?php echo htmlspecialchars($email ?: $phone); ?>">
                    <div class="text-red-500 text-sm mt-1"><?php echo $errors['email_or_phone'] ?? ''; ?></div>
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">كلمة مرور جديدة (اختياري)</label>
                    <input type="password" id="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="أدخل كلمة مرور جديدة إذا أردت تغييرها">
                    <div class="text-red-500 text-sm mt-1"><?php echo $errors['password'] ?? ''; ?></div>
                </div>
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">تأكيد كلمة المرور الجديدة</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="أعد إدخال كلمة المرور الجديدة">
                    <div class="text-red-500 text-sm mt-1"><?php echo $errors['confirm_password'] ?? ''; ?></div>
                </div>
                <button type="submit" class="w-full py-2 px-4 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow transition">تحديث البيانات</button>
            </form>
        </div>
    </div>
</body>
</html> 