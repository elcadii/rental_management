-- Add trial support to admins table
ALTER TABLE admins 
ADD COLUMN trial_start_date DATETIME DEFAULT NULL,
ADD COLUMN trial_end_date DATETIME DEFAULT NULL,
ADD COLUMN is_trial_active BOOLEAN DEFAULT FALSE,
ADD COLUMN subscription_status ENUM('trial', 'active', 'expired', 'cancelled') DEFAULT 'trial';

-- Update existing admins to have trial status
UPDATE admins SET 
    trial_start_date = created_at,
    trial_end_date = DATE_ADD(created_at, INTERVAL 14 DAY),
    is_trial_active = CASE 
        WHEN DATE_ADD(created_at, INTERVAL 14 DAY) > NOW() THEN TRUE 
        ELSE FALSE 
    END,
    subscription_status = CASE 
        WHEN DATE_ADD(created_at, INTERVAL 14 DAY) > NOW() THEN 'trial'
        ELSE 'expired'
    END; 