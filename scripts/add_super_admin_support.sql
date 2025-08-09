-- Add super admin support to admins table
ALTER TABLE admins 
ADD COLUMN is_super_admin BOOLEAN DEFAULT FALSE AFTER password;

-- Set the first admin (ID 1) as super admin
UPDATE admins SET is_super_admin = TRUE WHERE id = 1;

-- Optionally, set other specific admins as super admin
-- UPDATE admins SET is_super_admin = TRUE WHERE id IN (2, 3); 