<?php
require_once 'config/db.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // التحقق من صحة البيانات
    if (empty($name)) {
        $errors['name'] = 'الاسم مطلوب';
    }
    
    if (empty($email)) {
        $errors['email'] = 'البريد الإلكتروني مطلوب';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'البريد الإلكتروني غير صحيح';
    }
    
    if (empty($password)) {
        $errors['password'] = 'كلمة المرور مطلوبة';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
    }
    
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'كلمات المرور غير متطابقة';
    }
    
    // التحقق من وجود البريد الإلكتروني
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors['email'] = 'البريد الإلكتروني مستخدم بالفعل';
        }
    }
    
    // إنشاء الحساب
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admins (name, email, password) VALUES (?, ?, ?)");
        
        if ($stmt->execute([$name, $email, $hashed_password])) {
            $success = 'تم إنشاء الحساب بنجاح! يمكنك الآن تسجيل الدخول.';
            header('Location: login.php');
        } else {
            $errors['general'] = 'حدث خطأ أثناء إنشاء الحساب';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء حساب جديد - نظام إدارة الإيجارات</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'arabic': ['Tajawal', 'Arial', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-50 font-arabic">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-bold text-gray-900">
                    إنشاء حساب جديد
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    أو
                    <a href="login.php" class="font-medium text-blue-600 hover:text-blue-500">
                        سجل دخولك إذا كان لديك حساب
                    </a>
                </p>
            </div>
            
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($errors['general'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <?php echo $errors['general']; ?>
                </div>
            <?php endif; ?>
            
            <form class="mt-8 space-y-6" method="POST" id="registerForm">
                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">الاسم الكامل</label>
                        <input id="name" name="name" type="text" required 
                               class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                               placeholder="أدخل اسمك الكامل" value="<?php echo htmlspecialchars($name ?? ''); ?>">
                        <div id="name-error" class="text-red-500 text-sm mt-1">
                            <?php echo $errors['name'] ?? ''; ?>
                        </div>
                    </div>
                    
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
                    
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700">تأكيد كلمة المرور</label>
                        <input id="confirm_password" name="confirm_password" type="password" required 
                               class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                               placeholder="أعد إدخال كلمة المرور">
                        <div id="confirm_password-error" class="text-red-500 text-sm mt-1">
                            <?php echo $errors['confirm_password'] ?? ''; ?>
                        </div>
                    </div>
                </div>
                
                <div>
                    <button type="submit" 
                            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        إنشاء الحساب
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            let isValid = true;
            
            // مسح الأخطاء السابقة
            document.querySelectorAll('[id$="-error"]').forEach(el => el.textContent = '');
            
            // التحقق من الاسم
            const name = document.getElementById('name').value.trim();
            if (!name) {
                document.getElementById('name-error').textContent = 'الاسم مطلوب';
                isValid = false;
            }
            
            // التحقق من البريد الإلكتروني
            const email = document.getElementById('email').value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email) {
                document.getElementById('email-error').textContent = 'البريد الإلكتروني مطلوب';
                isValid = false;
            } else if (!emailRegex.test(email)) {
                document.getElementById('email-error').textContent = 'البريد الإلكتروني غير صحيح';
                isValid = false;
            }
            
            // التحقق من كلمة المرور
            const password = document.getElementById('password').value;
            if (!password) {
                document.getElementById('password-error').textContent = 'كلمة المرور مطلوبة';
                isValid = false;
            } else if (password.length < 6) {
                document.getElementById('password-error').textContent = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
                isValid = false;
            }
            
            // التحقق من تأكيد كلمة المرور
            const confirmPassword = document.getElementById('confirm_password').value;
            if (password !== confirmPassword) {
                document.getElementById('confirm_password-error').textContent = 'كلمات المرور غير متطابقة';
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
