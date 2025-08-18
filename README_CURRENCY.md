# نظام إدارة العملة - Currency Management System

## كيفية استخدام نظام العملة في أي صفحة:

### 1. إضافة ملف إدارة العملة:
```php
<?php
require_once 'includes/currency_manager.php';
?>
```

### 2. استخدام الدوال المتاحة:

#### تحويل العملة:
```php
$amount_mad = 1000; // مبلغ بالدرهم المغربي
$converted_amount = convertCurrency($amount_mad); // تحويل للعملة المحددة
```

#### عرض العملة المنسقة:
```php
$formatted_amount = formatCurrency($amount); // تنسيق مع الرمز
echo $formatted_amount; // مثال: $ 99.00
```

#### الحصول على معلومات العملة الحالية:
```php
$current_currency = getCurrentCurrency();
echo $current_currency['symbol']; // رمز العملة
echo $current_currency['code'];   // كود العملة
echo $current_currency['name'];   // اسم العملة
```

### 3. إضافة محدد العملة:
```php
<?php include 'components/currency_selector.php'; ?>
```

## مثال على صفحة تستخدم النظام:

```php
<?php
require_once 'config/db.php';
require_once 'includes/currency_manager.php';
requireLogin();

// استخدام النظام
$total_revenue_mad = 50000; // إيرادات بالدرهم
$total_revenue = convertCurrency($total_revenue_mad); // تحويل للعملة المحددة

// عرض محدد العملة
include 'components/currency_selector.php';

// عرض القيم
echo formatCurrency($total_revenue);
?>
```

## الميزات:
- ✅ تحويل تلقائي لجميع القيم المالية
- ✅ حفظ العملة المختارة في الجلسة
- ✅ دعم 5 عملات رئيسية
- ✅ تحديث فوري عند تغيير العملة
- ✅ سهولة الاستخدام في أي صفحة 