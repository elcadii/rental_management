-- Create the database
CREATE DATABASE IF NOT EXISTS rental_management_ain_tighdouin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE rental_management_ain_tighdouin;

-- Admins table (property owners)
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tenants table
CREATE TABLE IF NOT EXISTS tenants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    cin VARCHAR(20) NOT NULL,
    house_type ENUM('شقة', 'فيلا', 'استوديو', 'غرفة', 'محل تجاري') NOT NULL,
    start_date DATE NOT NULL,
    duration INT NOT NULL,
    duration_type ENUM('أيام', 'شهور') NOT NULL DEFAULT 'شهور',
    admin_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
);

-- Insert sample data
INSERT INTO admins (name, email, password) VALUES 
('أحمد محمد', 'ahmed@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('فاطمة علي', 'fatima@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO tenants (full_name, phone, email, cin, house_type, start_date, duration, admin_id) VALUES 
('محمد أحمد السعيد', '0123456789', 'mohamed@example.com', 'AB123456', 'شقة', '2024-01-01', 12, 1),
('سارة محمود', '0987654321', 'sara@example.com', 'CD789012', 'فيلا', '2024-02-01', 6, 1),
('عمر حسن', '0555123456', 'omar@example.com', 'EF345678', 'استوديو', '2024-03-01', 3, 2);
