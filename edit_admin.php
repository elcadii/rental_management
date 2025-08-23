<?php
require_once 'config/db.php';
require_once 'includes/i18n.php';
$errors = [];
$success = '';
$admin_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
if (!$admin_id) die(__('errors.invalid_admin_id'));
// Fetch admin
$stmt = $pdo->prepare('SELECT * FROM admins WHERE id = ?');
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();
if (!$admin) die(__('errors.admin_not_found'));
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
        $errors['name'] = __('errors.name_required');
    }
    if (empty($email_or_phone)) {
        $errors['email_or_phone'] = __('errors.email_or_phone_required');
    } elseif (filter_var($email_or_phone, FILTER_VALIDATE_EMAIL)) {
        $new_email = $email_or_phone;
    } elseif (preg_match('/^\+?\d{8,15}$/', $email_or_phone)) {
        $new_phone = $email_or_phone;
    } else {
        $errors['email_or_phone'] = __('errors.invalid_email_or_phone');
    }
    // Check uniqueness if changed
    if (empty($errors)) {
        if ($new_email && $new_email !== $email) {
            $stmt = $pdo->prepare('SELECT id FROM admins WHERE email = ? AND id != ?');
            $stmt->execute([$new_email, $admin_id]);
            if ($stmt->fetch()) {
                $errors['email_or_phone'] = __('errors.email_in_use');
            }
        }
        if ($new_phone && $new_phone !== $phone) {
            $stmt = $pdo->prepare('SELECT id FROM admins WHERE phone = ? AND id != ?');
            $stmt->execute([$new_phone, $admin_id]);
            if ($stmt->fetch()) {
                $errors['email_or_phone'] = __('errors.phone_in_use');
            }
        }
    }
    // Password update (optional)
    $update_password = false;
    if (!empty($password) || !empty($confirm_password)) {
        if (strlen($password) < 6) {
            $errors['password'] = __('errors.password_length');
        } elseif ($password !== $confirm_password) {
            $errors['confirm_password'] = __('errors.password_mismatch');
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
            $success = __('success.admin_updated');
            echo '<meta http-equiv="refresh" content="2;url=manage_admins.php">';
        } else {
            $errors['general'] = __('errors.update_failed');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(currentLang()); ?>" dir="<?php echo htmlspecialchars(currentDir()); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('edit_admin.title'); ?> - <?php echo __('app.title'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { font-family: 'Tajawal', Arial, sans-serif; }</style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-purple-50 min-h-screen" dir="<?php echo htmlspecialchars(currentDir()); ?>">
    <div class="max-w-lg mx-auto my-10 px-4">
        <div class="bg-white rounded-2xl shadow-2xl p-8 flex flex-col items-center text-center">
            <h2 class="text-2xl font-bold text-gray-900 mb-6"><i class="fa-solid fa-user-edit text-blue-500 ml-2"></i><?php echo __('edit_admin.heading'); ?></h2>
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
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('edit_admin.name'); ?></label>
                    <input type="text" id="name" name="name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="<?php echo __('edit_admin.placeholder_name'); ?>" value="<?php echo htmlspecialchars($name ?? ''); ?>">
                    <div class="text-red-500 text-sm mt-1"><?php echo $errors['name'] ?? ''; ?></div>
                </div>
                <div>
                    <label for="email_or_phone" class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('edit_admin.email_or_phone'); ?></label>
                    <input type="text" id="email_or_phone" name="email_or_phone" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="<?php echo __('edit_admin.placeholder_email_or_phone'); ?>" value="<?php echo htmlspecialchars($email ?: $phone); ?>">
                    <div class="text-red-500 text-sm mt-1"><?php echo $errors['email_or_phone'] ?? ''; ?></div>
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('edit_admin.new_password'); ?></label>
                    <input type="password" id="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="<?php echo __('edit_admin.placeholder_password'); ?>">
                    <div class="text-red-500 text-sm mt-1"><?php echo $errors['password'] ?? ''; ?></div>
                </div>
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('edit_admin.confirm_new_password'); ?></label>
                    <input type="password" id="confirm_password" name="confirm_password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="<?php echo __('edit_admin.placeholder_confirm_password'); ?>">
                    <div class="text-red-500 text-sm mt-1"><?php echo $errors['confirm_password'] ?? ''; ?></div>
                </div>
                <button type="submit" class="w-full py-2 px-4 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow transition"><?php echo __('edit_admin.submit'); ?></button>
            </form>
        </div>
    </div>
</body>
</html> 