<?php
require_once 'config/db.php';
require_once 'includes/trial_manager.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email_or_phone = sanitize($_POST['email_or_phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $email = null;
    $phone = null;
    
    // Determine if input is email or phone
    if (empty($email_or_phone)) {
        $errors['email_or_phone'] = 'البريد الإلكتروني أو رقم الهاتف مطلوب';
    } elseif (filter_var($email_or_phone, FILTER_VALIDATE_EMAIL)) {
        $email = $email_or_phone;
    } elseif (preg_match('/^\+?\d{8,15}$/', $email_or_phone)) {
        $phone = $email_or_phone;
    } else {
        $errors['email_or_phone'] = 'يرجى إدخال بريد إلكتروني أو رقم هاتف صحيح';
    }
    
    if (empty($name)) {
        $errors['name'] = 'الاسم مطلوب';
    }
    
    if (empty($password)) {
        $errors['password'] = 'كلمة المرور مطلوبة';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
    }
    
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'كلمات المرور غير متطابقة';
    }
    
    // Check if email or phone exists
    if (empty($errors)) {
        if ($email) {
            $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors['email_or_phone'] = 'البريد الإلكتروني مستخدم بالفعل';
            }
        }
        if ($phone) {
            $stmt = $pdo->prepare("SELECT id FROM admins WHERE phone = ?");
            $stmt->execute([$phone]);
            if ($stmt->fetch()) {
                $errors['email_or_phone'] = 'رقم الهاتف مستخدم بالفعل';
            }
        }
    }
    
    // Create account
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admins (name, email, phone, password) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$name, $email, $phone, $hashed_password])) {
            $admin_id = $pdo->lastInsertId();
            
            // Start trial for new user
            $trial_manager = new TrialManager($pdo);
            $trial_manager->startTrial($admin_id);
            
            $success = 'تم إنشاء الحساب بنجاح! يمكنك الآن تسجيل الدخول.';
            header('Location: login.php');
            exit();
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
    <div class="min-h-screen flex items-center justify-center py-8 px-2 sm:px-4 lg:px-8">
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
            
            // Clear previous errors
            document.querySelectorAll('[id$="-error"]').forEach(el => el.textContent = '');
            
            // Validate name
            const name = document.getElementById('name').value.trim();
            if (!name) {
                document.getElementById('name-error').textContent = 'الاسم مطلوب';
                isValid = false;
            }
            
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
            } else if (password.length < 6) {
                document.getElementById('password-error').textContent = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
                isValid = false;
            }
            
            // Validate confirm password
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
