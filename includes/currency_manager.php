<?php
// Currency Manager - إدارة العملة
// This file manages currency selection and conversion across the entire website

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Currency conversion rates (you can update these or fetch from an API)
$currency_rates = [
    'MAD' => 1.0,      // Moroccan Dirham (base currency)
    'USD' => 0.099,    // US Dollar
    'EUR' => 0.092,    // Euro
    'GBP' => 0.079,    // British Pound
    'JPY' => 14.85     // Japanese Yen
];

// Currency symbols and names
$currency_symbols = [
    'MAD' => 'د.م',
    'USD' => '$',
    'EUR' => '€',
    'GBP' => '£',
    'JPY' => '¥'
];

$currency_names = [
    'MAD' => 'الدرهم المغربي',
    'USD' => 'الدولار الأمريكي',
    'EUR' => 'اليورو',
    'GBP' => 'الجنيه الإسترليني',
    'JPY' => 'الين الياباني'
];

// Get selected currency from session or default to MAD
$selected_currency = $_SESSION['selected_currency'] ?? 'MAD';

// Function to convert currency
function convertCurrency($amount, $from_currency = 'MAD', $to_currency = null, $rates = null) {
    global $currency_rates, $selected_currency;
    
    if ($to_currency === null) {
        $to_currency = $selected_currency;
    }
    
    if ($rates === null) {
        $rates = $currency_rates;
    }
    
    if ($from_currency === $to_currency) return $amount;
    
    // Check if currencies exist in rates
    if (!isset($rates[$from_currency]) || !isset($rates[$to_currency])) {
        return $amount; // Return original amount if conversion not possible
    }
    
    return $amount * $rates[$to_currency];
}

// Function to format currency display
function formatCurrency($amount, $currency = null, $decimals = 2) {
    global $currency_symbols, $selected_currency;
    
    if ($currency === null) {
        $currency = $selected_currency;
    }
    
    $symbol = $currency_symbols[$currency] ?? '';
    $formatted_amount = number_format($amount, $decimals);
    
    return $symbol . ' ' . $formatted_amount;
}

// Function to get current currency info
function getCurrentCurrency() {
    global $selected_currency, $currency_symbols, $currency_names;
    
    return [
        'code' => $selected_currency,
        'symbol' => $currency_symbols[$selected_currency],
        'name' => $currency_names[$selected_currency]
    ];
}

// Function to handle currency change
function changeCurrency($new_currency) {
    global $currency_rates;
    
    if (isset($currency_rates[$new_currency])) {
        $_SESSION['selected_currency'] = $new_currency;
        return true;
    }
    return false;
}

// Handle currency change from POST request
if (isset($_POST['currency_change']) && isset($_POST['selected_currency'])) {
    $new_currency = $_POST['selected_currency'];
    if (changeCurrency($new_currency)) {
        // Redirect to refresh the page with new currency
        $redirect_url = $_SERVER['REQUEST_URI'];
        header('Location: ' . $redirect_url);
        exit();
    }
}
?> 