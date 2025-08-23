<?php
require_once __DIR__ . '/config/db.php';
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(currentLang()); ?>" dir="<?php echo htmlspecialchars(currentDir()); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('errors.not_found.title'); ?> - <?php echo __('app.title'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="text-center p-8">
        <div class="text-8xl font-extrabold text-blue-600">404</div>
        <h1 class="mt-4 text-2xl font-bold text-gray-900"><?php echo __('errors.not_found.heading'); ?></h1>
        <p class="mt-2 text-gray-600"><?php echo __('errors.not_found.desc'); ?></p>
        <a href="index.php" class="mt-6 inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            <?php echo __('errors.not_found.back_home'); ?>
        </a>
    </div>
</body>
</html>

