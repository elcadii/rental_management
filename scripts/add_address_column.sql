-- Add address column to tenants table
ALTER TABLE tenants ADD COLUMN address TEXT DEFAULT NULL AFTER cin;

-- Update existing records to have a default address if needed
-- UPDATE tenants SET address = 'غير محدد' WHERE address IS NULL; 