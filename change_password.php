<?php
require_once 'config/db.php';
require_once 'includes/i18n.php';
requireLogin();
$admin_id = $_SESSION['admin_id'];
$errors = [];
$success = '';

include 'header.php';
include 'sidebar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Fetch current password hash
    $stmt = $pdo->prepare('SELECT password FROM admins WHERE id = ?');
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch();
    if (!$admin || !password_verify($current_password, $admin['password'])) {
        $errors['current_password'] = __('errors.current_password_incorrect');
    }
    if (empty($new_password)) {
        $errors['new_password'] = __('errors.new_password_required');
    } elseif (strlen($new_password) < 6) {
        $errors['new_password'] = __('errors.password_length');
    }
    if ($new_password !== $confirm_password) {
        $errors['confirm_password'] = __('errors.password_mismatch');
    }
    if (empty($errors)) {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE admins SET password = ? WHERE id = ?');
        if ($stmt->execute([$hashed, $admin_id])) {
            $success = __('success.password_changed');
        } else {
            $errors['general'] = __('errors.password_change_failed');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(currentLang()); ?>" dir="<?php echo htmlspecialchars(currentDir()); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('change_password.title'); ?> - <?php echo __('app.title'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Tajawal', Arial, sans-serif; }</style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-purple-50 min-h-screen" dir="<?php echo htmlspecialchars(currentDir()); ?>">
    <div class="max-w-[60%] mx-auto my-8 px-4">
        <div class="bg-white rounded-2xl shadow-2xl p-8 flex flex-col items-center text-center">
            <h2 class="text-2xl font-bold text-gray-900 mb-6"><?php echo __('change_password.heading'); ?></h2>
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
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('change_password.current_password'); ?></label>
                    <input type="password" id="current_password" name="current_password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="<?php echo __('change_password.placeholder_current'); ?>">
                    <div class="text-red-500 text-sm mt-1"><?php echo $errors['current_password'] ?? ''; ?></div>
                </div>
                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('change_password.new_password'); ?></label>
                    <input type="password" id="new_password" name="new_password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="<?php echo __('change_password.placeholder_new'); ?>">
                    <div class="text-red-500 text-sm mt-1"><?php echo $errors['new_password'] ?? ''; ?></div>
                </div>
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('change_password.confirm_new_password'); ?></label>
                    <input type="password" id="confirm_password" name="confirm_password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="<?php echo __('change_password.placeholder_confirm'); ?>">
                    <div class="text-red-500 text-sm mt-1"><?php echo $errors['confirm_password'] ?? ''; ?></div>
                </div>
                <button type="submit" class="w-full py-2 px-4 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow transition"><?php echo __('change_password.submit'); ?></button>
            </form>
        </div>
    </div>
</body>
</html> 