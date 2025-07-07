<?php
require_once 'config/db.php';
requireLogin();

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $cin = sanitize($_POST['cin'] ?? '');
    $house_type = sanitize($_POST['house_type'] ?? '');
    $start_date = sanitize($_POST['start_date'] ?? '');
    $end_date = sanitize($_POST['end_date'] ?? '');
    
    // التحقق من صحة البيانات
    if (empty($full_name)) {
        $errors['full_name'] = 'الاسم الكامل مطلوب';
    }
    
    if (empty($phone)) {
        $errors['phone'] = 'رقم الهاتف مطلوب';
    }
    
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'البريد الإلكتروني غير صحيح';
    }
    
    if (empty($cin)) {
        $errors['cin'] = 'رقم الهوية مطلوب';
    }
    
    if (empty($house_type)) {
        $errors['house_type'] = 'نوع السكن مطلوب';
    }
    
    if (empty($start_date)) {
        $errors['start_date'] = 'تاريخ بداية الإيجار مطلوب';
    }
    
    if (empty($end_date)) {
        $errors['end_date'] = 'تاريخ نهاية الإيجار مطلوب';
    }
    
    // التحقق من أن تاريخ النهاية بعد تاريخ البداية
    if (!empty($start_date) && !empty($end_date)) {
        if (strtotime($end_date) <= strtotime($start_date)) {
            $errors['end_date'] = 'تاريخ النهاية يجب أن يكون بعد تاريخ البداية';
        }
    }
    
    // إضافة المستأجر
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO tenants (full_name, phone, email, cin, house_type, start_date, end_date, admin_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$full_name, $phone, $email ?: null, $cin, $house_type, $start_date, $end_date, $_SESSION['admin_id']])) {
            $success = 'تم إضافة المستأجر بنجاح!';
            // مسح البيانات بعد الإضافة الناجحة
            $full_name = $phone = $email = $cin = $house_type = $start_date = $end_date = '';
        } else {
            $errors['general'] = 'حدث خطأ أثناء إضافة المستأجر';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة مستأجر جديد - نظام إدارة الإيجارات</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Tajawal', Arial, sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    </style>
</head>
<body class="bg-gray-50">
    <!-- شريط التنقل -->
    <nav class="bg-white shadow-lg border-b-2 border-blue-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-8 space-x-reverse">
                    <div class="flex items-center">
                        <i class="fas fa-home text-blue-600 text-2xl ml-3"></i>
                        <a href="dashboard.php" class="text-xl font-bold text-gray-900">نظام الإيجارات</a>
                    </div>
                    <div class="hidden md:flex items-center space-x-6 space-x-reverse">
                        <a href="dashboard.php" class="flex items-center text-gray-700 hover:text-blue-600 font-medium">
                            <i class="fas fa-home ml-2"></i>
                            الرئيسية
                        </a>
                        <a href="dashboard.php#tenants-list" class="flex items-center text-gray-700 hover:text-blue-600 font-medium">
                            <i class="fas fa-users ml-2"></i>
                            قائمة المستأجرين
                        </a>
                        <a href="add_tenant.php" class="flex items-center text-blue-600 hover:text-blue-800 font-medium">
                            <i class="fas fa-plus ml-2"></i>
                            إضافة مستأجر
                        </a>
                    </div>
                </div>
                <div class="flex items-center space-x-4 space-x-reverse">
                    <span class="text-gray-700 font-medium">مرحباً، <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                    <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200">
                        <i class="fas fa-sign-out-alt ml-1"></i>
                        تسجيل الخروج
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- القسم الرئيسي -->
    <div class="gradient-bg py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl font-bold text-white mb-2">إضافة مستأجر جديد</h1>
            <p class="text-blue-100">أدخل بيانات المستأجر الجديد بدقة</p>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <div class="flex items-center">
                    <i class="fas fa-user-plus text-blue-600 text-xl ml-3"></i>
                    <h3 class="text-lg font-bold text-gray-900">نموذج إضافة مستأجر</h3>
                </div>
            </div>
            
            <?php if ($success): ?>
                <div class="mx-6 mt-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle ml-2"></i>
                        <?php echo $success; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (isset($errors['general'])): ?>
                <div class="mx-6 mt-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle ml-2"></i>
                        <?php echo $errors['general']; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="tenantForm" class="p-6">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- الاسم الكامل -->
                    <div class="sm:col-span-2">
                        <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user ml-1"></i>
                            الاسم الكامل للمستأجر
                        </label>
                        <input type="text" name="full_name" id="full_name" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                               placeholder="أدخل الاسم الكامل"
                               value="<?php echo htmlspecialchars($full_name ?? ''); ?>">
                        <div id="full_name-error" class="text-red-500 text-sm mt-1">
                            <?php echo $errors['full_name'] ?? ''; ?>
                        </div>
                    </div>
                    
                    <!-- رقم الهاتف -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-phone ml-1"></i>
                            رقم الهاتف
                        </label>
                        <input type="tel" name="phone" id="phone" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                               placeholder="مثال: 0123456789"
                               value="<?php echo htmlspecialchars($phone ?? ''); ?>">
                        <div id="phone-error" class="text-red-500 text-sm mt-1">
                            <?php echo $errors['phone'] ?? ''; ?>
                        </div>
                    </div>
                    
                    <!-- البريد الإلكتروني -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-envelope ml-1"></i>
                            البريد الإلكتروني <span class="text-gray-500">(اختياري)</span>
                        </label>
                        <input type="email" name="email" id="email"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                               placeholder="example@email.com"
                               value="<?php echo htmlspecialchars($email ?? ''); ?>">
                        <div id="email-error" class="text-red-500 text-sm mt-1">
                            <?php echo $errors['email'] ?? ''; ?>
                        </div>
                    </div>
                    
                    <!-- رقم الهوية -->
                    <div>
                        <label for="cin" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-id-card ml-1"></i>
                            رقم الهوية الوطنية (CIN)
                        </label>
                        <input type="text" name="cin" id="cin" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                               placeholder="مثال: AB123456"
                               value="<?php echo htmlspecialchars($cin ?? ''); ?>">
                        <div id="cin-error" class="text-red-500 text-sm mt-1">
                            <?php echo $errors['cin'] ?? ''; ?>
                        </div>
                    </div>
                    
                    <!-- نوع السكن -->
                    <div>
                        <label for="house_type" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-building ml-1"></i>
                            نوع السكن أو الغرفة
                        </label>
                        <select name="house_type" id="house_type" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                            <option value="">اختر نوع السكن</option>
                            <option value="شقة" <?php echo ($house_type ?? '') === 'شقة' ? 'selected' : ''; ?>>شقة</option>
                            <option value="فيلا" <?php echo ($house_type ?? '') === 'فيلا' ? 'selected' : ''; ?>>فيلا</option>
                            <option value="استوديو" <?php echo ($house_type ?? '') === 'استوديو' ? 'selected' : ''; ?>>استوديو</option>
                            <option value="غرفة" <?php echo ($house_type ?? '') === 'غرفة' ? 'selected' : ''; ?>>غرفة</option>
                            <option value="محل تجاري" <?php echo ($house_type ?? '') === 'محل تجاري' ? 'selected' : ''; ?>>محل تجاري</option>
                            <option value="مكتب" <?php echo ($house_type ?? '') === 'مكتب' ? 'selected' : ''; ?>>مكتب</option>
                        </select>
                        <div id="house_type-error" class="text-red-500 text-sm mt-1">
                            <?php echo $errors['house_type'] ?? ''; ?>
                        </div>
                    </div>
                    
                    <!-- تاريخ البداية -->
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-alt ml-1"></i>
                            تاريخ بداية الإيجار
                        </label>
                        <input type="date" name="start_date" id="start_date" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                               value="<?php echo htmlspecialchars($start_date ?? ''); ?>">
                        <div id="start_date-error" class="text-red-500 text-sm mt-1">
                            <?php echo $errors['start_date'] ?? ''; ?>
                        </div>
                    </div>
                    
                    <!-- تاريخ النهاية -->
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-check ml-1"></i>
                            تاريخ نهاية الإيجار
                        </label>
                        <input type="date" name="end_date" id="end_date" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                               value="<?php echo htmlspecialchars($end_date ?? ''); ?>">
                        <div id="end_date-error" class="text-red-500 text-sm mt-1">
                            <?php echo $errors['end_date'] ?? ''; ?>
                        </div>
                    </div>
                </div>
                
                <!-- معلومات إضافية -->
                <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-info-circle text-blue-600 ml-2"></i>
                        <h4 class="font-medium text-blue-900">معلومات مهمة</h4>
                    </div>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>• سيتم حفظ هذا المستأجر تحت حسابك تلقائياً</li>
                        <li>• تأكد من صحة جميع البيانات قبل الحفظ</li>
                        <li>• يمكنك تعديل البيانات لاحقاً من قائمة المستأجرين</li>
                    </ul>
                </div>
                
                <!-- أزرار الإجراءات -->
                <div class="mt-8 flex justify-end space-x-4 space-x-reverse">
                    <a href="dashboard.php" 
                       class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition duration-200">
                        <i class="fas fa-times ml-1"></i>
                        إلغاء
                    </a>
                    <button type="submit" 
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition duration-200">
                        <i class="fas fa-save ml-1"></i>
                        حفظ المستأجر
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        document.getElementById('tenantForm').addEventListener('submit', function(e) {
            let isValid = true;
            
            // مسح الأخطاء السابقة
            document.querySelectorAll('[id$="-error"]').forEach(el => el.textContent = '');
            
            // التحقق من الاسم الكامل
            const fullName = document.getElementById('full_name').value.trim();
            if (!fullName) {
                document.getElementById('full_name-error').textContent = 'الاسم الكامل مطلوب';
                isValid = false;
            }
            
            // التحقق من رقم الهاتف
            const phone = document.getElementById('phone').value.trim();
            if (!phone) {
                document.getElementById('phone-error').textContent = 'رقم الهاتف مطلوب';
                isValid = false;
            }
            
            // التحقق من البريد الإلكتروني (إذا تم إدخاله)
            const email = document.getElementById('email').value.trim();
            if (email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    document.getElementById('email-error').textContent = 'البريد الإلكتروني غير صحيح';
                    isValid = false;
                }
            }
            
            // التحقق من رقم الهوية
            const cin = document.getElementById('cin').value.trim();
            if (!cin) {
                document.getElementById('cin-error').textContent = 'رقم الهوية مطلوب';
                isValid = false;
            }
            
            // التحقق من نوع السكن
            const houseType = document.getElementById('house_type').value;
            if (!houseType) {
                document.getElementById('house_type-error').textContent = 'نوع السكن مطلوب';
                isValid = false;
            }
            
            // التحقق من تاريخ البداية
            const startDate = document.getElementById('start_date').value;
            if (!startDate) {
                document.getElementById('start_date-error').textContent = 'تاريخ بداية الإيجار مطلوب';
                isValid = false;
            }
            
            // التحقق من تاريخ النهاية
            const endDate = document.getElementById('end_date').value;
            if (!endDate) {
                document.getElementById('end_date-error').textContent = 'تاريخ نهاية الإيجار مطلوب';
                isValid = false;
            }
            
            // التحقق من أن تاريخ النهاية بعد تاريخ البداية
            if (startDate && endDate) {
                if (new Date(endDate) <= new Date(startDate)) {
                    document.getElementById('end_date-error').textContent = 'تاريخ النهاية يجب أن يكون بعد تاريخ البداية';
                    isValid = false;
                }
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });

        // تحديث تاريخ النهاية تلقائياً عند تغيير تاريخ البداية (اختياري)
        document.getElementById('start_date').addEventListener('change', function() {
            const startDate = new Date(this.value);
            if (startDate) {
                // إضافة سنة واحدة كافتراضي
                const endDate = new Date(startDate);
                endDate.setFullYear(endDate.getFullYear() + 1);
                
                const endDateInput = document.getElementById('end_date');
                if (!endDateInput.value) {
                    endDateInput.value = endDate.toISOString().split('T')[0];
                }
            }
        });
    </script>
</body>
</html>
