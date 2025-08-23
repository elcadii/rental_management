<?php
require_once 'config/db.php';
require_once 'includes/currency_manager.php';
require_once 'includes/i18n.php';
requireLogin();

$tenant_id = (int)($_GET['id'] ?? 0);
$errors = [];
$success = '';

// Check if tenant exists and belongs to the current admin
$stmt = $pdo->prepare("SELECT * FROM tenants WHERE id = ? AND admin_id = ?");
$stmt->execute([$tenant_id, $_SESSION['admin_id']]);
$tenant = $stmt->fetch();

if (!$tenant) {
    die(__('errors.tenant_not_found'));
}

// Fetch housing types for this admin
$stmt = $pdo->prepare('SELECT * FROM housing_types WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$_SESSION['admin_id']]);
$admin_housing_types = $stmt->fetchAll();

$marital_status = $tenant['marital_status'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $cin = sanitize($_POST['cin'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $house_type = sanitize($_POST['house_type'] ?? '');
    $marital_status = sanitize($_POST['marital_status'] ?? '');
    $start_date = sanitize($_POST['start_date'] ?? '');
    $end_date = sanitize($_POST['end_date'] ?? '');
    $price_per_day = isset($_POST['pricePerDay']) ? floatval($_POST['pricePerDay']) : '';
    
    // Handle marriage contract upload
    $marriage_contract_path = $tenant['marriage_contract'] ?? null; // Keep existing if no new upload
    if ($marital_status === 'Married' && isset($_FILES['marriage_contract']) && $_FILES['marriage_contract']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/marriage_contracts/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['marriage_contract']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            // تحقق من حجم الملف (5MB)
            if ($_FILES['marriage_contract']['size'] > 5 * 1024 * 1024) {
                $errors['marriage_contract'] = __('errors.file_size_limit');
            } else {
                $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['marriage_contract']['tmp_name'], $upload_path)) {
                    // Delete old file if exists
                    if ($marriage_contract_path && file_exists($marriage_contract_path)) {
                        unlink($marriage_contract_path);
                    }
                    $marriage_contract_path = $upload_path;
                } else {
                    $errors['marriage_contract'] = __('errors.upload_failed');
                }
            }
        } else {
            $errors['marriage_contract'] = __('errors.invalid_file_type');
        }
    } elseif ($marital_status === 'Married' && empty($marriage_contract_path)) {
        // If married but no file uploaded and no existing file, show error
        $errors['marriage_contract'] = __('errors.marriage_contract_required');
    }
    
    // Data validation
    if (empty($full_name)) {
        $errors['full_name'] = __('errors.tenant_name_required');
    }
    
    if (empty($phone)) {
        $errors['phone'] = __('errors.tenant_phone_required');
    }
    
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = __('errors.invalid_email');
    }
    
    if (empty($cin)) {
        $errors['cin'] = __('errors.tenant_cin_required');
    }
    
    if (empty($address)) {
        $errors['address'] = __('errors.tenant_address_required');
    }
    
    if (empty($house_type)) {
        $errors['house_type'] = __('errors.tenant_house_type_required');
    }
    
    if (empty($marital_status)) {
        $errors['marital_status'] = __('errors.tenant_marital_status_required');
    }
    
    if (empty($start_date)) {
        $errors['start_date'] = __('errors.tenant_start_date_required');
    }
    
    if (empty($end_date)) {
        $errors['end_date'] = __('errors.tenant_end_date_required');
    }
    
    if ($price_per_day === '' || $price_per_day <= 0) {
        $errors['pricePerDay'] = __('errors.tenant_price_per_day_required');
    }
    
    // Check that end date is after start date
    if (!empty($start_date) && !empty($end_date)) {
        if (strtotime($end_date) <= strtotime($start_date)) {
            $errors['end_date'] = __('errors.tenant_end_date_after_start');
        }
    }
    
    // Calculate total rent (exclusive)
    $total_rent = null;
    if (!empty($start_date) && !empty($end_date) && $price_per_day > 0) {
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $days = $start->diff($end)->days; // exclusive
        if ($days > 0) {
            $total_rent = $days * $price_per_day;
        }
    }
    
    // Update tenant
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE tenants 
            SET full_name = ?, phone = ?, email = ?, cin = ?, address = ?, house_type = ?, marital_status = ?, start_date = ?, end_date = ?, price_per_day = ?, total_rent = ?, marriage_contract = ?
            WHERE id = ? AND admin_id = ?
        ");
        
        if ($stmt->execute([$full_name, $phone, $email ?: null, $cin, $address, $house_type, $marital_status, $start_date, $end_date, $price_per_day, $total_rent, $marriage_contract_path, $tenant_id, $_SESSION['admin_id']])) {
            $success = __('success.tenant_updated');
            // Update displayed data
            $tenant['full_name'] = $full_name;
            $tenant['phone'] = $phone;
            $tenant['email'] = $email;
            $tenant['cin'] = $cin;
            $tenant['address'] = $address;
            $tenant['house_type'] = $house_type;
            $tenant['marital_status'] = $marital_status;
            $tenant['start_date'] = $start_date;
            $tenant['end_date'] = $end_date;
            $tenant['price_per_day'] = $price_per_day;
            $tenant['total_rent'] = $total_rent;
            $tenant['marriage_contract'] = $marriage_contract_path;
        } else {
            $errors['general'] = __('errors.tenant_update_failed');
        }
    }
}
?>

