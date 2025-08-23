<?php
require_once 'config/db.php';
require_once 'includes/currency_manager.php';
requireLogin();

$admin_id = $_SESSION['admin_id'];

// Fetch admin info (including created_at)
$stmt = $pdo->prepare("SELECT name, email, phone, created_at FROM admins WHERE id = ?");
$stmt->execute([$admin_id]);
$user = $stmt->fetch();
if (!$user) die(__('errors.user_not_found'));
$name = $user['name'];
$email = $user['email'];
$phone = $user['phone'];
$created_at = $user['created_at'];

// Fetch statistics
// 1. Number of tenants
$stmt = $pdo->prepare("SELECT COUNT(*) FROM tenants WHERE admin_id = ?");
$stmt->execute([$admin_id]);
$total_tenants = $stmt->fetchColumn();
// 2. Number of housing types
$stmt = $pdo->prepare("SELECT COUNT(*) FROM housing_types WHERE user_id = ?");
$stmt->execute([$admin_id]);
$total_housing_types = $stmt->fetchColumn();
// 3. Active contracts (tenants with end_date >= today)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM tenants WHERE admin_id = ? AND end_date >= CURDATE()");
$stmt->execute([$admin_id]);
$active_contracts = $stmt->fetchColumn();
// 4. Expired contracts (tenants with end_date < today)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM tenants WHERE admin_id = ? AND end_date < CURDATE()");
$stmt->execute([$admin_id]);
$expired_contracts = $stmt->fetchColumn();
// 5. Total rent
$stmt = $pdo->prepare("SELECT SUM(total_rent) FROM tenants WHERE admin_id = ?");
$stmt->execute([$admin_id]);
$total_rent_mad = $stmt->fetchColumn() ?: 0;
$total_rent = convertCurrency($total_rent_mad);

// Additional financial statistics
// Monthly revenue for the last 6 months
$monthly_revenue = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $stmt = $pdo->prepare("SELECT SUM(total_rent) FROM tenants WHERE admin_id = ? AND DATE_FORMAT(created_at, '%Y-%m') = ?");
    $stmt->execute([$admin_id, $month]);
    $monthly_revenue_mad = $stmt->fetchColumn() ?: 0;
    $monthly_revenue[$month] = convertCurrency($monthly_revenue_mad);
}

