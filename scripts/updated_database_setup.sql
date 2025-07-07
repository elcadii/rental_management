-- إنشاء قاعدة البيانات
CREATE DATABASE IF NOT EXISTS rental_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE rental_management;

-- حذف الجداول إذا كانت موجودة
DROP TABLE IF EXISTS tenants;
DROP TABLE IF EXISTS admins;

-- جدول المديرين (أصحاب العقارات)
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول المستأجرين - محدث بالحقول الجديدة
CREATE TABLE IF NOT EXISTS tenants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    cin VARCHAR(20) NOT NULL,
    house_type ENUM('شقة', 'فيلا', 'استوديو', 'غرفة', 'محل تجاري', 'مكتب') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    admin_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
);

-- إدراج بيانات تجريبية
INSERT INTO admins (name, email, password) VALUES 
('أحمد محمد', 'ahmed@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('فاطمة علي', 'fatima@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO tenants (full_name, phone, email, cin, house_type, start_date, end_date, admin_id) VALUES 
('محمد أحمد السعيد', '0123456789', 'mohamed@example.com', 'AB123456', 'شقة', '2024-01-01', '2024-12-31', 1),
('سارة محمود', '0987654321', 'sara@example.com', 'CD789012', 'فيلا', '2024-02-01', '2024-08-01', 1),
('عمر حسن', '0555123456', 'omar@example.com', 'EF345678', 'استوديو', '2024-03-01', '2024-06-01', 2);
