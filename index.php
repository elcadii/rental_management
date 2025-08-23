<?php
// Landing page for the system
session_start();
require_once __DIR__ . '/includes/i18n.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['admin_id']);
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(currentLang()); ?>" dir="<?php echo htmlspecialchars(currentDir()); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('app.title'); ?> | <?php echo __('index.home'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'cairo': ['Cairo', 'sans-serif'],
                    },
                    colors: {
                        primary: '#2563eb',
                    }
                }
            }
        }
        
        // Mobile Menu Functions - Define early so it's available for onclick
        function toggleMobileMenu() {
            console.log('toggleMobileMenu function called');
            const mobileMenu = document.getElementById('mobileMenu');
            const mobileMenuIcon = document.getElementById('mobileMenuIcon');
            
            if (!mobileMenu || !mobileMenuIcon) {
                console.error('Mobile menu elements not found');
                return;
            }
            
            if (mobileMenu.classList.contains('hidden')) {
                mobileMenu.classList.remove('hidden');
                mobileMenuIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                `;
            } else {
                mobileMenu.classList.add('hidden');
                mobileMenuIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                `;
            }
        }
    </script>
    <style>
        body { font-family: 'Cairo', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-background" dir="<?php echo htmlspecialchars(currentDir()); ?>">
    <!-- Trial Banner - Only show if not logged in -->
    <?php if (!$isLoggedIn): ?>
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-center">
                <span class="text-lg mr-2">✨</span>
                <p class="text-sm font-medium"><?php echo __('index.trial_banner.message'); ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Header -->
    <header class="border-b bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/60 sticky top-0 z-50">
        <div class="container mx-auto px-4 h-16 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="h-8 w-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <span class="text-xl font-bold"><?php echo __('app.title'); ?></span>
            </div>
            
            <!-- Desktop Navigation -->
            <nav class="hidden lg:flex items-center gap-6">
                <a href="#features" class="text-sm font-medium hover:text-primary transition-colors"><?php echo __('index.nav.features'); ?></a>
                <a href="#pricing" class="text-sm font-medium hover:text-primary transition-colors"><?php echo __('index.nav.pricing'); ?></a>
                <a href="#testimonials" class="text-sm font-medium hover:text-primary transition-colors"><?php echo __('index.nav.testimonials'); ?></a>
                <a href="#contact" class="text-sm font-medium hover:text-primary transition-colors"><?php echo __('index.nav.contact'); ?></a>
            </nav>
            
            <div class="flex items-center gap-2">
                <!-- Language Selector -->
                <form method="GET" class="flex items-center mb-0">
                    <select name="lang" onchange="this.form.submit()" class="px-2 py-1 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                        <?php foreach (supportedLanguages() as $code => $lang): ?>
                            <option value="<?php echo htmlspecialchars($code); ?>" <?php echo currentLang() === $code ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($lang['flag']); ?> <?php echo htmlspecialchars($lang['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
                
                <?php if ($isLoggedIn): ?>
                    <a href="dashboard.php" class="hidden sm:inline-block px-4 py-2 text-sm bg-primary text-white rounded-md hover:bg-blue-700 transition-colors font-medium">
                        <?php echo __('index.nav.dashboard'); ?>
                    </a>
                    <a href="logout.php" class="hidden sm:inline-block px-3 py-2 text-sm font-medium hover:text-primary transition-colors">
                        <?php echo __('header.logout'); ?>
                    </a>
                <?php else: ?>
                    <a href="login.php" class="hidden sm:inline-block px-3 py-2 text-sm font-medium hover:text-primary transition-colors"><?php echo __('index.nav.login'); ?></a>
                    <button onclick="openTrialModal()" class="hidden sm:inline-block px-4 py-2 text-sm bg-primary text-white rounded-md hover:bg-blue-700 transition-colors font-medium">
                        <?php echo __('index.cta.trial'); ?>
                    </button>
                <?php endif; ?>
                
                <!-- Mobile Menu Button -->
                <button onclick="toggleMobileMenu()" class="lg:hidden p-2 rounded-md hover:bg-gray-100 transition-colors">
                    <svg id="mobileMenuIcon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Mobile Navigation Menu -->
        <div id="mobileMenu" class="lg:hidden hidden bg-white border-t">
            <div class="container mx-auto px-4 py-4">
                <nav class="flex flex-col space-y-4">
                    <a href="#features" class="text-sm font-medium hover:text-primary transition-colors py-2"><?php echo __('index.nav.features'); ?></a>
                    <a href="#pricing" class="text-sm font-medium hover:text-primary transition-colors py-2"><?php echo __('index.nav.pricing'); ?></a>
                    <a href="#testimonials" class="text-sm font-medium hover:text-primary transition-colors py-2"><?php echo __('index.nav.testimonials'); ?></a>
                    <a href="#contact" class="text-sm font-medium hover:text-primary transition-colors py-2"><?php echo __('index.nav.contact'); ?></a>
                    
                    <?php if ($isLoggedIn): ?>
                        <div class="pt-4 border-t">
                            <a href="dashboard.php" class="block w-full text-center px-4 py-2 text-sm bg-primary text-white rounded-md hover:bg-blue-700 transition-colors font-medium mb-2">
                                <?php echo __('index.nav.dashboard'); ?>
                            </a>
                            <a href="logout.php" class="block w-full text-center px-4 py-2 text-sm font-medium hover:text-primary transition-colors">
                                <?php echo __('header.logout'); ?>
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="pt-4 border-t">
                            <a href="login.php" class="block w-full text-center px-4 py-2 text-sm font-medium hover:text-primary transition-colors mb-2"><?php echo __('index.nav.login'); ?></a>
                            <button onclick="openTrialModal()" class="block w-full text-center px-4 py-2 text-sm bg-primary text-white rounded-md hover:bg-blue-700 transition-colors font-medium">
                                <?php echo __('index.cta.trial'); ?>
                            </button>
                        </div>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="py-20 lg:py-32 bg-gradient-to-b from-blue-50 to-white">
        <div class="container mx-auto px-4">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="space-y-8">
                    <div class="space-y-4">
                        <span class="inline-block px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-full"><?php echo __('index.hero.badge'); ?></span>
                        <h1 class="text-4xl lg:text-6xl font-bold leading-tight">
                            <?php echo __('index.hero.title_line1'); ?>
                            <span class="text-primary block"><?php echo __('index.hero.title_line2'); ?></span>
                        </h1>
                        <p class="text-xl text-gray-600 leading-relaxed">
                            <?php echo __('index.hero.subtitle'); ?>
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <?php if ($isLoggedIn): ?>
                            <a href="dashboard.php" class="inline-flex items-center justify-center px-8 py-3 text-lg font-medium text-white bg-primary rounded-md hover:bg-blue-700 transition-colors">
                                <?php echo __('index.nav.dashboard'); ?>
                            </a>
                            <a href="#" class="inline-flex items-center justify-center px-8 py-3 text-lg font-medium text-primary border border-primary rounded-md hover:bg-primary hover:text-white transition-colors">
                                <?php echo __('index.hero.watch_demo'); ?>
                            </a>
                        <?php else: ?>
                            <button onclick="openTrialModal()" class="inline-flex items-center justify-center px-8 py-3 text-lg font-medium text-white bg-primary rounded-md hover:bg-blue-700 transition-colors">
                                <?php echo __('index.cta.trial'); ?>
                            </button>
                            <a href="#" class="inline-flex items-center justify-center px-8 py-3 text-lg font-medium text-primary border border-primary rounded-md hover:bg-primary hover:text-white transition-colors">
                                <?php echo __('index.hero.watch_demo'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="flex items-center gap-8 pt-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-primary">500+</div>
                            <div class="text-sm text-gray-600"><?php echo __('index.stats.landlords'); ?></div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-primary">5000+</div>
                            <div class="text-sm text-gray-600"><?php echo __('index.stats.units'); ?></div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-primary">99%</div>
                            <div class="text-sm text-gray-600"><?php echo __('index.stats.satisfaction'); ?></div>
                        </div>
                    </div>
                </div>
                <div class="relative">
                    <img src="img/0c4db0db4dc3bc825a30cc2b25242e62.jpg" class="w-full h-96 bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl shadow-2xl flex items-center justify-center">
                        
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-4xl font-bold mb-4"><?php echo __('index.features.title'); ?></h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto"><?php echo __('index.features.subtitle'); ?></p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="border-0 shadow-lg hover:shadow-xl transition-shadow rounded-lg p-6">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2"><?php echo __('index.features.tenants.title'); ?></h3>
                    <p class="text-gray-600"><?php echo __('index.features.tenants.desc'); ?></p>
                </div>

                <div class="border-0 shadow-lg hover:shadow-xl transition-shadow rounded-lg p-6">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2"><?php echo __('index.features.properties.title'); ?></h3>
                    <p class="text-gray-600"><?php echo __('index.features.properties.desc'); ?></p>
                </div>

                <div class="border-0 shadow-lg hover:shadow-xl transition-shadow rounded-lg p-6">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2"><?php echo __('index.features.payments.title'); ?></h3>
                    <p class="text-gray-600"><?php echo __('index.features.payments.desc'); ?></p>
                </div>

                <div class="border-0 shadow-lg hover:shadow-xl transition-shadow rounded-lg p-6">
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2"><?php echo __('index.features.contracts.title'); ?></h3>
                    <p class="text-gray-600"><?php echo __('index.features.contracts.desc'); ?></p>
                </div>

                <div class="border-0 shadow-lg hover:shadow-xl transition-shadow rounded-lg p-6">
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2"><?php echo __('index.features.reports.title'); ?></h3>
                    <p class="text-gray-600"><?php echo __('index.features.reports.desc'); ?></p>
                </div>

                <div class="border-0 shadow-lg hover:shadow-xl transition-shadow rounded-lg p-6">
                    <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="h-6 w-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2"><?php echo __('index.features.security.title'); ?></h3>
                    <p class="text-gray-600"><?php echo __('index.features.security.desc'); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="py-20 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div>
                    <img src="img/Why choose our system.jpg" class="w-full h-80 bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl shadow-xl flex items-center justify-center">
                       
                    </img>
                </div>
                <div class="space-y-8">
                    <div>
                        <h2 class="text-3xl lg:text-4xl font-bold mb-6"><?php echo __('index.benefits.title'); ?></h2>
                        <p class="text-lg text-gray-600 mb-8"><?php echo __('index.benefits.description'); ?></p>
                    </div>

                    <div class="space-y-6">
                        <div class="flex gap-4">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold mb-2"><?php echo __('index.benefits.items.save_time.title'); ?></h3>
                                <p class="text-gray-600"><?php echo __('index.benefits.items.save_time.desc'); ?></p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold mb-2"><?php echo __('index.benefits.items.increase_revenue.title'); ?></h3>
                                <p class="text-gray-600"><?php echo __('index.benefits.items.increase_revenue.desc'); ?></p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold mb-2"><?php echo __('index.benefits.items.ease_of_use.title'); ?></h3>
                                <p class="text-gray-600"><?php echo __('index.benefits.items.ease_of_use.desc'); ?></p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold mb-2"><?php echo __('index.benefits.items.great_support.title'); ?></h3>
                                <p class="text-gray-600"><?php echo __('index.benefits.items.great_support.desc'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section id="testimonials" class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-4xl font-bold mb-4"><?php echo __('index.testimonials.title'); ?></h2>
                <p class="text-xl text-gray-600"><?php echo __('index.testimonials.subtitle'); ?></p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="border-0 shadow-lg rounded-lg p-6">
                    <div class="flex mb-4">
                        <svg class="h-5 w-5 fill-yellow-400 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                        <svg class="h-5 w-5 fill-yellow-400 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                        <svg class="h-5 w-5 fill-yellow-400 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                        <svg class="h-5 w-5 fill-yellow-400 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                        <svg class="h-5 w-5 fill-yellow-400 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                    </div>
                    <p class="text-gray-600 mb-6">"<?php echo __('index.testimonials.card1.quote'); ?>"</p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gray-200 rounded-full"></div>
                        <div>
                            <div class="font-semibold"><?php echo __('index.testimonials.card1.name'); ?></div>
                            <div class="text-sm text-gray-600"><?php echo __('index.testimonials.card1.role'); ?></div>
                        </div>
                    </div>
                </div>

                <div class="border-0 shadow-lg rounded-lg p-6">
                    <div class="flex mb-4">
                        <svg class="h-5 w-5 fill-yellow-400 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                        <svg class="h-5 w-5 fill-yellow-400 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                        <svg class="h-5 w-5 fill-yellow-400 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                        <svg class="h-5 w-5 fill-yellow-400 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                        <svg class="h-5 w-5 fill-yellow-400 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                    </div>
                    <p class="text-gray-600 mb-6">"<?php echo __('index.testimonials.card2.quote'); ?>"</p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gray-200 rounded-full"></div>
                        <div>
                            <div class="font-semibold"><?php echo __('index.testimonials.card2.name'); ?></div>
                            <div class="text-sm text-gray-600"><?php echo __('index.testimonials.card2.role'); ?></div>
                        </div>
                    </div>
                </div>

                <div class="border-0 shadow-lg rounded-lg p-6">
                    <div class="flex mb-4">
                        <svg class="h-5 w-5 fill-yellow-400 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                        <svg class="h-5 w-5 fill-yellow-400 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                        <svg class="h-5 w-5 fill-yellow-400 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                        <svg class="h-5 w-5 fill-yellow-400 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                        <svg class="h-5 w-5 fill-yellow-400 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                    </div>
                    <p class="text-gray-600 mb-6">"<?php echo __('index.testimonials.card3.quote'); ?>"</p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gray-200 rounded-full"></div>
                                <div>
                            <div class="font-semibold"><?php echo __('index.testimonials.card3.name'); ?></div>
                            <div class="text-sm text-gray-600"><?php echo __('index.testimonials.card3.role'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

        <!-- Pricing Plans Section -->
    <section id="pricing" class="py-20 bg-gradient-to-br from-gray-50 to-blue-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-4xl font-bold mb-4 text-gray-900"><?php echo __('index.pricing.title'); ?></h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto"><?php echo __('index.pricing.subtitle'); ?></p>
            </div>

            <div class="grid lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <!-- Free Trial Plan -->
                <div class="bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border border-gray-200 overflow-hidden">
                    <div class="p-8">
                        <div class="text-center mb-8">
                            <div class="inline-block px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full mb-4"><?php echo __('index.pricing.free.badge'); ?></div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2"><?php echo __('index.pricing.free.title'); ?></h3>
                            <div class="text-4xl font-bold text-primary mb-2">
                                <?php echo __('index.pricing.free.duration'); ?>
                                <span class="text-lg text-gray-600 font-normal"><?php echo __('index.pricing.free.free_label'); ?></span>
                            </div>
                            <p class="text-gray-600"><?php echo __('index.pricing.free.features_summary'); ?></p>
                        </div>
                        
                        <div class="space-y-4 mb-8">
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <span class="text-gray-700"><?php echo __('index.pricing.free.features.renters_limit'); ?></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <span class="text-gray-700"><?php echo __('index.pricing.free.features.data_management'); ?></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <span class="text-gray-700"><?php echo __('index.pricing.free.features.basic_rent'); ?></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <span class="text-gray-700"><?php echo __('index.pricing.free.features.view_list'); ?></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                                <span class="text-gray-500 line-through"><?php echo __('index.pricing.basic.features.export_data'); ?></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                                <span class="text-gray-500 line-through"><?php echo __('index.pricing.premium.features.advanced_financial_reports'); ?></span>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <?php if ($isLoggedIn): ?>
                                <a href="dashboard.php" class="w-full inline-block text-center px-6 py-3 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition-colors">
                                    <?php echo __('index.nav.dashboard'); ?>
                                </a>
                            <?php else: ?>
                                <button onclick="openTrialModal()" class="w-full px-6 py-3 bg-blue-600 text-white rounded-xl font-medium hover:bg-blue-700 transition-colors shadow-lg hover:shadow-xl">
                                    <?php echo __('index.cta.trial'); ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Basic Plan -->
                <div class="bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border border-gray-200 overflow-hidden">
                    <div class="p-8">
                        <div class="text-center mb-8">
                            <h3 class="text-2xl font-bold text-gray-900 mb-2"><?php echo __('index.pricing.basic.title'); ?></h3>
                            <div class="text-4xl font-bold text-primary mb-2">
                                <?php echo __('index.pricing.basic.price_usd'); ?>
                                <span class="text-lg text-gray-600 font-normal"><?php echo __('index.pricing.basic.period_month'); ?></span>
                            </div>
                            <p class="text-gray-600"><?php echo __('index.pricing.basic.summary'); ?></p>
                        </div>
                        
                        <div class="space-y-4 mb-8">
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <span class="text-gray-700"><?php echo __('index.pricing.basic.features.includes_free'); ?></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <span class="text-gray-700"><?php echo __('index.pricing.basic.features.unlimited_tenants'); ?></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <span class="text-gray-700"><?php echo __('index.pricing.basic.features.manage_housing_types'); ?></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <span class="text-gray-700"><?php echo __('index.pricing.basic.features.export_data'); ?></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <span class="text-gray-700"><?php echo __('index.pricing.basic.features.basic_reports'); ?></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <span class="text-gray-700"><?php echo __('index.pricing.basic.features.basic_support'); ?></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                                <span class="text-gray-500 line-through"><?php echo __('index.pricing.premium.features.advanced_financial_reports'); ?></span>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <?php if ($isLoggedIn): ?>
                                <a href="dashboard.php" class="w-full inline-block text-center px-6 py-3 bg-primary text-white rounded-xl font-medium hover:bg-blue-700 transition-colors shadow-lg hover:shadow-xl">
                                    <?php echo __('index.nav.dashboard'); ?>
                                </a>
                            <?php else: ?>
                                <button onclick="openTrialModal()" class="w-full px-6 py-3 bg-primary text-white rounded-xl font-medium hover:bg-blue-700 transition-colors shadow-lg hover:shadow-xl">
                                    <?php echo __('index.cta.trial'); ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Premium Plan -->
                <div class="bg-white rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border-2 border-primary relative overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 bg-gradient-to-r from-primary to-blue-600 text-white text-center py-2">
                        <span class="text-sm font-medium"><?php echo __('index.pricing.premium.ribbon'); ?></span>
                    </div>
                    <div class="p-8 pt-12">
                        <div class="text-center mb-8">
                            <h3 class="text-2xl font-bold text-gray-900 mb-2"><?php echo __('index.pricing.premium.title'); ?></h3>
                            <div class="text-4xl font-bold text-primary mb-2">
                                <?php echo __('index.pricing.premium.price_usd'); ?>
                                <span class="text-lg text-gray-600 font-normal"><?php echo __('index.pricing.premium.period_month'); ?></span>
                            </div>
                            <p class="text-gray-600"><?php echo __('index.pricing.premium.summary'); ?></p>
                        </div>
                        
                        <div class="space-y-4 mb-8">
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <span class="text-gray-700"><?php echo __('index.pricing.premium.features.includes_basic'); ?></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <span class="text-gray-700"><?php echo __('index.pricing.premium.features.advanced_financial_reports'); ?></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <span class="text-gray-700"><?php echo __('index.pricing.premium.features.full_analytics'); ?></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <span class="text-gray-700"><?php echo __('index.pricing.premium.features.advanced_notifications'); ?></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <span class="text-gray-700"><?php echo __('index.pricing.premium.features.priority_support'); ?></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <span class="text-gray-700"><?php echo __('index.pricing.premium.features.automatic_backups'); ?></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <span class="text-gray-700"><?php echo __('index.pricing.premium.features.full_customization'); ?></span>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <?php if ($isLoggedIn): ?>
                                <a href="dashboard.php" class="w-full inline-block text-center px-6 py-3 bg-primary text-white rounded-xl font-medium hover:bg-blue-700 transition-colors shadow-lg hover:shadow-xl">
                                    <?php echo __('index.nav.dashboard'); ?>
                                </a>
                            <?php else: ?>
                                <button onclick="openTrialModal()" class="w-full px-6 py-3 bg-primary text-white rounded-xl font-medium hover:bg-blue-700 transition-colors shadow-lg hover:shadow-xl">
                                    <?php echo __('index.cta.trial'); ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Additional Info -->
            <div class="text-center mt-12">
                <p class="text-gray-600 mb-4"><?php echo __('index.pricing.additional.note'); ?></p>
                <div class="flex flex-wrap justify-center gap-6 text-sm text-gray-500">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span><?php echo __('index.pricing.additional.cancel_anytime'); ?></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span><?php echo __('index.pricing.additional.support_available'); ?></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span><?php echo __('index.pricing.additional.free_updates'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-primary text-white">
        <div class="container mx-auto px-4 text-center">
            <div class="max-w-3xl mx-auto space-y-8">
                <h2 class="text-3xl lg:text-4xl font-bold"><?php echo __('index.cta.title'); ?></h2>
                <p class="text-xl opacity-90"><?php echo __('index.cta.subtitle'); ?></p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <?php if ($isLoggedIn): ?>
                        <a href="dashboard.php" class="inline-flex items-center justify-center px-8 py-3 text-lg font-medium bg-white text-primary rounded-md hover:bg-gray-100 transition-colors">
                            <?php echo __('index.nav.dashboard'); ?>
                        </a>
                        <a href="#contact" class="inline-flex items-center justify-center px-8 py-3 text-lg font-medium border border-white text-white rounded-md hover:bg-white hover:text-primary transition-colors">
                            <?php echo __('index.cta.talk_to_expert'); ?>
                        </a>
                    <?php else: ?>
                        <button onclick="openTrialModal()" class="inline-flex items-center justify-center px-8 py-3 text-lg font-medium bg-white text-primary rounded-md hover:bg-gray-100 transition-colors">
                            <?php echo __('index.cta.trial_14'); ?>
                        </button>
                        <a href="#contact" class="inline-flex items-center justify-center px-8 py-3 text-lg font-medium border border-white text-white rounded-md hover:bg-white hover:text-primary transition-colors">
                            <?php echo __('index.cta.talk_to_expert'); ?>
                        </a>
                    <?php endif; ?>
                </div>
                <p class="text-sm opacity-75"><?php echo __('index.cta.no_cc'); ?></p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact" class="bg-gray-900 text-white py-16">
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <svg class="h-8 w-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <span class="text-xl font-bold"><?php echo __('app.title'); ?></span>
                    </div>
                    <p class="text-gray-400"><?php echo __('index.footer.desc'); ?></p>
                    <div class="flex gap-4">
                        <div class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-primary cursor-pointer transition-colors">
                            <span class="text-sm">ف</span>
                        </div>
                        <div class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-primary cursor-pointer transition-colors">
                            <span class="text-sm">ت</span>
                        </div>
                        <div class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-primary cursor-pointer transition-colors">
                            <span class="text-sm">ل</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="text-lg font-semibold"><?php echo __('index.footer.product'); ?></h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#features" class="hover:text-white transition-colors"><?php echo __('index.nav.features'); ?></a></li>
                        <li><a href="#pricing" class="hover:text-white transition-colors"><?php echo __('index.nav.pricing'); ?></a></li>
                        <li><a href="#" class="hover:text-white transition-colors"><?php echo __('index.footer.updates'); ?></a></li>
                        <li><a href="#" class="hover:text-white transition-colors"><?php echo __('index.footer.security'); ?></a></li>
                    </ul>
    </div>

                <div class="space-y-4">
                    <h3 class="text-lg font-semibold"><?php echo __('index.footer.support'); ?></h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition-colors"><?php echo __('index.footer.help_center'); ?></a></li>
                        <li><a href="#" class="hover:text-white transition-colors"><?php echo __('index.footer.tech_support'); ?></a></li>
                        <li><a href="#" class="hover:text-white transition-colors"><?php echo __('index.footer.training'); ?></a></li>
                        <li><a href="#" class="hover:text-white transition-colors"><?php echo __('index.footer.faq'); ?></a></li>
                    </ul>
                </div>

                <div class="space-y-4">
                    <h3 class="text-lg font-semibold"><?php echo __('index.footer.contact'); ?></h3>
                    <div class="space-y-3 text-gray-400">
                        <div class="flex items-center gap-3">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <span><?php echo __('index.footer.phone'); ?></span>
                        </div>
                        <div class="flex items-center gap-3">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <span><?php echo __('index.footer.email'); ?></span>
                        </div>
                        <div class="flex items-center gap-3">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span><?php echo __('index.footer.address'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-400 text-sm">© 2024 <?php echo __('app.title'); ?>. <?php echo __('index.footer.rights'); ?></p>
                <div class="flex gap-6 text-sm text-gray-400 mt-4 md:mt-0">
                    <a href="#" class="hover:text-white transition-colors"><?php echo __('index.footer.privacy'); ?></a>
                    <a href="#" class="hover:text-white transition-colors"><?php echo __('index.footer.terms'); ?></a>
                    <a href="#" class="hover:text-white transition-colors"><?php echo __('index.footer.cookies'); ?></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Trial Signup Modal - Only show if not logged in -->
    <?php if (!$isLoggedIn): ?>
    <div id="trialModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-2xl font-bold text-gray-900"><?php echo __('index.cta.trial'); ?></h3>
                        <button onclick="closeTrialModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <div class="flex items-center">
                            <span class="text-2xl mr-3">✨</span>
                            <div>
                                <p class="font-semibold text-blue-900"><?php echo __('index.pricing.free.duration'); ?> <?php echo __('index.pricing.free.free_label'); ?></p>
                                <p class="text-sm text-blue-700"><?php echo __('index.cta.no_cc'); ?></p>
                            </div>
                        </div>
                    </div>

                    <form id="trialForm" method="POST" action="register.php" class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('register.name'); ?></label>
                            <input type="text" id="name" name="name" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label for="email_or_phone" class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('register.email_or_phone'); ?></label>
                            <input type="text" id="email_or_phone" name="email_or_phone" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="example@email.com أو +966501234567">
                        </div>
                        
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('register.password'); ?></label>
                            <input type="password" id="password" name="password" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   minlength="6">
                        </div>
                        
                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('register.confirm_password'); ?></label>
                            <input type="password" id="confirm_password" name="confirm_password" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   minlength="6">
                        </div>
                        
                        <button type="submit" 
                                class="w-full bg-primary text-white py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                            <?php echo __('index.cta.trial'); ?>
                        </button>
                    </form>
                    
                    <p class="text-xs text-gray-500 text-center mt-4">
                        <?php echo __('index.footer.terms'); ?> 
                        <a href="#" class="text-blue-600 hover:underline"><?php echo __('index.footer.terms'); ?></a> 
                        <?php echo __('index.footer.privacy'); ?>
                        <a href="#" class="text-blue-600 hover:underline"><?php echo __('index.footer.privacy'); ?></a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>

        
        // Trial Modal Functions
        function openTrialModal() {
            document.getElementById('trialModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        
        function closeTrialModal() {
            document.getElementById('trialModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
        
        // Initialize event listeners when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Close modal when clicking outside
            const trialModal = document.getElementById('trialModal');
            if (trialModal) {
                trialModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeTrialModal();
                    }
                });
            }
            
            // Form validation
            const trialForm = document.getElementById('trialForm');
            if (trialForm) {
                trialForm.addEventListener('submit', function(e) {
                    const password = document.getElementById('password').value;
                    const confirmPassword = document.getElementById('confirm_password').value;
                    
                    if (password !== confirmPassword) {
                        e.preventDefault();
                        alert('<?php echo __('errors.password_mismatch'); ?>');
                        return false;
                    }
                    
                    if (password.length < 6) {
                        e.preventDefault();
                        alert('<?php echo __('errors.password_length'); ?>');
                        return false;
                    }
                });
            }
            
            // Close modal with Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeTrialModal();
                }
            });
        });
    </script>
    <?php endif; ?>
</body>
</html>