<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(currentLang()); ?>" dir="<?php echo htmlspecialchars(currentDir()); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('edit_tenant.title'); ?> - <?php echo __('app.title'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Tajawal', Arial, sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    </style>
</head>
<body class="bg-gray-50" dir="<?php echo htmlspecialchars(currentDir()); ?>">

    <!-- القسم الرئيسي -->
    <div class="gradient-bg py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl font-bold text-white mb-2"><?php echo __('edit_tenant.hero_title'); ?></h1>
            <p class="text-blue-100"><?php echo __('edit_tenant.hero_sub'); ?>: <?php echo htmlspecialchars($tenant['full_name']); ?></p>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <div class="flex flex-col sm:flex-row items-center gap-2">
                    <i class="fas fa-user-edit text-blue-600 text-xl ml-3"></i>
                    <h3 class="text-lg font-bold text-gray-900"><?php echo __('edit_tenant.form_title'); ?></h3>
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
            
            <?php if (isset($errors['marriage_contract'])): ?>
                <div class="mx-6 mt-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle ml-2"></i>
                        <?php echo $errors['marriage_contract']; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="editTenantForm" class="p-6" enctype="multipart/form-data">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- الاسم الكامل -->
                    <div class="sm:col-span-2">
                        <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user ml-1"></i>
                            <?php echo __('edit_tenant.full_name'); ?>
                        </label>
                        <input type="text" name="full_name" id="full_name" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                               value="<?php echo htmlspecialchars($tenant['full_name']); ?>">
                        <div id="full_name-error" class="text-red-500 text-sm mt-1">
                            <?php echo $errors['full_name'] ?? ''; ?>
                        </div>
                    </div>
                    
                    <!-- رقم الهاتف -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-phone ml-1"></i>
                            <?php echo __('edit_tenant.phone'); ?>
                        </label>
                        <input type="tel" name="phone" id="phone" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                               value="<?php echo htmlspecialchars($tenant['phone']); ?>">
                        <div id="phone-error" class="text-red-500 text-sm mt-1">
                            <?php echo $errors['phone'] ?? ''; ?>
                        </div>
                    </div>
                    
                    <!-- البريد الإلكتروني -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-envelope ml-1"></i>
                            <?php echo __('edit_tenant.email'); ?> <span class="text-gray-500">(<?php echo __('edit_tenant.optional'); ?>)</span>
                        </label>
                        <input type="email" name="email" id="email"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                               value="<?php echo htmlspecialchars($tenant['email'] ?? ''); ?>">
                        <div id="email-error" class="text-red-500 text-sm mt-1">
                            <?php echo $errors['email'] ?? ''; ?>
                        </div>
                    </div>
                    
                    <!-- رقم الهوية -->
                    <div>
                        <label for="cin" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-id-card ml-1"></i>
                            <?php echo __('edit_tenant.cin'); ?>
                        </label>
                        <input type="text" name="cin" id="cin" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                               value="<?php echo htmlspecialchars($tenant['cin']); ?>">
                        <div id="cin-error" class="text-red-500 text-sm mt-1">
                            <?php echo $errors['cin'] ?? ''; ?>
                        </div>
                    </div>
                    
                    <!-- العنوان -->
                    <div class="sm:col-span-2">
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-map-marker-alt ml-1"></i>
                            <?php echo __('edit_tenant.address'); ?>
                        </label>
                        <textarea name="address" id="address" required rows="3"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                  placeholder="<?php echo __('edit_tenant.placeholder_address'); ?>"
                                  ><?php echo htmlspecialchars($tenant['address'] ?? ''); ?></textarea>
                        <div id="address-error" class="text-red-500 text-sm mt-1">
                            <?php echo $errors['address'] ?? ''; ?>
                        </div>
                    </div>
                    
                    <!-- نوع السكن -->
                    <div>
                        <label for="house_type" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-building ml-1"></i>
                            <?php echo __('edit_tenant.house_type'); ?>
                        </label>
                        <select name="house_type" id="house_type" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                            <option value=""><?php echo __('edit_tenant.select_house_type'); ?></option>
                            <?php foreach ($admin_housing_types as $type): ?>
                                <option value="<?php echo htmlspecialchars($type['name']); ?>" <?php echo ($tenant['house_type'] ?? '') === $type['name'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($type['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div id="house_type-error" class="text-red-500 text-sm mt-1">
                            <?php echo $errors['house_type'] ?? ''; ?>
                        </div>
                    </div>
                    
                    <!-- الحالة الاجتماعية -->
                    <div>
                        <label for="marital_status" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-ring ml-1"></i>
                            <?php echo __('edit_tenant.marital_status'); ?>
                        </label>
                        <select name="marital_status" id="marital_status" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                            <option value=""><?php echo __('edit_tenant.select_marital_status'); ?></option>
                            <option value="Single" <?php echo ($marital_status ?? '') === 'Single' ? 'selected' : ''; ?>><?php echo __('edit_tenant.single'); ?></option>
                            <option value="Married" <?php echo ($marital_status ?? '') === 'Married' ? 'selected' : ''; ?>><?php echo __('edit_tenant.married'); ?></option>
                            <option value="Family" <?php echo ($marital_status ?? '') === 'Family' ? 'selected' : ''; ?>><?php echo __('edit_tenant.family'); ?></option>
                        </select>
                        <div id="marital_status-error" class="text-red-500 text-sm mt-1">
                            <?php echo $errors['marital_status'] ?? ''; ?>
                        </div>
                    </div>
                    
                    <!-- حقل رفع عقد الزواج (مخفي افتراضياً) -->
                    <div id="marriage-upload-wrapper" class="sm:col-span-2 hidden" aria-hidden="true">
                        <label for="marriage_contract" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-file-upload ml-1"></i>
                            <?php echo __('edit_tenant.marriage_contract'); ?>
                        </label>
                        <input
                            type="file"
                            name="marriage_contract"
                            id="marriage_contract"
                            accept=".jpg,.jpeg,.png,.pdf,application/pdf"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                        />
                        <p class="text-xs text-gray-500 mt-1"><?php echo __('edit_tenant.allowed_files'); ?></p>
                        <div id="marriage_contract-error" class="text-red-500 text-sm mt-1">
                            <?php echo $errors['marriage_contract'] ?? ''; ?>
                        </div>
                        
                        <!-- عرض العقد الحالي إن وجد -->
                        <?php if (!empty($tenant['marriage_contract']) && file_exists($tenant['marriage_contract'])): ?>
                            <div class="mt-2 p-3 bg-green-50 border border-green-200 rounded-lg">
                                <div class="flex items-center gap-2 text-green-700">
                                    <i class="fas fa-file-check"></i>
                                    <span class="text-sm font-medium"><?php echo __('edit_tenant.current_contract'); ?>:</span>
                                    <a href="<?php echo htmlspecialchars($tenant['marriage_contract']); ?>" 
                                       target="_blank" 
                                       class="text-blue-600 hover:text-blue-800 underline">
                                        <?php echo __('edit_tenant.view_contract'); ?>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- تاريخ البداية -->
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-alt ml-1"></i>
                            <?php echo __('edit_tenant.start_date'); ?>
                        </label>
                        <input type="date" name="start_date" id="start_date" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                               value="<?php echo htmlspecialchars($tenant['start_date']); ?>">
                        <div id="start_date-error" class="text-red-500 text-sm mt-1">
                            <?php echo $errors['start_date'] ?? ''; ?>
                        </div>
                    </div>
                    
                    <!-- تاريخ النهاية -->
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-check ml-1"></i>
                            <?php echo __('edit_tenant.end_date'); ?>
                        </label>
                        <input type="date" name="end_date" id="end_date" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                               value="<?php echo htmlspecialchars($tenant['end_date']); ?>">
                        <div id="end_date-error" class="text-red-500 text-sm mt-1">
                            <?php echo $errors['end_date'] ?? ''; ?>
                        </div>
                    </div>
                    
                    <!-- سعر الإيجار اليومي -->
                    <div>
                        <label for="pricePerDay" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-money-bill-wave ml-1"></i>
                            <?php echo __('edit_tenant.price_per_day'); ?> (<?php echo getCurrentCurrency()['symbol']; ?> <?php echo getCurrentCurrency()['code']; ?>)
                        </label>
                        <input type="number" name="pricePerDay" id="pricePerDay" min="1" step="0.01" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                               placeholder="<?php echo __('edit_tenant.placeholder_price_per_day'); ?>"
                               value="<?php echo htmlspecialchars($tenant['price_per_day'] ?? ''); ?>">
                        <div id="pricePerDay-error" class="text-red-500 text-sm mt-1">
                            <?php echo $errors['pricePerDay'] ?? ''; ?>
                        </div>
                    </div>
                </div>
                
                <!-- معلومات إضافية -->
                <div class="mt-6 p-4 bg-yellow-50 rounded-lg">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-info-circle text-yellow-600 ml-2"></i>
                        <h4 class="font-medium text-yellow-900"><?php echo __('edit_tenant.important_info'); ?></h4>
                    </div>
                    <ul class="text-sm text-yellow-800 space-y-1">
                        <li>• <?php echo __('edit_tenant.info_verify_data'); ?></li>
                        <li>• <?php echo __('edit_tenant.info_update_immediately'); ?></li>
                        <li>• <?php echo __('edit_tenant.info_cancel_edit'); ?></li>
                    </ul>
                </div>
                
                <!-- إجمالي الإيجار (يتم تحديثه تلقائياً) -->
                <div id="total-rent-info" class="mt-4 text-blue-800 font-bold text-lg flex items-center gap-2">
                    <i class="fas fa-calculator"></i>
                    <span id="days-count"></span>
                    <span id="total-rent"></span>
                </div>
                
                <!-- أزرار الإجراءات -->
                <div class="mt-8 flex gap-2 justify-end space-x-4 space-x-reverse">
                    <a href="dashboard.php" 
                       class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition duration-200">
                        <i class="fas fa-times ml-1"></i>
                        <?php echo __('edit_tenant.cancel'); ?>
                    </a>
                    <button type="submit" 
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition duration-200">
                        <i class="fas fa-save ml-1"></i>
                        <?php echo __('edit_tenant.save_changes'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // إظهار/إخفاء حقل رفع عقد الزواج بناءً على اختيار الحالة الاجتماعية
        (function setupMarriageContractToggle() {
            const select = document.getElementById('marital_status');
            const wrapper = document.getElementById('marriage-upload-wrapper');
            const input = document.getElementById('marriage_contract');

            if (!select || !wrapper || !input) return;

            function applyToggle() {
                if (select.value === 'Married') {
                    wrapper.classList.remove('hidden');
                    wrapper.setAttribute('aria-hidden', 'false');
                    input.required = true;
                } else {
                    wrapper.classList.add('hidden');
                    wrapper.setAttribute('aria-hidden', 'true');
                    input.required = false;
                    input.value = '';
                }
            }

            select.addEventListener('change', applyToggle);
            document.addEventListener('DOMContentLoaded', applyToggle);
            applyToggle();
        })();

        document.getElementById('editTenantForm').addEventListener('submit', function(e) {
            let isValid = true;
            
            // مسح الأخطاء السابقة
            document.querySelectorAll('[id$="-error"]').forEach(el => el.textContent = '');
            
            // التحقق من الاسم الكامل
            const fullName = document.getElementById('full_name').value.trim();
            if (!fullName) {
                document.getElementById('full_name-error').textContent = '<?php echo __('errors.tenant_name_required'); ?>';
                isValid = false;
            }
            
            // التحقق من رقم الهاتف
            const phone = document.getElementById('phone').value.trim();
            if (!phone) {
                document.getElementById('phone-error').textContent = '<?php echo __('errors.tenant_phone_required'); ?>';
                isValid = false;
            }
            
            // التحقق من البريد الإلكتروني (إذا تم إدخاله)
            const email = document.getElementById('email').value.trim();
            if (email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    document.getElementById('email-error').textContent = '<?php echo __('errors.invalid_email'); ?>';
                    isValid = false;
                }
            }
            
            // التحقق من رقم الهوية
            const cin = document.getElementById('cin').value.trim();
            if (!cin) {
                document.getElementById('cin-error').textContent = '<?php echo __('errors.tenant_cin_required'); ?>';
                isValid = false;
            }
            
            // التحقق من العنوان
            const address = document.getElementById('address').value.trim();
            if (!address) {
                document.getElementById('address-error').textContent = '<?php echo __('errors.tenant_address_required'); ?>';
                isValid = false;
            }
            
            // التحقق من نوع السكن
            const houseType = document.getElementById('house_type').value;
            if (!houseType) {
                document.getElementById('house_type-error').textContent = '<?php echo __('errors.tenant_house_type_required'); ?>';
                isValid = false;
            }
            
            // Validate marital status
            const maritalStatus = document.getElementById('marital_status').value;
            if (!maritalStatus) {
                document.getElementById('marital_status-error').textContent = '<?php echo __('errors.tenant_marital_status_required'); ?>';
                isValid = false;
            }
            
            // التحقق من تاريخ البداية
            const startDate = document.getElementById('start_date').value;
            if (!startDate) {
                document.getElementById('start_date-error').textContent = '<?php echo __('errors.tenant_start_date_required'); ?>';
                isValid = false;
            }
            
            // التحقق من تاريخ النهاية
            const endDate = document.getElementById('end_date').value;
            if (!endDate) {
                document.getElementById('end_date-error').textContent = '<?php echo __('errors.tenant_end_date_required'); ?>';
                isValid = false;
            }
            
            // التحقق من أن تاريخ النهاية بعد تاريخ البداية
            if (startDate && endDate) {
                if (new Date(endDate) <= new Date(startDate)) {
                    document.getElementById('end_date-error').textContent = '<?php echo __('errors.tenant_end_date_after_start'); ?>';
                    isValid = false;
                }
            }
            
            // التحقق من سعر الإيجار اليومي
            const pricePerDay = parseFloat(document.getElementById('pricePerDay').value);
            if (pricePerDay === 0 || isNaN(pricePerDay)) {
                document.getElementById('pricePerDay-error').textContent = '<?php echo __('errors.tenant_price_per_day_required'); ?>';
                isValid = false;
            }
            
            // التحقق من عقد الزواج للمتزوجين
            const marriageContract = document.getElementById('marriage_contract');
            const marriageContractError = document.getElementById('marriage_contract-error');

            if (maritalStatus === 'Married') {
                if (!marriageContract.files || marriageContract.files.length === 0) {
                    // تحقق من وجود عقد قديم
                    const currentContract = '<?php echo !empty($tenant['marriage_contract']) ? "exists" : ""; ?>';
                    if (!currentContract) {
                        marriageContractError.textContent = '<?php echo __('errors.marriage_contract_required'); ?>';
                        isValid = false;
                    }
                } else {
                    const file = marriageContract.files[0];
                    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
                    if (!allowedTypes.includes(file.type)) {
                        marriageContractError.textContent = '<?php echo __('errors.invalid_file_type'); ?>. <?php echo __('edit_tenant.allowed_files'); ?>';
                        isValid = false;
                    }
                    
                    // تحقق من حجم الملف (5MB كحد أقصى)
                    const maxSize = 5 * 1024 * 1024; // 5MB
                    if (file.size > maxSize) {
                        marriageContractError.textContent = '<?php echo __('errors.file_size_limit'); ?>';
                        isValid = false;
                    }
                }
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });

        function calculateTotalRent() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const pricePerDay = parseFloat(document.getElementById('pricePerDay').value);
            let days = 0;
            let total = 0;
            if (startDate && endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);
                days = Math.floor((end - start) / (1000 * 60 * 60 * 24)); // exclusive
                if (days > 0 && !isNaN(pricePerDay) && pricePerDay > 0) {
                    total = days * pricePerDay;
                }
            }
            document.getElementById('days-count').textContent = days > 0 ? `<?php echo __('edit_tenant.days_count'); ?>: ${days}` : '';
            document.getElementById('total-rent').textContent = (days > 0 && total > 0) ? `| <?php echo __('edit_tenant.total_rent'); ?>: <?php echo getCurrentCurrency()['symbol']; ?> ${total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}` : '';
        }
        document.getElementById('start_date').addEventListener('input', calculateTotalRent);
        document.getElementById('end_date').addEventListener('input', calculateTotalRent);
        document.getElementById('pricePerDay').addEventListener('input', calculateTotalRent);
        window.addEventListener('DOMContentLoaded', calculateTotalRent);
    </script>
</body>
</html>
