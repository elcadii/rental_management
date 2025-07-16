<?php
require_once 'config/db.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_or_phone = sanitize($_POST['email_or_phone'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email_or_phone)) {
        $errors['email_or_phone'] = 'البريد الإلكتروني أو رقم الهاتف مطلوب';
    }
    
    if (empty($password)) {
        $errors['password'] = 'كلمة المرور مطلوبة';
    }
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id, name, email, phone, password FROM admins WHERE (email = ? OR phone = ?)");
        $stmt->execute([$email_or_phone, $email_or_phone]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_phone'] = $admin['phone'];
            
            header('Location: index.php');
            exit();
        } else {
            $errors['general'] = 'البريد الإلكتروني أو رقم الهاتف أو كلمة المرور غير صحيحة';
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
                        <label for="email_or_phone" class="block text-sm font-medium text-gray-700">البريد الإلكتروني أو رقم الهاتف</label>
                        <input id="email_or_phone" name="email_or_phone" type="text" required 
                               class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                               placeholder="أدخل بريدك الإلكتروني أو رقم هاتفك" value="<?php echo htmlspecialchars($email_or_phone ?? ''); ?>">
                        <div id="email_or_phone-error" class="text-red-500 text-sm mt-1">
                            <?php echo $errors['email_or_phone'] ?? ''; ?>
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
            
            // Validate email or phone
            const emailOrPhone = document.getElementById('email_or_phone').value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const phoneRegex = /^\+?\d{8,15}$/;
            if (!emailOrPhone) {
                document.getElementById('email_or_phone-error').textContent = 'البريد الإلكتروني أو رقم الهاتف مطلوب';
                isValid = false;
            } else if (!emailRegex.test(emailOrPhone) && !phoneRegex.test(emailOrPhone)) {
                document.getElementById('email_or_phone-error').textContent = 'يرجى إدخال بريد إلكتروني أو رقم هاتف صحيح';
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
