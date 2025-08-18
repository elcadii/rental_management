<?php
require_once 'includes/currency_manager.php';
?>

<!-- Currency Selector Component -->
<div class="currency-selector bg-white/90 backdrop-blur-sm rounded-2xl p-4 mb-6 text-center shadow-lg border border-white/30">
    <form method="POST" class="inline-flex items-center gap-4">
        <label for="selected_currency" class="text-lg font-semibold text-gray-800">
            <i class="fas fa-exchange-alt ml-2"></i>
            اختر العملة:
        </label>
        <select name="selected_currency" id="selected_currency" 
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg font-medium"
                onchange="this.form.submit()">
            <option value="MAD" <?php echo $selected_currency === 'MAD' ? 'selected' : ''; ?>>د.م الدرهم المغربي (MAD)</option>
            <option value="USD" <?php echo $selected_currency === 'USD' ? 'selected' : ''; ?>>$ الدولار الأمريكي (USD)</option>
            <option value="EUR" <?php echo $selected_currency === 'EUR' ? 'selected' : ''; ?>>€ اليورو (EUR)</option>
            <option value="GBP" <?php echo $selected_currency === 'GBP' ? 'selected' : ''; ?>>£ الجنيه الإسترليني (GBP)</option>
            <option value="JPY" <?php echo $selected_currency === 'JPY' ? 'selected' : ''; ?>>¥ الين الياباني (JPY)</option>
        </select>
        <input type="hidden" name="currency_change" value="1">
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            <i class="fas fa-sync-alt ml-1"></i>
            تحديث
        </button>
    </form>
    <div class="mt-2 text-sm text-gray-600">
        العملة المحددة: <span class="font-semibold"><?php echo $currency_symbols[$selected_currency]; ?> <?php echo $selected_currency; ?></span>
    </div>
</div>

<style>
.currency-selector {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.3);
}
</style> 