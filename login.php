<?php
require_once 'config/db.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email)) {
        $errors['email'] = 'البريد الإلكتروني مطلوب';
    }
    
    if (empty($password)) {
        $errors['password'] = 'كلمة المرور مطلوبة';
    }
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id, name, email, password FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_email'] = $admin['email'];
            
            header('Location: index.php');
            exit();
        } else {
            $errors['general'] = 'البريد الإلكتروني أو كلمة المرور غير صحيحة';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - نظام إدارة الإيجارات</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Tajawal', Arial, sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-8 px-2 sm:px-4 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-bold text-gray-900">
                    تسجيل الدخول
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    أو
                    <a href="register.php" class="font-medium text-blue-600 hover:text-blue-500">
                        أنشئ حساباً جديداً
                    </a>
                </p>
            </div>
            
            <?php if (isset($errors['general'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <?php echo $errors['general']; ?>
                </div>
            <?php endif; ?>
            
            <form class="mt-8 space-y-6" method="POST" id="loginForm">
                <div class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">البريد الإلكتروني</label>
                        <input id="email" name="email" type="email" required 
                               class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                               placeholder="أدخل بريدك الإلكتروني" value="<?php echo htmlspecialchars($email ?? ''); ?>">
                        <div id="email-error" class="text-red-500 text-sm mt-1">
                            <?php echo $errors['email'] ?? ''; ?>
                        </div>
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">كلمة المرور</label>
                        <input id="password" name="password" type="password" required 
                               class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                               placeholder="أدخل كلمة المرور">
                        <div id="password-error" class="text-red-500 text-sm mt-1">
                            <?php echo $errors['password'] ?? ''; ?>
                        </div>
                    </div>
                </div>
                
                <div>
                    <button type="submit" 
                            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        تسجيل الدخول
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            let isValid = true;
            
            // Clear previous errors
            document.querySelectorAll('[id$="-error"]').forEach(el => el.textContent = '');
            
            // Validate email
            const email = document.getElementById('email').value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email) {
                document.getElementById('email-error').textContent = 'البريد الإلكتروني مطلوب';
                isValid = false;
            } else if (!emailRegex.test(email)) {
                document.getElementById('email-error').textContent = 'البريد الإلكتروني غير صحيح';
                isValid = false;
            }
            
            // Validate password
            const password = document.getElementById('password').value;
            if (!password) {
                document.getElementById('password-error').textContent = 'كلمة المرور مطلوبة';
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