// Revenue by housing type
$stmt = $pdo->prepare("
    SELECT ht.name, SUM(t.total_rent) as total_revenue, COUNT(t.id) as tenant_count
    FROM tenants t
    JOIN housing_types ht ON t.house_type = ht.name
    WHERE t.admin_id = ? AND ht.user_id = ?
    GROUP BY ht.name
    ORDER BY total_revenue DESC
");
$stmt->execute([$admin_id, $admin_id]);
$revenue_by_type = $stmt->fetchAll();

// Average rent per day
$stmt = $pdo->prepare("SELECT AVG(price_per_day) FROM tenants WHERE admin_id = ?");
$stmt->execute([$admin_id]);
$avg_daily_rent_mad = $stmt->fetchColumn() ?: 0;
$avg_daily_rent = convertCurrency($avg_daily_rent_mad);

// Occupancy rate
$stmt = $pdo->prepare("
    SELECT 
        COUNT(CASE WHEN end_date >= CURDATE() THEN 1 END) as occupied,
        COUNT(*) as total
    FROM tenants 
    WHERE admin_id = ?
");
$stmt->execute([$admin_id]);
$occupancy = $stmt->fetch();
$occupancy_rate = $occupancy['total'] > 0 ? round(($occupancy['occupied'] / $occupancy['total']) * 100, 1) : 0;

$current_currency = getCurrentCurrency();
?>

<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(currentLang()); ?>" dir="<?php echo htmlspecialchars(currentDir()); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('profile.page_title'); ?> - <?php echo __('app.title'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            font-family: 'Tajawal', Arial, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .stat-card {
            background: linear-gradient(135deg, #fff 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .profile-avatar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 20px 25px -5px rgba(102, 126, 234, 0.4);
        }
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body class="min-h-screen">
    <?php
    include 'header.php';
    include 'sidebar.php';
    ?>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Hero Section with Profile -->
        <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center w-32 h-32 profile-avatar rounded-full mb-6 shadow-2xl">
                <i class="fa-solid fa-user-shield text-white text-5xl"></i>
            </div>
            <h1 class="text-4xl sm:text-5xl font-bold text-white mb-4">
                <?php echo __('profile.greeting', ['name' => htmlspecialchars($name)]); ?>
            </h1>
            <p class="text-xl text-blue-100 max-w-2xl mx-auto">
                <?php echo __('profile.welcome_subtitle'); ?>
            </p>
        </div>

        <!-- Currency Selector -->
        <?php include 'components/currency_selector.php'; ?>

        <!-- Profile Information Card -->
        <div class="glass-effect rounded-3xl shadow-2xl p-8 mb-12">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                <!-- Profile Details -->
                <div class="space-y-6">
                    <h2 class="text-2xl font-bold gradient-text mb-6">
                        <i class="fas fa-user-circle ml-3"></i>
                        <?php echo __('profile.personal_info'); ?>
                    </h2>
                    
                    <div class="space-y-4">
                        <div class="flex items-center p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border-r-4 border-blue-500">
                            <div class="bg-blue-100 text-blue-600 rounded-full p-3 ml-4">
                                <i class="fas fa-envelope text-lg"></i>
                            </div>
                            <div>
                                <div class="text-sm text-blue-600 font-medium"><?php echo __('profile.email'); ?></div>
                                <div class="text-lg font-semibold text-gray-900">
                                    <?php echo $email ? htmlspecialchars($email) : '<span class="text-gray-400">' . __('profile.not_set') . '</span>'; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center p-4 bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl border-r-4 border-purple-500">
                            <div class="bg-purple-100 text-purple-600 rounded-full p-3 ml-4">
                                <i class="fas fa-phone text-lg"></i>
                            </div>
                            <div>
                                <div class="text-sm text-purple-600 font-medium"><?php echo __('profile.phone'); ?></div>
                                <div class="text-lg font-semibold text-gray-900">
                                    <?php echo $phone ? htmlspecialchars($phone) : '<span class="text-gray-400">' . __('profile.not_set') . '</span>'; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border-r-4 border-green-500">
                            <div class="bg-green-100 text-green-600 rounded-full p-3 ml-4">
                                <i class="fas fa-calendar-alt text-lg"></i>
                            </div>
                            <div>
                                <div class="text-sm text-green-600 font-medium"><?php echo __('profile.join_date'); ?></div>
                                <div class="text-lg font-semibold text-gray-900">
                                    <?php echo date('Y/m/d', strtotime($created_at)); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="space-y-4">
                    <div class="space-y-3">
                        <a href="edit_profile.php" 
                           class="w-full flex items-center justify-center p-4 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-xl font-semibold transition-all duration-300 transform hover:scale-105 shadow-lg">
                            <i class="fas fa-edit ml-3 text-lg"></i>
                            <?php echo __('profile.edit_profile'); ?>
                        </a>
                        <a href="change_password.php" 
                           class="w-full flex items-center justify-center p-4 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white rounded-xl font-semibold transition-all duration-300 transform hover:scale-105 shadow-lg">
                            <i class="fas fa-key ml-3 text-lg"></i>
                            <?php echo __('profile.change_password'); ?>
                        </a>
                    </div>
            </div>
            </div>
        </div>

        <!-- Statistics Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <div class="stat-card rounded-2xl p-6 text-center group">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-2xl p-4 mb-4 inline-block group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-users text-3xl"></i>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-2"><?php echo number_format($total_tenants); ?></div>
                <div class="text-gray-600 font-medium"><?php echo __('profile.stats_total_tenants'); ?></div>
                <div class="w-16 h-1 bg-blue-500 mx-auto mt-3 rounded-full"></div>
            </div>
            
            <div class="stat-card rounded-2xl p-6 text-center group">
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-2xl p-4 mb-4 inline-block group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-building text-3xl"></i>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-2"><?php echo number_format($total_housing_types); ?></div>
                <div class="text-gray-600 font-medium"><?php echo __('profile.stats_housing_types'); ?></div>
                <div class="w-16 h-1 bg-purple-500 mx-auto mt-3 rounded-full"></div>
            </div>
            
            <div class="stat-card rounded-2xl p-6 text-center group">
                <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-2xl p-4 mb-4 inline-block group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-file-contract text-3xl"></i>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-2"><?php echo number_format($active_contracts); ?></div>
                <div class="text-gray-600 font-medium"><?php echo __('profile.stats_active_contracts'); ?></div>
                <div class="w-16 h-1 bg-green-500 mx-auto mt-3 rounded-full"></div>
            </div>
            
            <div class="stat-card rounded-2xl p-6 text-center group">
                <div class="bg-gradient-to-br from-red-500 to-red-600 text-white rounded-2xl p-4 mb-4 inline-block group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-file-circle-xmark text-3xl"></i>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-2"><?php echo number_format($expired_contracts); ?></div>
                <div class="text-gray-600 font-medium"><?php echo __('profile.stats_expired_contracts'); ?></div>
                <div class="w-16 h-1 bg-red-500 mx-auto mt-3 rounded-full"></div>
            </div>
        </div>

        

        <!-- Comprehensive Financial Reports Section -->
        <div class="glass-effect rounded-3xl shadow-2xl p-8 mb-12">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold gradient-text mb-4">
                    <i class="fas fa-chart-line ml-3"></i>
                    <?php echo __('errors.financial_reports.title'); ?>
                </h2>
                <p class="text-gray-600 text-lg"><?php echo __('errors.financial_reports.subtitle'); ?></p>
            </div>

            <!-- Key Financial Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-gradient-to-r from-emerald-50 to-teal-50 rounded-2xl p-6 border border-emerald-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm text-emerald-600 font-medium"><?php echo __('profile.avg_daily_rent'); ?></div>
                            <div class="text-2xl font-bold text-emerald-800">
                                <?php echo number_format($avg_daily_rent, 2); ?>
                            </div>
                            <div class="text-xs text-emerald-600"><?php echo $current_currency['symbol']; ?><?php echo __('errors.financial_reports.per_day'); ?></div>
                        </div>
                        <div class="bg-emerald-100 text-emerald-600 rounded-full p-3">
                            <i class="fas fa-calculator text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm text-blue-600 font-medium"><?php echo __('profile.occupancy_rate'); ?></div>
                            <div class="text-2xl font-bold text-blue-800"><?php echo $occupancy_rate; ?>%</div>
                            <div class="text-xs text-blue-600"><?php echo __('profile.units_occupied'); ?></div>
                        </div>
                        <div class="bg-blue-100 text-blue-600 rounded-full p-3">
                            <i class="fas fa-percentage text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-2xl p-6 border border-purple-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm text-purple-600 font-medium"><?php echo __('profile.stats_total_tenants'); ?></div>
                            <div class="text-2xl font-bold text-purple-800"><?php echo number_format($total_tenants); ?></div>
                            <div class="text-xs text-purple-600"><?php echo __('profile.tenants_label'); ?></div>
                        </div>
                        <div class="bg-purple-100 text-purple-600 rounded-full p-3">
                            <i class="fas fa-users text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Revenue Chart -->
            <div class="bg-white rounded-2xl p-6 mb-8 border border-gray-200">
                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-chart-area text-blue-500 ml-2"></i>
                    <?php echo __('profile.monthly_revenue_title', ['symbol' => $current_currency['symbol'], 'code' => $current_currency['code']]); ?>
                </h3>
                <div class="grid grid-cols-6 gap-4">
                    <?php foreach ($monthly_revenue as $month => $revenue): ?>
                        <div class="text-center">
                            <div class="text-sm text-gray-600 mb-2"><?php echo date('M Y', strtotime($month . '-01')); ?></div>
                            <div class="bg-gradient-to-t from-blue-500 to-blue-600 rounded-lg p-3 text-white">
                                <div class="text-lg font-bold"><?php echo number_format($revenue, 0); ?></div>
                                <div class="text-xs opacity-90"><?php echo $current_currency['symbol']; ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Revenue by Housing Type -->
            <div class="bg-white rounded-2xl p-6 mb-8 border border-gray-200">
                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-building text-green-500 ml-2"></i>
                    <?php echo __('profile.revenue_by_type_title', ['symbol' => $current_currency['symbol'], 'code' => $current_currency['code']]); ?>
                </h3>
                <div class="space-y-4">
                    <?php foreach ($revenue_by_type as $type): ?>
                        <?php 
                        $converted_revenue = convertCurrency($type['total_revenue']);
                        ?>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                            <div class="flex items-center gap-3">
                                <div class="w-3 h-3 bg-gradient-to-r from-green-400 to-blue-500 rounded-full"></div>
                                <div>
                                    <div class="font-semibold text-gray-900"><?php echo htmlspecialchars($type['name']); ?></div>
                                    <div class="text-sm text-gray-600"><?php echo $type['tenant_count']; ?> <?php echo __('profile.tenants_label'); ?></div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-bold text-green-600"><?php echo number_format($converted_revenue, 2); ?></div>
                                <div class="text-xs text-gray-500"><?php echo $current_currency['symbol']; ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Financial Performance Summary -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Active vs Expired Contracts -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-file-contract text-orange-500 ml-2"></i>
                        <?php echo __('profile.contracts_status'); ?>
                    </h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                <span class="text-green-700 font-medium"><?php echo __('profile.active_contracts'); ?></span>
                            </div>
                            <div class="text-green-600 font-bold"><?php echo $active_contracts; ?></div>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                                <span class="text-red-700 font-medium"><?php echo __('profile.expired_contracts'); ?></span>
                            </div>
                            <div class="text-red-600 font-bold"><?php echo $expired_contracts; ?></div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-rocket text-purple-500 ml-2"></i>
                        <?php echo __('profile.quick_actions'); ?>
                    </h3>
                    <div class="space-y-3">
                        <a href="full_tenants_list.php" class="block w-full p-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg text-center font-medium hover:from-blue-600 hover:to-blue-700 transition-all duration-300">
                            <i class="fas fa-list ml-2"></i>
                            <?php echo __('profile.view_all_tenants'); ?>
                        </a>
                        <a href="export_tenants_excel.php" class="block w-full p-3 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg text-center font-medium hover:from-green-600 hover:to-green-700 transition-all duration-300">
                            <i class="fas fa-file-excel ml-2"></i>
                            <?php echo __('profile.export_excel'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Revenue Summary Card -->
        <div class="glass-effect rounded-3xl shadow-2xl p-8 text-center mb-12">
            <div class="max-w-2xl mx-auto">
                <div class="bg-gradient-to-r from-amber-400 to-orange-500 text-white rounded-2xl p-6 mb-6 inline-block">
                    <i class="fas fa-coins text-5xl"></i>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-4"><?php echo __('profile.revenue_summary'); ?></h2>
                <div class="text-6xl font-bold gradient-text mb-4">
                    <?php echo number_format($total_rent, 2); ?>
                </div>
                <div class="text-xl text-gray-600 font-medium"><?php echo $current_currency['symbol']; ?> <?php echo $current_currency['code']; ?></div>
                <div class="w-24 h-1 bg-gradient-to-r from-amber-400 to-orange-500 mx-auto mt-6 rounded-full"></div>
            </div>
        </div>
    </div>
</body>
</html> 