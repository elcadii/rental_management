<?php
require_once 'config/db.php';
require_once 'includes/currency_manager.php';
require_once 'includes/i18n.php';
requireLogin();

$errors = [];
$success = '';

// Fetch housing types for this admin
$stmt = $pdo->prepare('SELECT * FROM housing_types WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$_SESSION['admin_id']]);
$admin_housing_types = $stmt->fetchAll();

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
    $marriage_contract_path = null;
    if ($marital_status === 'Married' && isset($_FILES['marriage_contract']) && $_FILES['marriage_contract']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/marriage_contracts/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['marriage_contract']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['marriage_contract']['tmp_name'], $upload_path)) {
                $marriage_contract_path = $upload_path;
            } else {
                $errors['marriage_contract'] = __('errors.upload_failed');
            }
        } else {
            $errors['marriage_contract'] = __('errors.invalid_file_type');
        }
    } elseif ($marital_status === 'Married') {
        // If married but no file uploaded, show error
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
    
    // Add tenant
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO tenants (full_name, phone, email, cin, address, house_type, marital_status, start_date, end_date, price_per_day, total_rent, admin_id, marriage_contract) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$full_name, $phone, $email ?: null, $cin, $address, $house_type, $marital_status, $start_date, $end_date, $price_per_day, $total_rent, $_SESSION['admin_id'], $marriage_contract_path])) {
            $success = __('success.tenant_added');
            // Clear data after successful addition
            $full_name = $phone = $email = $cin = $address = $house_type = $marital_status = $start_date = $end_date = $price_per_day = '';
        } else {
            $errors['general'] = __('errors.tenant_add_failed');
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
    <title><?php echo __('add_tenant.page_title'); ?> - <?php echo __('app.title'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Tajawal', Arial, sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    </style>
</head>
<body class="bg-gray-50" dir="<?php echo htmlspecialchars(currentDir()); ?>">

    <!-- Main Section -->
    <div class="gradient-bg py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl font-bold text-white mb-2"><?php echo __('add_tenant.hero_title'); ?></h1>
            <p class="text-blue-100"><?php echo __('add_tenant.hero_sub'); ?></p>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <div class="flex flex-col sm:flex-row items-center gap-2">
                    <i class="fas fa-user-plus text-blue-600 text-xl ml-3"></i>
                    <h3 class="text-lg font-bold text-gray-900"><?php echo __('add_tenant.form_title'); ?></h3>
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
            
            <form method="POST" id="tenantForm" class="p-6" enctype="multipart/form-data">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- الاسم الكامل -->
                    <div class="sm:col-span-2">
                        <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user ml-1"></i>
                            <?php echo __('add_tenant.full_name'); ?>
                        </label>
                        <input type="text" name="full_name" id="full_name" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                               placeholder="<?php echo __('add_tenant.placeholder_full_name'); ?>"
                               value="<?php echo htmlspecialchars($full_name ?? ''); ?>">
                        <div id="full_name-error" class="text-red-500 text-sm mt-1">
                            <?php echo $errors['full_name'] ?? ''; ?>
                        </div>
                    </div>
                    
                    <!-- رقم الهاتف -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-phone ml-1"></i>
                            <?php echo __('add_tenant.phone'); ?>
                        </label>
                        <input type="tel" name="phone" id="phone" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                               placeholder="<?php echo __('add_tenant.placeholder_phone'); ?>"
                               value="<?php echo htmlspecialchars($phone ?? ''); ?>">
                        <div id="phone-error" class="text-red-500 text-sm mt-1">
                            <?php echo $errors['phone'] ?? ''; ?>
                        </div>
                    </div>
                    
                    <!-- البريد الإلكتروني -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-envelope ml-1"></i>
                            <?php echo __('add_tenant.email'); ?> <span class="text-gray-500">(<?php echo __('add_tenant.optional'); ?>)</span>
                        </label>
                        <input type="email" name="email" id="email"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                               placeholder="<?php echo __('add_tenant.placeholder_email'); ?>"
                               value="<?php echo htmlspecialchars($email ?? ''); ?>">
                        <div id="email-error" class="text-red-500 text-sm mt-1">
                            <?php echo $errors['email'] ?? ''; ?>
                        </div>
                    </div>
                    
                    <!-- رقم الهوية -->
                    <div>
                        <label for="cin" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-id-card ml-1"></i>
                            <?php echo __('add_tenant.cin'); ?>
                        </label>
                        <input type="text" name="cin" id="cin" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                               placeholder="<?php echo __('add_tenant.placeholder_cin'); ?>"
                               value="<?php echo htmlspecialchars($cin ?? ''); ?>">
                        <div id="cin-error" class="text-red-500 text-sm mt-1">
                            <?php echo $errors['cin'] ?? ''; ?>
                        </div>
                    </div>
                    
                    <!-- العنوان -->
                    <div class="sm:col-span-2">
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-map-marker-alt ml-1"></i>
                            <?php echo __('add_tenant.address'); ?>
                        </label>
                        <textarea name="address" id="address" required rows="3"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                  placeholder="<?php echo __('add_tenant.placeholder_address'); ?>"
                                  ><?php echo htmlspecialchars($address ?? ''); ?></textarea>
                        <div id="address-error" class="text-red-500 text-sm mt-1">
                            <?php echo $errors['address'] ?? ''; ?>
                        </div>
                    </div>
                    
                    <!-- نوع السكن -->
                    <div>
                        <label for="house_type" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-building ml-1"></i>
                            <?php echo __('add_tenant.house_type'); ?>
                        </label>
                        <?php if (empty($admin_housing_types)): ?>
                            <div class="text-red-500 text-sm mb-2"><?php echo __('add_tenant.no_housing_types'); ?></div>
                        <?php endif; ?>
                        <select name="house_type" id="house_type" required <?php echo empty($admin_housing_types) ? 'disabled' : ''; ?>
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                            <option value=""><?php echo __('add_tenant.select_house_type'); ?></option>
                            <?php foreach ($admin_housing_types as $type): ?>
                                <option value="<?php echo htmlspecialchars($type['name']); ?>" <?php echo ($house_type ?? '') === $type['name'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($type['name']); ?></option>
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
                            <?php echo __('add_tenant.marital_status'); ?>
                        </label>
                        <select name="marital_status" id="marital_status" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                            <option value=""><?php echo __('add_tenant.select_marital_status'); ?></option>
                            <option value="Single" <?php echo ($marital_status ?? '') === 'Single' ? 'selected' : ''; ?>><?php echo __('add_tenant.single'); ?></option>
                            <option value="Married" <?php echo ($marital_status ?? '') === 'Married' ? 'selected' : ''; ?>><?php echo __('add_tenant.married'); ?></option>
                            <option value="Family" <?php echo ($marital_status ?? '') === 'Family' ? 'selected' : ''; ?>><?php echo __('add_tenant.family'); ?></option>
                        </select>
                        <div id="marital_status-error" class="text-red-500 text-sm mt-1">
                            <?php echo $errors['marital_status'] ?? ''; ?>
                        </div>
                    </div>
                    
                    <!-- Marriage contract upload field (hidden by default) -->
                    <div id="marriage-upload-wrapper" class="sm:col-span-2 hidden" aria-hidden="true">
                        <label for="marriage_contract" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-file-upload ml-1"></i>
                            <?php echo __('add_tenant.marriage_contract'); ?>
                        </label>
                        <input
                            type="file"
                            name="marriage_contract"
                            id="marriage_contract"
                            accept=".jpg,.jpeg,.png,.pdf,application/pdf"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                        />
                        <p class="text-xs text-gray-500 mt-1"><?php echo __('add_tenant.allowed_files'); ?></p>
                        <div id="marriage_contract-error" class="text-red-500 text-sm mt-1"></div>
                    </div>
                    
                    <!-- تاريخ البداية -->
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-alt ml-1"></i>
                            <?php echo __('add_tenant.start_date'); ?>
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
                            <?php echo __('add_tenant.end_date'); ?>
                        </label>
                        <input type="date" name="end_date" id="end_date" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                               value="<?php echo htmlspecialchars($end_date ?? ''); ?>">
                        <div id="end_date-error" class="text-red-500 text-sm mt-1">
                            <?php echo $errors['end_date'] ?? ''; ?>
                        </div>
                    </div>
                    
                    <!-- سعر الإيجار اليومي -->
                    <div>
                        <label for="pricePerDay" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-money-bill-wave ml-1"></i>
                            <?php echo __('add_tenant.price_per_day'); ?> (<?php echo getCurrentCurrency()['symbol']; ?> <?php echo getCurrentCurrency()['code']; ?>)
                        </label>
                        <input type="number" name="pricePerDay" id="pricePerDay" min="1" step="0.01" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                               placeholder="<?php echo __('add_tenant.placeholder_price_per_day'); ?>"
                               value="<?php echo htmlspecialchars($price_per_day ?? ''); ?>">
                        <div id="pricePerDay-error" class="text-red-500 text-sm mt-1">
                            <?php echo $errors['pricePerDay'] ?? ''; ?>
                        </div>
                    </div>
                    <!-- Total rent (updated automatically) -->
                    <div id="total-rent-info" class="mt-4 text-blue-800 font-bold text-lg flex items-center gap-2">
                        <i class="fas fa-calculator"></i>
                        <span id="days-count"></span>
                        <span id="total-rent"></span>
                    </div>
                </div>
                
                <!-- Additional information -->
                <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-info-circle text-blue-600 ml-2"></i>
                        <h4 class="font-medium text-blue-900"><?php echo __('add_tenant.important_info'); ?></h4>
                    </div>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>• <?php echo __('add_tenant.info_save_automatically'); ?></li>
                        <li>• <?php echo __('add_tenant.info_verify_data'); ?></li>
                        <li>• <?php echo __('add_tenant.info_edit_later'); ?></li>
                    </ul>
                </div>
                
                <!-- Action buttons -->
                <div class="mt-8 flex gap-2 justify-end space-x-4 space-x-reverse">
                    <a href="dashboard.php" 
                       class="px-6 py-3  border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition duration-200">
                        <i class="fas fa-times ml-1"></i>
                        <?php echo __('add_tenant.cancel'); ?>
                    </a>
                    <button type="submit" 
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition duration-200">
                        <i class="fas fa-save ml-1"></i>
                        <?php echo __('add_tenant.save'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // إظهار/إخفاء حقل رفع عقد الزواج بناءً على اختيار الحالة الاجتماعية
        function setupMarriageContractToggle() {
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
            // تأكد من الحالة عند تحميل الصفحة
            document.addEventListener('DOMContentLoaded', applyToggle);
            applyToggle();
        }

        // تشغيل الوظيفة عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', setupMarriageContractToggle);

        document.getElementById('tenantForm').addEventListener('submit', function(e) {
            let isValid = true;
            
            // Clear previous errors
            document.querySelectorAll('[id$="-error"]').forEach(el => el.textContent = '');
            
            // Validate full name
            const fullName = document.getElementById('full_name').value.trim();
            if (!fullName) {
                document.getElementById('full_name-error').textContent = '<?php echo __('errors.tenant_name_required'); ?>';
                isValid = false;
            }
            
            // Validate phone number
            const phone = document.getElementById('phone').value.trim();
            if (!phone) {
                document.getElementById('phone-error').textContent = '<?php echo __('errors.tenant_phone_required'); ?>';
                isValid = false;
            }
            
            // Validate email (if entered)
            const email = document.getElementById('email').value.trim();
            if (email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    document.getElementById('email-error').textContent = '<?php echo __('errors.invalid_email'); ?>';
                    isValid = false;
                }
            }
            
            // Validate CIN
            const cin = document.getElementById('cin').value.trim();
            if (!cin) {
                document.getElementById('cin-error').textContent = '<?php echo __('errors.tenant_cin_required'); ?>';
                isValid = false;
            }
            
            // Validate address
            const address = document.getElementById('address').value.trim();
            if (!address) {
                document.getElementById('address-error').textContent = '<?php echo __('errors.tenant_address_required'); ?>';
                isValid = false;
            }
            
            // Validate house type
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
            
            // Validate start date
            const startDate = document.getElementById('start_date').value;
            if (!startDate) {
                document.getElementById('start_date-error').textContent = '<?php echo __('errors.tenant_start_date_required'); ?>';
                isValid = false;
            }
            
            // Validate end date
            const endDate = document.getElementById('end_date').value;
            if (!endDate) {
                document.getElementById('end_date-error').textContent = '<?php echo __('errors.tenant_end_date_required'); ?>';
                isValid = false;
            }
            
            // Check that end date is after start date
            if (startDate && endDate) {
                if (new Date(endDate) <= new Date(startDate)) {
                    document.getElementById('end_date-error').textContent = '<?php echo __('errors.tenant_end_date_after_start'); ?>';
                    isValid = false;
                }
            }
            
            // Validate marriage contract for married tenants
            const marriageContract = document.getElementById('marriage_contract');
            const marriageContractError = document.getElementById('marriage_contract-error');

            if (maritalStatus === 'Married') {
                if (!marriageContract.files || marriageContract.files.length === 0) {
                    marriageContractError.textContent = '<?php echo __('errors.marriage_contract_required'); ?>';
                    isValid = false;
                } else {
                    const file = marriageContract.files[0];
                    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
                    if (!allowedTypes.includes(file.type)) {
                        marriageContractError.textContent = '<?php echo __('errors.invalid_file_type'); ?>';
                        isValid = false;
                    }
                }
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });

        // Automatically update end date when start date changes (optional)
        document.getElementById('start_date').addEventListener('change', function() {
            const startDate = new Date(this.value);
            if (startDate) {
                // Add one year by default
                const endDate = new Date(startDate);
                endDate.setFullYear(endDate.getFullYear() + 1);
                
                const endDateInput = document.getElementById('end_date');
                if (!endDateInput.value) {
                    endDateInput.value = endDate.toISOString().split('T')[0];
                }
            }
        });

        function calculateTotalRent() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const pricePerDay = parseFloat(document.getElementById('pricePerDay').value);
            let days = 0;
            let total = 0;
            if (startDate && endDate) {
                const start = new DateTime(startDate);
                const end = new DateTime(endDate);
                days = Math.floor((end - start) / (1000 * 60 * 60 * 24)); // exclusive
                if (days > 0 && !isNaN(pricePerDay) && pricePerDay > 0) {
                    total = days * pricePerDay;
                }
            }
            document.getElementById('days-count').textContent = days > 0 ? `<?php echo __('add_tenant.days_count'); ?>: ${days}` : '';
            document.getElementById('total-rent').textContent = (days > 0 && total > 0) ? `| <?php echo __('add_tenant.total_rent'); ?>: <?php echo getCurrentCurrency()['symbol']; ?> ${total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}` : '';
        }
        document.getElementById('start_date').addEventListener('input', calculateTotalRent);
        document.getElementById('end_date').addEventListener('input', calculateTotalRent);
        document.getElementById('pricePerDay').addEventListener('input', calculateTotalRent);
        window.addEventListener('DOMContentLoaded', calculateTotalRent);
    </script>
</body>
</html>
