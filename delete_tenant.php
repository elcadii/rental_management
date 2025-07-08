<?php
require_once 'config/db.php';
requireLogin();

$tenant_id = (int)($_GET['id'] ?? 0);

// Check if tenant exists and belongs to the current admin
$stmt = $pdo->prepare("SELECT full_name FROM tenants WHERE id = ? AND admin_id = ?");
$stmt->execute([$tenant_id, $_SESSION['admin_id']]);
$tenant = $stmt->fetch();

if (!$tenant) {
    header('Location: index.php');
    exit();
}

// Delete tenant
$stmt = $pdo->prepare("DELETE FROM tenants WHERE id = ? AND admin_id = ?");
if ($stmt->execute([$tenant_id, $_SESSION['admin_id']])) {
    $_SESSION['success_message'] = 'تم حذف المستأجر "' . htmlspecialchars($tenant['full_name']) . '" بنجاح';
} else {
    $_SESSION['error_message'] = 'حدث خطأ أثناء حذف المستأجر';
}

header('Location: index.php');
exit();
?>
